<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuizController extends Controller
{
    public function show(Quiz $quiz)
    {
        // إذا كانت questions فارغة أو نصاً بسبب خطأ سابق، نتعامل معها بحذر
        $questions = $quiz->questions;

        // التأكد من أنها مصفوفة قبل المعالجة
        if (is_array($questions)) {
            $questions = collect($questions)->map(function ($q) {
                // نحول العنصر إلى مصفوفة (تحوطاً) ثم نحذف الإجابة
                $q = (array) $q;
                unset($q['correct_index']);
                return $q;
            })->values()->all(); // values() يعيد ترقيم المفاتيح
        } else {
            $questions = []; // إذا حدث خطأ، أرجع مصفوفة فارغة
        }

        return response()->json([
            'quiz_title' => $quiz->title,
            'questions' => $questions
        ]);
    }

 public function submit(Request $request, Quiz $quiz)
{
    // 1. التحقق من المدخلات
    $request->validate([
        'answers' => 'required|array',
        'answers.*' => 'integer'
    ]);

    $user = $request->user();
    $answers = $request->answers;
    
    // 2. معالجة الأسئلة (الحل الجذري هنا)
    $questions = $quiz->questions;

    // إذا كانت البيانات نصاً، حولها إلى مصفوفة
    if (is_string($questions)) {
        $questions = json_decode($questions, true);
    }
    
    // التأكد النهائي أننا نملك مصفوفة
    if (!is_array($questions)) {
        return response()->json(['message' => 'Quiz data format is invalid.'], 500);
    }

    // 3. حساب الدرجة
    $correctCount = 0;
    foreach ($questions as $index => $question) {
        // التحقق إذا الإجابة صحيحة
        if (isset($answers[$index]) && $answers[$index] == $question['correct_index']) {
            $correctCount++;
        }
    }

    $totalQuestions = count($questions);
    $score = ($totalQuestions > 0) ? ($correctCount / $totalQuestions) * 100 : 0;
    $passed = $score >= $quiz->passing_score;

    // ... باقي كود الحفظ والإرجاع
    // (احفظ المحاولة في قاعدة البيانات كما شرحنا سابقاً)
    
    return response()->json([
        'message' => $passed ? 'Congratulations! You passed.' : 'You did not pass.',
        'score' => $score . '%',
        'passed' => $passed
    ]);
}

    protected function updateStudentGrades($user, $quiz, $score)
    {
        // بسيطة: سنضيف الدرجة إلى حقل total_quiz_grade أو نحدثه
        // هذه دالة مبسطة، يمكن تطويرها لاحتساب المعدل التراكمي
        $cohort = $quiz->lesson->course->cohorts()->whereHas('students', fn($q) => $q->where('user_id', $user->id))->first();
        
        if ($cohort) {
            $currentTotal = $cohort->students()->where('user_id', $user->id)->first()->pivot->total_quiz_grade;
            $newTotal = ($currentTotal + $score) / 2; // مثال: حساب المتوسط

            $cohort->students()->updateExistingPivot($user->id, [
                'total_quiz_grade' => $newTotal
            ]);
        }
    }

    protected function updateFinalGrade($user, $quiz, $score)
    {
        $cohort = $quiz->course->cohorts()->whereHas('students', fn($q) => $q->where('user_id', $user->id))->first();
        
        if ($cohort) {
            $cohort->students()->updateExistingPivot($user->id, [
                'final_exam_grade' => $score
            ]);
        }
    }
}