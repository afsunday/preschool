<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('type', 16)->default('update')->after('user_id');
            $table->string('event_title')->nullable()->after('body');
            $table->timestamp('event_at')->nullable()->after('event_title');
            $table->timestamp('event_ends_at')->nullable()->after('event_at');
            $table->string('event_location')->nullable()->after('event_ends_at');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['type', 'event_title', 'event_at', 'event_ends_at', 'event_location']);
        });
    }
};
