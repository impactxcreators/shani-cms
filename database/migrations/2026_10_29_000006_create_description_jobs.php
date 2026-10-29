<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // one row per item in a bulk "write descriptions" run; keeps the old text for undo
        Schema::create('description_jobs', function (Blueprint $table) {
            $table->id();
            $table->uuid('batch')->index();
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->string('title')->nullable();
            $table->string('length', 10)->default('medium');
            $table->boolean('overwrite')->default(false);
            $table->string('status', 10)->default('pending'); // pending | done | skipped | failed | undone
            $table->longText('old_description')->nullable();
            $table->longText('new_description')->nullable();
            $table->string('note', 500)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('description_jobs');
    }
};
