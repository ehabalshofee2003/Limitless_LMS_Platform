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
    Schema::create('quizzes', function (Blueprint $table) {
        $table->id();
        $table->foreignId('lesson_id')->nullable()->constrained()->onDelete('cascade'); // اختبار درس
        $table->foreignId('course_id')->constrained()->onDelete('cascade'); // اختبار نهائي (lesson_id null)
        $table->string('title');
        $table->integer('passing_score')->default(50);
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};
