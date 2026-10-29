<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // push messages become campaigns: what they promote, an internal name, optional send time
        Schema::table('push_messages', function (Blueprint $table) {
            $table->string('name')->nullable()->after('id');
            $table->string('promote_type', 30)->nullable()->after('name');
            $table->unsignedBigInteger('promote_id')->nullable()->after('promote_type');
            $table->timestamp('scheduled_at')->nullable()->after('image_url');
            $table->unsignedBigInteger('copied_from_id')->nullable()->index();
        });

        // lets an admin send a test campaign to their own browser
        Schema::table('push_subscriptions', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->index()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('push_subscriptions', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropColumn('user_id');
        });
        Schema::table('push_messages', function (Blueprint $table) {
            $table->dropIndex(['copied_from_id']);
            $table->dropColumn(['name', 'promote_type', 'promote_id', 'scheduled_at', 'copied_from_id']);
        });
    }
};
