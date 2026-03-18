<?php
// app/Http/Controllers/Api/AiController.php

use OpenAI;
use App\Models\Lesson;

class AiController extends Controller
{
    public function lessonSummary($id)
    {
        $lesson = Lesson::findOrFail($id);
        
        // التحقق من صلاحية الطالب (يجب أن يكون مشتركاً)
        // ... كود التحقق ...

        $client = OpenAI::client(config('services.openai.key'));

        // نفترض أن نص الدرس موجود في description أو حقل content
        $prompt = "Summarize the following lesson content in 3 bullet points:\n\n" . $lesson->description;

        $response = $client->chat()->create([
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
        ]);

        return response()->json([
            'lesson_title' => $lesson->title,
            'ai_summary' => $response->choices[0]->message->content,
        ]);
    }
}