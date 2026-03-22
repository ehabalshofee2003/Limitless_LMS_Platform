<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CodeExecutionService;
use Illuminate\Http\Request;

class CodeRunnerController extends Controller
{
    protected $codeService;

    public function __construct(CodeExecutionService $codeService)
    {
        $this->codeService = $codeService;
    }

    public function run(Request $request)
    {
        $request->validate([
            'source_code' => 'required|string',
            'language_id' => 'required|integer', 
            // Language IDs: 71 (Python 3), 68 (PHP), 63 (JavaScript Node)
        ]);

        $result = $this->codeService->execute(
            $request->source_code,
            $request->language_id
        );

        return response()->json($result);
    }
}