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
 * Photos attach through the existing polymorphic `mediables` pivot (HasMedia),
 * so nothing here carries a media_id and "where is this file used?" keeps working.
 */
return new class extends Migration
{
    public function up(): void
    {
        // "Mr James · Grade 1 · 2026/2027". `year` is a plain string — deliberately
        // not a terms/sessions system.
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

        // The roster. No login, no password — `invite_code` is how a parent links
        // themselves to this child, which is the whole relationship system.
        Schema::create('children', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classroom_id')->nullable()->constrained('classrooms')->nullOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->date('dob')->nullable();
            $table->text('notes')->nullable();
            $table->string('invite_code', 12)->nullable()->unique();
            $table->timestamps();
            $table->softDeletes();

            $table->index('classroom_id');
        });

        // Many-to-many: a parent can have several children, a child can have
        // several guardians. Three columns is the entire "relation system".
        Schema::create('child_guardian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained('children')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('relationship')->default('guardian'); // mum | dad | guardian
            $table->timestamps();

            $table->unique(['child_id', 'user_id']);
        });

        // The class feed (ClassDojo's "Class Story"): broadcast to every guardian
        // of every child in the room.
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classroom_id')->constrained('classrooms')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->timestamps();

            $table->index(['classroom_id', 'created_at']);
        });

        // One thread per room ↔ guardian. Scoped to the *classroom*, not the
        // teacher, so co-teachers can be added later with no schema change.
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classroom_id')->constrained('classrooms')->cascadeOnDelete();
            $table->foreignId('guardian_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('last_message_at')->nullable();
            // Two sides, so unread needs no participants table.
            $table->timestamp('teacher_read_at')->nullable();
            $table->timestamp('guardian_read_at')->nullable();
            $table->timestamps();

            $table->unique(['classroom_id', 'guardian_id']);
            $table->index('last_message_at');
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('conversations')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->timestamps();

            $table->index(['conversation_id', 'created_at']);
        });

        // One report per child per day. Stays private until `published_at` is set.
        Schema::create('daily_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained('children')->cascadeOnDelete();
            $table->date('date');
            $table->string('mood')->nullable(); // happy | ok | sad | tired
            $table->text('summary')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['child_id', 'date']);
            $table->index('date');
        });

        // A flat timeline of events. One table with a `type` beats a table per
        // kind of event — naps/meals/nappies differ only in which fields they use.
        Schema::create('report_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_report_id')->constrained('daily_reports')->cascadeOnDelete();
            $table->string('type'); // nap | meal | nappy | note | photo
            $table->timestamp('occurred_at')->nullable();
            $table->timestamp('ended_at')->nullable(); // naps are a range
            $table->string('detail')->nullable();      // "Ate all", "Wet", …
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['daily_report_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_entries');
        Schema::dropIfExists('daily_reports');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('conversations');
        Schema::dropIfExists('posts');
        Schema::dropIfExists('child_guardian');
        Schema::dropIfExists('children');
        Schema::dropIfExists('classrooms');
    }
};
