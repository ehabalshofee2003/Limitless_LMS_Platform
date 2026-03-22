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
    Schema::create('comments', function (Blueprint $table) {
        $table->id();
        
        // صاحب التعليق
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        
        // التعليق قابل للربط بأي كيان (درس، دورة، الخ)
        $table->morphs('commentable'); 
            
        // جوهر النظام التشعبي: الربط بالتعليق الأب
        $table->unsignedBigInteger('parent_id')->nullable(); 
        $table->foreign('parent_id')->references('id')->on('comments')->onDelete('cascade');
        
        $table->text('body');
        $table->timestamps();
        
        // فهرسة لسرعة جلب التعلبات
        $table->index(['commentable_id', 'commentable_type', 'parent_id']);
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
