<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('page_sections', function (Blueprint $table) {
            // Human handle for addressing an instance: $sections->section('hero').
            $table->string('name')->nullable()->after('type');
            // Schema version this instance was saved under (drift detection).
            $table->unsignedInteger('schema_version')->default(1)->after('settings');

            $table->index(['page_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::table('page_sections', function (Blueprint $table) {
            $table->dropIndex(['page_id', 'name']);
            $table->dropColumn(['name', 'schema_version']);
        });
    }
};
