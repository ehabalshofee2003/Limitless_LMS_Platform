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
    Schema::create('institutions', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('slug')->unique();
        $table->text('description')->nullable();
        $table->boolean('is_verified')->default(false); // موافقة المشرف
        $table->decimal('platform_commission', 5, 2)->default(20.00); // نسبة المنصة
        $table->foreignId('user_id')->constrained()->onDelete('cascade'); // المستخدم المسؤول عن المؤسسة
        $table->timestamps();
    });
    
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('institutions');
    }
};
