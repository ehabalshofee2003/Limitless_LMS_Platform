<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CodeExecutionService
{
    protected $apiUrl;
    protected $apiKey;

    public function __construct()
    {
        // يمكن وضع هذه القيم في ملف .env
        $this->apiUrl = 'https://judge0-ce.p.rapidapi.com';
        $this->apiKey = env('JUDGE0_API_KEY'); 
    }

    public function execute(string $sourceCode, int $languageId)
    {
        try {
            // 1. إرسال الكود للحصول على Submission ID
            $response = Http::withHeaders([
                'X-RapidAPI-Key' => $this->apiKey,
                'X-RapidAPI-Host' => 'judge0-ce.p.rapidapi.com',
                'Content-Type' => 'application/json',
            ])->post("{$this->apiUrl}/submissions", [
                'source_code' => $sourceCode,
                'language_id' => $languageId, // 71 = Python, 68 = PHP
                'stdin' => '', // مدخلات الاختبار (اختياري)
            ]);

            if (!$response->successful()) {
                return ['error' => 'Execution service unavailable.'];
            }

            $token = $response->json('token');

            // 2. انتظار قصير ثم جلب النتيجة
            // في الإنتاج نستخدم Queue للانتظار، هنا سننتظر ثانيتين للتجربة
            sleep(2);

            return $this->getResult($token);

        } catch (\Exception $e) {
            Log::error('Code Execution Error: ' . $e->getMessage());
            return ['error' => 'Internal Server Error during execution.'];
        }
    }

    protected function getResult($token)
    {
        $response = Http::withHeaders([
            'X-RapidAPI-Key' => $this->apiKey,
            'X-RapidAPI-Host' => 'judge0-ce.p.rapidapi.com',
        ])->get("{$this->apiUrl}/submissions/{$token}");

        $data = $response->json();

        return [
            'status' => $data['status']['description'] ?? 'Unknown',
            'output' => $data['stdout'] ?? null, // الناتج الصحيح
            'error' => $data['stderr'] ?? null,  // أخطاء المعالجة
            'compile_output' => $data['compile_output'] ?? null, // أخطاء الكومبايل
            'time' => $data['time'] ?? null,
            'memory' => $data['memory'] ?? null,
        ];
    }
}