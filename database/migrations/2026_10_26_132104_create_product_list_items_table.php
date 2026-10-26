<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('product_list_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_list_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('rank')->default(0);
            $table->string('custom_title')->nullable();
            $table->text('custom_blurb')->nullable();
            $table->text('editor_note')->nullable();
            $table->string('badge')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('product_list_items'); }
};
