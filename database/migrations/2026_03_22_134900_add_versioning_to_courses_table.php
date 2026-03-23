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
    Schema::table('cohorts', function (Blueprint $table) {
        // استراتيجية الفتح: sequential (تسلسلي), manual (يدوي), all (مفتوح الكل)
        $table->enum('content_unlock_strategy', ['sequential', 'manual', 'all'])->default('all');
        $table->integer('watch_threshold')->default(80); // نسبة المشاهدة المطلوبة (80%)
    });

    Schema::table('lesson_user', function (Blueprint $table) {
        // لتسجيل هل الدرس مفتوح لهذا الطالب؟
        $table->boolean('is_unlocked')->default(false); 
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            //
        });
    }
};
