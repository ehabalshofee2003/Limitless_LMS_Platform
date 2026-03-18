<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\EnrollmentService;
use App\Repositories\CohortRepository;
use App\Models\User;
use Mockery;

class EnrollmentServiceTest extends TestCase
{
    /** @test */
    public function it_returns_error_if_cohort_not_found()
    {
        // 1. Mocking (تهكير الـ Repository)
        // نحن لا نريد الوصول لقاعدة البيانات هنا، نريد فقط اختبار المنطق داخل Service
        $mockRepo = Mockery::mock(CohortRepository::class);
        
        // نقول للموك: إذا تم استدعاء findById بـ 999، أرجع null
        $mockRepo->shouldReceive('findById')->with(999)->andReturn(null);

        // 2. Inject
        $service = new EnrollmentService($mockRepo, app(\App\Repositories\CourseRepository::class));
        $user = User::factory()->make(); // make لا ينشئ في القاعدة

        // 3. Act
        $result = $service->enrollStudent($user, 999);

        // 4. Assert
        $this->assertArrayHasKey('error', $result);
        $this->assertEquals('Cohort not found.', $result['error']);
    }
}