<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Course;
use App\Models\Cohort;
use App\Models\Institution;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use PHPUnit\Framework\Attributes\Test; // استيراد الـ Attribute الجديد

class EnrollmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        Role::create(['name' => 'student']);
        Role::create(['name' => 'institution']);
    }

    #[Test] // الطريقة الحديثة
    public function a_student_can_enroll_in_a_cohort()
    {
        // 1. Arrange
        $student = User::factory()->create();
        $student->assignRole('student');

        $institutionUser = User::factory()->create();
        $institutionUser->assignRole('institution');
        $institution = Institution::create([
            'user_id' => $institutionUser->id,
            'name' => 'Test Academy',
            'slug' => 'test-academy'
        ]);
        
        $course = Course::create([
            'institution_id' => $institution->id,
            'title' => 'PHP Course',
            'slug' => 'php-course',
            'description' => 'Test Description', // تمت الإضافة
            'status' => 'published',
            'price' => 100
        ]);

        $cohort = Cohort::create([
            'course_id' => $course->id,
            'name' => 'Batch 1',
            'start_date' => now(),
            'end_date' => now()->addMonth(),
            'max_students' => 10
        ]);

        // 2. Act
        $response = $this->actingAs($student, 'sanctum')
                         ->postJson("/api/cohorts/{$cohort->id}/enroll");

        // 3. Assert
        $response->assertStatus(201);
        $response->assertJson(['message' => 'Enrollment successful.']);

        $this->assertDatabaseHas('cohort_user', [
            'user_id' => $student->id,
            'cohort_id' => $cohort->id,
        ]);
    }

    #[Test] // الطريقة الحديثة
    public function a_student_cannot_enroll_in_a_full_cohort()
    {
        // 1. Arrange
        $student1 = User::factory()->create()->assignRole('student');
        $student2 = User::factory()->create()->assignRole('student');
        
        $institutionUser = User::factory()->create()->assignRole('institution');
        $institution = Institution::create(['user_id' => $institutionUser->id, 'name' => 'Inst', 'slug' => 'inst']);
        $course = Course::create([
            'institution_id' => $institution->id, 
            'title' => 'C', 
            'slug' => 'c', 
            'description' => 'Test', // تمت الإضافة
            'status' => 'published'
        ]);
        
        $cohort = Cohort::create([
            'course_id' => $course->id, 'name' => 'Full Batch', 
            'start_date' => now(), 'end_date' => now()->addDay(),
            'max_students' => 1
        ]);

        // ملء الدفعة بالطالب الأول
        $cohort->students()->attach($student1->id, ['enrolled_at' => now()]);

        // 2. Act
        $response = $this->actingAs($student2, 'sanctum')
                         ->postJson("/api/cohorts/{$cohort->id}/enroll");

        // 3. Assert
        $response->assertStatus(400); 
        $response->assertJson(['message' => 'This cohort is full.']);
    }
}