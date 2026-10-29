<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // product ratings are out of 10 (e.g. 8.5); decimal(3,2) could not hold 10.00
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('rating', 4, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('rating', 3, 2)->nullable()->change();
        });
    }
};
