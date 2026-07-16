<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Mood is an event, not a property of the day — a child can be happy at 9am and
 * miserable by 3pm. It becomes a `mood` report entry with its own time, and the
 * one-per-day column goes.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Carry anything already recorded over as a morning mood entry rather
        // than dropping it on the floor.
        foreach (DB::table('daily_reports')->whereNotNull('mood')->get() as $report) {
            DB::table('report_entries')->insert([
                'daily_report_id' => $report->id,
                'type' => 'mood',
                'detail' => ucfirst($report->mood),
                'occurred_at' => $report->created_at,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Schema::table('daily_reports', function (Blueprint $table) {
            $table->dropColumn('mood');
        });
    }

    public function down(): void
    {
        Schema::table('daily_reports', function (Blueprint $table) {
            $table->string('mood')->nullable()->after('date');
        });
    }
};
