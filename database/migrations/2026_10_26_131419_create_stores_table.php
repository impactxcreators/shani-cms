<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('logo')->nullable();
            $table->longText('description')->nullable();
            $table->string('website_url')->nullable();
            $table->string('affiliate_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->decimal('average_rating', 3, 2)->nullable();
            $table->unsignedInteger('coupons_count')->default(0);
            $table->unsignedInteger('deals_count')->default(0);
            $table->string('status')->default('published')->index();
            $table->timestamp('published_at')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 500)->nullable();
            $table->boolean('no_index')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void { Schema::dropIfExists('stores'); }
};
