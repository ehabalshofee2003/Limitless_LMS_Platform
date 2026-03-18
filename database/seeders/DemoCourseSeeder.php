<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Institution;
use App\Models\Course;
use App\Models\Cohort;
use App\Models\Quiz;
use App\Models\Lesson;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoCourseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. إنشاء مستخدم يمثل "مؤسسة/مدرب"
        $instructorUser = User::firstOrCreate(
            ['email' => 'instructor@test.com'],
            [
                'name' => 'Ahmed Academy',
                'password' => Hash::make('password')
            ]
        );
        // تعيين الدور
        if (!$instructorUser->hasRole('institution')) {
            $instructorUser->assignRole('institution');
        }

        // 2. إنشاء سجل المؤسسة المرتبط بالمستخدم
        $institution = Institution::firstOrCreate(
            ['user_id' => $instructorUser->id],
            [
                'name' => 'Ahmed Coding Academy',
                'slug' => 'ahmed-coding',
                'is_verified' => true, // نفترض أنه موثق
                'platform_commission' => 20.00
            ]
        );

        // 3. إنشاء دورة تجريبية
        $course = Course::firstOrCreate(
            ['slug' => 'laravel-masterclass'],
            [
                'institution_id' => $institution->id,
                'title' => 'Laravel 12 Masterclass',
                'description' => 'Learn Laravel from scratch to advanced.',
                'price' => 50.00,
                'status' => 'published' // يجب أن تكون منشورة ليظهر للطلاب
            ]
        );

        // 4. إنشاء دفعة (Cohort) - وهي الهدف الذي سيقدم عليه الطالب
        $cohort = Cohort::firstOrCreate(
            ['course_id' => $course->id, 'name' => 'Batch Jan 2025'],
            [
                'start_date' => now(),
                'end_date' => now()->addMonths(3),
                'max_students' => 30,
                'google_meet_link' => 'https://meet.google.com/demo-link'
            ]
        );

        // 5. إنشاء بعض الدروس داخل الدورة (لنجعل الدورة تحتوي على محتوى)
        for ($i = 1; $i <= 3; $i++) {
            Lesson::firstOrCreate(
                [
                    'course_id' => $course->id,
                    'title' => "Lesson $i: Basics of Laravel"
                ],
                [
                    'description' => "Content for lesson $i",
                    'type' => 'video',
                    'resource_path' => 'videos/laravel_intro.mp4',
                    'order' => $i,
                    'duration_minutes' => 15
                ]
            );
        }
 
        // إضافة اختبار للدرس الأول
        $firstLesson = Lesson::where('course_id', $course->id)->first();

        Quiz::firstOrCreate(
            ['lesson_id' => $firstLesson->id],
            [
                'course_id' => $course->id,
                'title' => 'Quiz for Lesson 1',
                'questions' => json_encode([
                    [
                        'question' => 'What is Laravel?',
                        'options' => ['Framework', 'Language', 'Database', 'Server'],
                        'correct_index' => 0 // الفهرس 0 يعني الإجابة الأولى
                    ],
                    [
                        'question' => 'Which command creates a controller?',
                        'options' => ['make:model', 'make:controller', 'make:migration', 'make:auth'],
                        'correct_index' => 1
                    ]
                ]),
                'passing_score' => 50 // درجة النجاح 50%
            ]
        );
        $this->command->info('Demo Course, Cohort, and Lessons created successfully!');
    }
}