<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Tables whose admin list has a Duplicate action. */
    private array $tables = [
        'adverts', 'authors', 'blog_categories', 'categories', 'coupons', 'deals', 'handpicks',
        'home_banners', 'home_blocks', 'pages', 'posts', 'product_categories', 'product_lists',
        'products', 'stores',
    ];

    public function up(): void
    {
        // remembers which record a copy was made from, so admin lists can show it right below the original
        foreach ($this->tables as $t) {
            Schema::table($t, function (Blueprint $table) {
                $table->unsignedBigInteger('copied_from_id')->nullable()->index();
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $t) {
            Schema::table($t, function (Blueprint $table) use ($t) {
                $table->dropIndex($t.'_copied_from_id_index');
                $table->dropColumn('copied_from_id');
            });
        }
    }
};
