<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReviewRequest;
use App\Services\ReviewService;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    protected $reviewService;

    public function __construct(ReviewService $reviewService)
    {
        $this->reviewService = $reviewService;
    }

    public function store(StoreReviewRequest $request, $courseId)
    {
        $result = $this->reviewService->createReview($request->user(), $courseId, $request->validated());

        if (isset($result['error'])) {
            return response()->json(['message' => $result['error']], $result['code']);
        }

        return response()->json([
            'message' => 'Review added successfully.',
            'data' => $result['data']
        ], 201);
    }

    public function index($courseId)
    {
        $reviews = $this->reviewService->getCourseReviews($courseId);
        return response()->json($reviews);
    }
}