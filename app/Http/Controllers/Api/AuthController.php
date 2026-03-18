<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str; // <--- هذا هو السطر المطلوب
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Validation\Rules\Password as PasswordRule;

class AuthController extends Controller
{
    /**
     * تسجيل مستخدم جديد
     */
    public function register(Request $request)
    {
        // 1. التحقق من البيانات المدخلة
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()], 
            'role' => ['sometimes', 'string', 'in:student,institution'], // الدور: إما طالب أو مؤسسة
        ]);

        // 2. إنشاء المستخدم
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // 3. تعيين الدور للمستخدم (افتراضياً طالب إذا لم يتم تحديده)
        $role = $request->role ?? 'student';
        $user->assignRole($role);

        // 4. إذا كان الدور "مؤسسة"، نقوم بإنشاء سجل مؤسسة فارغ ليملؤه لاحقاً
        if ($role === 'institution') {
            $user->institution()->create([
                'name' => $user->name . ' Institution',
                'slug' => Str::slug($user->name),
                'is_verified' => false, // يحتاج موافقة المشرف
            ]);
        }

        // 5. إنشاء التوكن (Token) للوصول للـ API
        $token = $user->createToken('auth_token')->plainTextToken;

        // هنا التغيير: نستخدم UserResource لتنسيق الرد
        return response()->json([
            'message' => 'User registered successfully',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => new UserResource($user), // <--- هنا تم التعديل
        ], 201);
    }

    /**
     * تسجيل الدخول
     */
    public function login(Request $request)
    {
        // 1. التحقق من البيانات
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        // 2. محاولة التحقق من صحة البيانات
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Invalid login details'
            ], 401);
        }

        $user = User::where('email', $request->email)->firstOrFail();

        // 3. إنشاء توكن جديد
    $token = $user->createToken('auth_token')->plainTextToken;

    // هنا التغيير: نستخدم UserResource لتنسيق الرد
    return response()->json([
        'message' => 'User registered successfully',
        'access_token' => $token,
        'token_type' => 'Bearer',
        'user' => new UserResource($user), // <--- هنا تم التعديل
    ], 201);
    }

    /**
     * تسجيل الخروج
     */
    public function logout(Request $request)
    {
        // حذف التوكن الحالي المستخدم للوصول
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * عرض بيانات المستخدم الحالي (Profile)
     */
    public function profile(Request $request)
    {
        // سنستخدم هذا لاحقاً لمعرفة: هل المستخدم طالب؟ أم مؤسسة؟ وما هي بياناته؟
        return response()->json([
            'user' => $request->user()->load('roles', 'institution', 'cohorts'),
        ]);
    }
    
    /**
     * 1. طلب رابط استعادة كلمة المرور
     */
    public function requestPasswordReset(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        // سنقوم بإرسال رابط الاستعادة
        // ملاحظة: ستحتاج لتعديل نموذج الإيميل ليرسل رابط Frontend بدلاً من Backend
        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['message' => __($status)])
            : response()->json(['message' => __($status)], 400);
    }

    /**
     * 2. إعادة تعيين كلمة المرور (عند الضغط على الرابط)
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => __($status)])
            : response()->json(['message' => __($status)], 400);
    }

    /**
     * 3. تغيير كلمة المرور (لمستخدم مسجل الدخول)
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => ['required', 'string', PasswordRule::defaults(), 'confirmed'],
        ]);

        $user = $request->user();

        // التحقق من كلمة المرور القديمة
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Current password is incorrect.'], 400);
        }

        // تحديث كلمة المرور
        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        // (اختياري) حذف كل التوكنات القديمة لإجباره على تسجيل الدخول من جديد
        // $user->tokens()->delete();

        return response()->json(['message' => 'Password changed successfully.']);
    }
    
    /**
     * 4. تحديث التوكن (Refresh Token)
     * ملاحظة: Sanctum لا يعمل بنظام Refresh Token تلقائي كـ JWT، 
     * ولكن هذه الطريقة تحذف القديم وتعطي جديداً.
     */
    public function refreshToken(Request $request)
    {
        $user = $request->user();
        
        // حذف التوكن الحالي
        $user->currentAccessToken()->delete();
        
        // إنشاء توكن جديد
        $newToken = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Token refreshed.',
            'access_token' => $newToken,
            'token_type' => 'Bearer',
        ]);
    }
}
 