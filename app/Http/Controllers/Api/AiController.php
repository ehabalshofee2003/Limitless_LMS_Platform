<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use OpenAI;
use App\Models\Lesson;

class AiController extends Controller
{
    public function lessonSummary($id)
    {
        $lesson = Lesson::findOrFail($id);

        if (!$lesson->description) {
            return response()->json([
                'message' => 'Lesson content is empty'
            ], 400);
        }

        $client = OpenAI::client(config('services.openai.key'));

        $prompt = "
        You are an expert educational assistant.

        Summarize the following lesson into exactly 3 concise bullet points:
        - Clear and simple
        - Focus on key ideas
        - No extra explanation

        Lesson:
        " . $lesson->description;

        try {
            $response = $client->chat()->create([
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
                'max_tokens' => 150,
            ]);

            return response()->json([
                'lesson_title' => $lesson->title,
                'ai_summary' => $response->choices[0]->message->content,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'AI service error',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}