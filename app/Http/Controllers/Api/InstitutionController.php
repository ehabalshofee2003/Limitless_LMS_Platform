<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Institution;
use App\Models\Payout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreInstitutionRequest;
use App\Http\Requests\UpdateInstitutionRequest;
use App\Services\InstitutionService;

class InstitutionController extends Controller
{
     protected $institutionService;

    public function __construct(InstitutionService $institutionService)
    {
        $this->institutionService = $institutionService;
    }
    public function store(StoreInstitutionRequest $request)
    {
        $result = $this->institutionService->createInstitution($request->user(), $request->validated());

        if (isset($result['error'])) {
            return response()->json(['message' => $result['error']], $result['code']);
        }

        return response()->json([
            'message' => 'Institution created successfully.',
            'data' => $result['data']
        ], 201);
    }

    public function update(UpdateInstitutionRequest $request, $id)
    {
        $result = $this->institutionService->updateInstitution($request->user()->id, $id, $request->validated());

        if (isset($result['error'])) {
            return response()->json(['message' => $result['error']], $result['code']);
        }

        return response()->json($result['data']);
    }

    // عرض لوحة التحكم
    public function dashboard($id)
    {
        $result = $this->institutionService->getDashboard($id);

        if (isset($result['error'])) {
            return response()->json(['message' => $result['error']], $result['code']);
        }

        return response()->json($result['data']);
    }
    
    // عرض ملف المؤسسة
    public function show($id)
    {
        $institution = Institution::with('user')->findOrFail($id);
        return response()->json($institution);
    }
    // ... inside InstitutionController

    public function index()
    {
        // للمشرف: عرض كل المؤسسات
        return Institution::with('user')->get();
    }

    public function approve($id)
    {
        $institution = Institution::findOrFail($id);
        $institution->update(['is_verified' => true]);
        return response()->json(['message' => 'Institution approved.']);
    }

    public function reject($id)
    {
        // منطق الرفض (مثلاً إرسال إشعار أو حذف)
        return response()->json(['message' => 'Institution rejected.']);
    }   
        public function requestPayout(Request $request)
    {
        $request->validate(['amount' => 'required|numeric|min:10']);
        
        $user = $request->user();
        $wallet = $user->wallet;

        // التحقق من الرصيد المتاح (وليس المعلق)
        if ($wallet->balance < $request->amount) {
            return response()->json(['message' => 'Insufficient available balance.'], 400);
        }

        $payout = Payout::create([
            'user_id' => $user->id,
            'amount' => $request->amount,
            'status' => 'pending',
            'payment_method' => 'bank_transfer',
            'details' => 'Account details...' // تؤخذ من الفورم
        ]);

        // تجميد المبلغ فوراً
        $wallet->balance -= $request->amount;
        $wallet->save();

        return response()->json(['message' => 'Payout request submitted.', 'data' => $payout]);
    }
}