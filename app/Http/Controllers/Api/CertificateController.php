<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cohort;
use App\Services\CertificateService;
use Illuminate\Http\Request;
use App\Notifications\CertificateIssuedNotification;

class CertificateController extends Controller
{
    protected $certificateService;

    public function __construct(CertificateService $certificateService)
    {
        $this->certificateService = $certificateService;
    }

    public function checkEligibility(Request $request, Cohort $cohort)
    {
        $isEligible = $this->certificateService->isEligible($request->user(), $cohort);

        return response()->json([
            'eligible' => $isEligible,
            'message' => $isEligible 
                ? 'Congratulations! You are eligible for the certificate.' 
                : 'You have not met the requirements yet.'
        ]);
    }

    public function download(Request $request, Cohort $cohort)
    {
        $user = $request->user();

        // التحقق قبل التحميل
        if (!$this->certificateService->isEligible($user, $cohort)) {
            return response()->json([
                'message' => 'You are not eligible to download the certificate yet.'
            ], 403);
        }

        // تحديث حالة إصدار الشهادة (اختياري)
        $cohort->students()->updateExistingPivot($user->id, [
            'certificate_issued' => true
        ]);

        return $this->certificateService->generatePdf($user, $cohort);
    }
 
}