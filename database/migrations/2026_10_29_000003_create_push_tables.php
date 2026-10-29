<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // one row per browser that said yes to deal alerts
        Schema::create('push_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->char('endpoint_hash', 64)->unique();
            $table->text('endpoint');
            $table->string('public_key');
            $table->string('auth_token');
            $table->string('content_encoding', 20)->default('aes128gcm');
            $table->string('user_agent', 500)->nullable();
            $table->timestamps();
        });

        // notifications written and sent from the admin panel
        Schema::create('push_messages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('body', 500);
            $table->string('url', 1000)->nullable();
            $table->string('image_url', 1000)->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->unsignedInteger('click_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('push_messages');
        Schema::dropIfExists('push_subscriptions');
    }
};
