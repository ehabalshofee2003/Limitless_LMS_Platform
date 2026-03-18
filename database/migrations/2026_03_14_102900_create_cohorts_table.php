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
    Schema::create('cohorts', function (Blueprint $table) {
        $table->id();
        $table->foreignId('course_id')->constrained()->onDelete('cascade');
        $table->string('name'); // مثال: "دفعة الصيف 2024"
        $table->dateTime('start_date');
        $table->dateTime('end_date');
        $table->integer('max_students')->default(50);
        $table->string('google_meet_link')->nullable(); // رابط الاجتماعات الحية
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cohorts');
    }
};
