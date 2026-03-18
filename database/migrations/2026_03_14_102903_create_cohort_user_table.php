<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
    Schema::create('cohort_user', function (Blueprint $table) {
        $table->id();
        $table->foreignId('cohort_id')->constrained()->onDelete('cascade');
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        
        // حقول المتابعة والتقييم
        $table->decimal('progress_percentage', 5, 2)->default(0);
        $table->decimal('attendance_percentage', 5, 2)->default(0);
        $table->decimal('total_quiz_grade', 5, 2)->default(0); // مجموع اختبارات الدروس
        $table->decimal('final_exam_grade', 5, 2)->default(0);
        $table->integer('instructor_rating')->nullable(); // تقييم المدرب للطالب
        
        $table->boolean('certificate_issued')->default(false);
        $table->timestamp('enrolled_at')->useCurrent();
         $table->timestamps();
        $table->unique(['cohort_id', 'user_id']); // منع التكرار
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cohort_user');
    }
};
