<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * An entry has two dimensions the old single `detail` column conflated: what it
 * was, and how it went. A meal is "Lunch" + "Ate all"; a nappy is "Change" +
 * "Wet". `label` carries the first, `detail` the second.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('report_entries', function (Blueprint $table) {
            $table->string('label')->nullable()->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('report_entries', function (Blueprint $table) {
            $table->dropColumn('label');
        });
    }
};
