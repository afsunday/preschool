<?php

use App\Models\ClassroomBanner;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A class picks its cover from a generated library (pattern × colourway). We
 * store only the key — the artwork is built in source, so nothing here can leak
 * a colour or a CSS class into the database.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('classrooms', function (Blueprint $table) {
            $table->string('banner', 40)->default(ClassroomBanner::DEFAULT)->after('color');
        });
    }

    public function down(): void
    {
        Schema::table('classrooms', function (Blueprint $table) {
            $table->dropColumn('banner');
        });
    }
};
