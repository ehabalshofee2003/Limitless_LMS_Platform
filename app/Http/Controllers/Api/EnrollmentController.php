<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\EnrollmentService;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    protected $enrollmentService;

    public function __construct(EnrollmentService $enrollmentService)
    {
        $this->enrollmentService = $enrollmentService;
    }

    public function enroll(Request $request, $cohortId)
    {
        $user = $request->user();

        // نستدعي الخدمة لتقوم بكل العمل
        $result = $this->enrollmentService->enroll($user, $cohortId);

        // نتعامل مع النتيجة فقط
        if (isset($result['error'])) {
            return response()->json(['message' => $result['error']], $result['code']);
        }

        return response()->json([
            'message' => 'Enrollment successful',
            'data' => $result['data']
        ], 201);
    }
}