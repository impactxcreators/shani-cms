<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('deals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->decimal('original_price', 10, 2)->nullable();
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->string('destination_url')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('status')->default('published')->index();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['store_id', 'status']);
        });
    }
    public function down(): void { Schema::dropIfExists('deals'); }
};
