<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            // Raw markup injected into <head> and before </body> on the public site.
            $table->text('header_scripts')->nullable()->after('og_media_id');
            $table->text('footer_scripts')->nullable()->after('header_scripts');
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn(['header_scripts', 'footer_scripts']);
        });
    }
};
