<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('short_title')->nullable();
            $table->string('slug')->unique();
            $table->string('brand')->nullable();
            $table->string('image_url', 1000)->nullable();
            $table->unsignedBigInteger('local_media_id')->nullable();
            $table->json('highlights')->nullable();
            $table->longText('description')->nullable();
            $table->decimal('rating', 3, 2)->nullable();
            $table->unsignedInteger('review_count')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->string('external_id')->nullable()->comment('ASIN or retailer id');
            $table->string('affiliate_url', 1000)->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->boolean('is_available')->default(true);
            $table->foreignId('product_category_id')->nullable()->constrained('product_categories')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void { Schema::dropIfExists('products'); }
};
