<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('click_events', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type')->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->string('destination_url', 1000)->nullable();
            $table->string('ip_hash', 64)->nullable();
            $table->string('user_agent', 512)->nullable();
            $table->string('referrer', 1000)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->index(['entity_type', 'entity_id', 'created_at']);
        });
    }
    public function down(): void { Schema::dropIfExists('click_events'); }
};
