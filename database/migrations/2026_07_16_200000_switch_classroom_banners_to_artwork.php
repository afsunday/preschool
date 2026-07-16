<?php

use App\Models\ClassroomBanner;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Banners moved from generated `{pattern}-{palette}` keys to hand-drawn artwork
 * in `public/images/banners/`. Old keys no longer name anything, so any row still
 * holding one is repointed at the default rather than left dangling.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('classrooms', function (Blueprint $table) {
            $table->string('banner', 40)->default(ClassroomBanner::DEFAULT)->change();
        });

        DB::table('classrooms')
            ->whereNotIn('banner', ClassroomBanner::keys())
            ->update(['banner' => ClassroomBanner::DEFAULT]);
    }

    public function down(): void
    {
        Schema::table('classrooms', function (Blueprint $table) {
            $table->string('banner', 40)->default('dots-grape')->change();
        });
    }
};
