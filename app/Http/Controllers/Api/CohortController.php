<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CohortService;
use Illuminate\Http\Request;
use App\Http\Requests\StoreCohortRequest;

class CohortController extends Controller
{
    protected $cohortService;

    public function __construct(CohortService $cohortService)
    {
        $this->cohortService = $cohortService;
    }


    public function store(StoreCohortRequest $request)
    {
        $result = $this->cohortService->createCohort($request->user(), $request->validated());

        if (isset($result['error'])) {
            return response()->json(['message' => $result['error']], $result['code']);
        }

        return response()->json([
            'message' => 'Cohort created successfully.',
            'data' => $result['data']
        ], 201);
    }
    // تحديث دفعة

    public function update(StoreCohortRequest $request, $id)
    {
        // يمكن استخدام نفس Request للتحديث غالباً
        $result = $this->cohortService->updateCohort($request->user(), $id, $request->validated());

        if (isset($result['error'])) {
            return response()->json(['message' => $result['error']], $result['code']);
        }

        return response()->json($result['data']);
    }

    // تسجيل طالب في دفعة
    public function enroll(Request $request, $id)
    {
        $result = $this->cohortService->enrollStudent($request->user(), $id);

        if (isset($result['error'])) {
            return response()->json(['message' => $result['error']], $result['code']);
        }

        return response()->json(['message' => $result['message']]);
    }

    // عرض طلاب الدفعة (للمدرب)
    public function getStudents(Request $request, $id)
    {
        $result = $this->cohortService->getCohortStudents($request->user(), $id);

        if (isset($result['error'])) {
            return response()->json(['message' => $result['error']], $result['code']);
        }

        return response()->json($result['data']);
    }
}