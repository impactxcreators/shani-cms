<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('code')->nullable();
            $table->text('description')->nullable();
            $table->string('discount_type')->default('other'); // percent, fixed, free_shipping, bogo, other
            $table->decimal('discount_value', 10, 2)->nullable();
            $table->string('destination_url')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_exclusive')->default(false);
            $table->boolean('is_verified')->default(false);
            $table->timestamp('last_verified_at')->nullable();
            $table->unsignedBigInteger('used_count')->default(0);
            $table->unsignedBigInteger('success_count')->default(0);
            $table->unsignedBigInteger('fail_count')->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('status')->default('published')->index();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['store_id', 'status']);
        });
    }
    public function down(): void { Schema::dropIfExists('coupons'); }
};
