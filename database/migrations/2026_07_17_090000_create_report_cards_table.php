<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A report card: a document issued for a child at the end of a term.
 *
 * The file is stored here as a path, NOT as a reference into the media library.
 * That library is for the public site's CMS — reusable assets with alt text and
 * usage tracking. A report card is the opposite: private to one family, never
 * reused, and it would bury the CMS asset list under a term's paperwork.
 *
 * The path is on a private disk and served through an authorised route, because
 * a child's report is nobody else's business — see PortalReportCardController.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained('children')->cascadeOnDelete();
            $table->string('title');
            $table->date('issued_on')->nullable();
            $table->text('note')->nullable();

            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->default(0);

            $table->timestamp('published_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['child_id', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_cards');
    }
};
