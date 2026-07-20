<?php

namespace Database\Seeders;

use App\Models\Child;
use App\Models\Classroom;
use App\Models\Conversation;
use App\Models\DailyReport;
use App\Models\Message;
use App\Models\Post;
use App\Models\ReportEntry;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * A small but realistic daycare: two rooms, two teachers, children, parents.
 *
 * Deliberately seeds the case that shapes the model — a parent with two children
 * in two different rooms — so the "one parent, many kids" path is exercised the
 * moment you open the portal. Password for every account is "password".
 */
class PortalSeeder extends Seeder
{
    public function run(): void
    {
        $james = User::factory()->teacher()->create([
            'first_name' => 'James',
            'last_name' => 'Okafor',
            'email' => 'james@example.com',
        ]);

        $ada = User::factory()->teacher()->create([
            'first_name' => 'Ada',
            'last_name' => 'Bello',
            'email' => 'ada@example.com',
        ]);

        $grade1 = Classroom::factory()->create([
            'name' => 'Mr James',
            'grade' => 'Grade 1',
            'year' => '2026/2027',
            'teacher_id' => $james->id,
            'color' => '#159cb0',
            'banner' => 'blocks',
        ]);

        $toddlers = Classroom::factory()->create([
            'name' => 'Ms Ada',
            'grade' => 'Toddlers',
            'year' => '2026/2027',
            'teacher_id' => $ada->id,
            'color' => '#f0a020',
            'banner' => 'art-table',
        ]);

        // Rooms carry their teachers via the pivot now (a room may have several).
        // The factory already mirrors teacher_id into the pivot, so sync — don't
        // re-attach — to stay idempotent.
        $grade1->teachers()->syncWithoutDetaching([$james->id]);
        $toddlers->teachers()->syncWithoutDetaching([$ada->id]);

        // The parent who proves the model: one account, two kids, two rooms.
        $bisi = User::factory()->parent()->create([
            'first_name' => 'Bisi',
            'last_name' => 'Adeyemi',
            'email' => 'bisi@example.com',
        ]);

        $tunde = Child::factory()->create([
            'classroom_id' => $grade1->id,
            'first_name' => 'Tunde',
            'last_name' => 'Adeyemi',
            'invite_code' => 'TUNDE001',
        ]);

        $zara = Child::factory()->create([
            'classroom_id' => $toddlers->id,
            'first_name' => 'Zara',
            'last_name' => 'Adeyemi',
            'invite_code' => 'ZARA0001',
        ]);

        $tunde->guardians()->attach($bisi->id, ['relationship' => 'mum']);
        $zara->guardians()->attach($bisi->id, ['relationship' => 'mum']);

        // A child with two guardians — the other half of the pivot.
        $kola = User::factory()->parent()->create([
            'first_name' => 'Kola',
            'last_name' => 'Balogun',
            'email' => 'kola@example.com',
        ]);
        $ngozi = User::factory()->parent()->create([
            'first_name' => 'Ngozi',
            'last_name' => 'Balogun',
            'email' => 'ngozi@example.com',
        ]);

        $ife = Child::factory()->create([
            'classroom_id' => $grade1->id,
            'first_name' => 'Ife',
            'last_name' => 'Balogun',
            'invite_code' => 'IFE00001',
        ]);
        $ife->guardians()->attach($kola->id, ['relationship' => 'dad']);
        $ife->guardians()->attach($ngozi->id, ['relationship' => 'mum']);

        // A few more children so the roster isn't bare.
        Child::factory()->count(4)->create(['classroom_id' => $grade1->id]);
        Child::factory()->count(3)->create(['classroom_id' => $toddlers->id]);

        // Class feed.
        Post::factory()->create([
            'classroom_id' => $grade1->id,
            'user_id' => $james->id,
            'body' => 'Painting day! The whole room made a mural of the school garden. Aprons went home in book bags — please send them back Monday.',
        ]);
        Post::factory()->create([
            'classroom_id' => $grade1->id,
            'user_id' => $james->id,
            'body' => 'Reminder: swimming on Thursday. Pack a towel and a change of clothes.',
        ]);

        $thread = Conversation::factory()->forGuardian($bisi)->create([
            'classroom_id' => $grade1->id,
            'last_message_at' => now(),
        ]);
        Message::factory()->create([
            'conversation_id' => $thread->id,
            'user_id' => $james->id,
            'body' => 'Tunde settled in really well today.',
            'created_at' => now()->subHour(),
        ]);
        Message::factory()->create([
            'conversation_id' => $thread->id,
            'user_id' => $bisi->id,
            'body' => 'Thank you! He talked about the painting all evening.',
            'created_at' => now(),
        ]);

        // Today's report for Tunde — published, so his mum can see it.
        $report = DailyReport::factory()->published()->create([
            'child_id' => $tunde->id,
            'date' => today(),
            'summary' => 'A brilliant day — lots of painting and a good long nap.',
            'created_by' => $james->id,
        ]);
        ReportEntry::factory()->nap()->create(['daily_report_id' => $report->id]);
        ReportEntry::factory()->meal()->create(['daily_report_id' => $report->id, 'detail' => 'Ate all']);
        ReportEntry::factory()->nappy()->create(['daily_report_id' => $report->id]);
        ReportEntry::factory()->create([
            'daily_report_id' => $report->id,
            'type' => 'mood',
            'detail' => 'Happy',
            'note' => null,
            'occurred_at' => today()->setTime(9, 15),
        ]);

        // Zara's is still a draft — the teacher hasn't sent it yet.
        DailyReport::factory()->create([
            'child_id' => $zara->id,
            'date' => today(),
            'created_by' => $ada->id,
        ]);
    }
}
