<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('home_blocks', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // hero, guide_grid, trending, handpicked, featured_logos, rich_text, coupon_grid, blog_feed
            $table->string('title')->nullable();
            $table->json('config')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('home_blocks'); }
};
