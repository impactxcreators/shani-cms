<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // short links on our own domain: /ref/{code}
        Schema::create('referer_links', function (Blueprint $table) {
            $table->id();
            $code = $table->string('code', 100)->unique();
            if (Schema::getConnection()->getDriverName() === 'mysql') {
                $code->collation('utf8mb4_bin'); // codes are case-sensitive: aB ≠ Ab
            }
            $table->string('type', 20)->default('direct'); // direct | blank | rotational
            $table->string('rotation_mode', 20)->nullable(); // in_order | random | weighted
            $table->unsignedBigInteger('rotation_pointer')->default(0);
            $table->string('name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->string('fallback_url', 2000)->nullable();
            $table->unsignedBigInteger('click_count')->default(0);
            $table->timestamp('last_clicked_at')->nullable();
            $table->uuid('batch')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('referer_destinations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referer_link_id')->constrained('referer_links')->cascadeOnDelete();
            $table->text('url');
            $table->char('url_hash', 64)->index();
            $table->unsignedInteger('weight')->default(1);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->unsignedBigInteger('click_count')->default(0);
            $table->string('check_status', 20)->nullable(); // ok | broken
            $table->unsignedSmallInteger('check_code')->nullable();
            $table->timestamp('checked_at')->nullable();
            $table->timestamps();
        });

        // clicks per link per day, for "last 7 / 30 days"
        Schema::create('referer_clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referer_link_id')->constrained('referer_links')->cascadeOnDelete();
            $table->date('date');
            $table->unsignedInteger('clicks')->default(0);
            $table->unique(['referer_link_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referer_clicks');
        Schema::dropIfExists('referer_destinations');
        Schema::dropIfExists('referer_links');
    }
};
