<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The daycare portal: classes, children, guardians, feed, chat, daily reports.
 *
 * The load-bearing decision here is that a child is a *record*, not an account —
 * there are no student logins. `children` is the edge that connects a classroom
 * to the parents the app actually talks to.
 *
 * Photos are plain uploads stored as paths (see App\Support\Upload), NOT media
 * library references. The library is for the public site's CMS — reusable assets
 * with alt text and usage tracking. A room's daily photos are none of those, and
 * would bury the CMS asset list within a week.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classrooms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('grade')->nullable();
            $table->string('year');
            $table->foreignId('teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('color', 7)->nullable();
            $table->boolean('is_archived')->default(false)->index();
            $table->timestamps();

            $table->index(['is_archived', 'year']);
        });

        Schema::create('children', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classroom_id')->nullable()->constrained('classrooms')->nullOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->date('dob')->nullable();
            $table->string('photo_path')->nullable();
            $table->text('notes')->nullable();
            $table->string('invite_code', 12)->nullable()->unique();
            $table->timestamps();
            $table->softDeletes();

            $table->index('classroom_id');
        });

        Schema::create('child_guardian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained('children')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('relationship')->default('guardian');
            $table->timestamps();

            $table->unique(['child_id', 'user_id']);
        });

        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classroom_id')->constrained('classrooms')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->json('photos')->nullable();
            $table->timestamps();

            $table->index(['classroom_id', 'created_at']);
        });

        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classroom_id')->constrained('classrooms')->cascadeOnDelete();
            // 'direct' = a family's thread with the room's staff; 'announcement' =
            // the class-wide thread. Who takes part lives in the pivot below, so a
            // thread can be staff↔guardian, staff↔staff, or class-wide with no
            // role baked into this row.
            $table->string('type')->default('direct');
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();

            $table->index(['classroom_id', 'type']);
            $table->index('last_message_at');
        });

        Schema::create('conversation_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('conversations')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            // Per-participant read cursor — the correct home for "unread" across any
            // number of members, unlike the old per-side read stamps.
            $table->timestamp('last_read_at')->nullable();
            $table->timestamps();

            $table->unique(['conversation_id', 'user_id']);
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('conversations')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->json('photos')->nullable();
            $table->timestamps();

            $table->index(['conversation_id', 'created_at']);
        });

        Schema::create('daily_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained('children')->cascadeOnDelete();
            $table->date('date');
            $table->string('mood')->nullable();
            $table->text('summary')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['child_id', 'date']);
            $table->index('date');
        });

        Schema::create('report_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_report_id')->constrained('daily_reports')->cascadeOnDelete();
            $table->string('type');
            $table->timestamp('occurred_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->string('detail')->nullable();
            $table->text('note')->nullable();
            $table->json('photos')->nullable();
            $table->timestamps();

            $table->index(['daily_report_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_entries');
        Schema::dropIfExists('daily_reports');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('conversation_participants');
        Schema::dropIfExists('conversations');
        Schema::dropIfExists('posts');
        Schema::dropIfExists('child_guardian');
        Schema::dropIfExists('children');
        Schema::dropIfExists('classrooms');
    }
};
