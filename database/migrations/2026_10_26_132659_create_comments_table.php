<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->string('commentable_type');
            $table->unsignedBigInteger('commentable_id');
            $table->string('author_name');
            $table->string('author_email')->nullable();
            $table->text('body');
            $table->string('status')->default('pending')->index(); // pending, approved, spam, rejected
            $table->foreignId('parent_id')->nullable()->constrained('comments')->nullOnDelete();
            $table->string('ip_hash', 64)->nullable();
            $table->string('user_agent', 512)->nullable();
            $table->timestamps();
            $table->index(['commentable_type', 'commentable_id', 'status']);
        });
    }
    public function down(): void { Schema::dropIfExists('comments'); }
};
