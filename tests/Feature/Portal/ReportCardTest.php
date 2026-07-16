<?php

use App\Models\Child;
use App\Models\Classroom;
use App\Models\ReportCard;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Report cards — the termly document. Staff upload and choose when to share; a
 * guardian only ever reads, and only their own child's shared cards.
 *
 * The file never touches the media library and is never a public URL.
 */
beforeEach(function () {
    Storage::fake(ReportCard::DISK);

    $this->teacher = User::factory()->teacher()->create();
    $this->classroom = Classroom::factory()->create(['teacher_id' => $this->teacher->id]);

    $this->parent = User::factory()->parent()->create();
    $this->child = Child::factory()->create(['classroom_id' => $this->classroom->id]);
    $this->child->guardians()->attach($this->parent->id, ['relationship' => 'mum']);

    $this->pdf = UploadedFile::fake()->create('tunde-term-1.pdf', 120, 'application/pdf');
});

test('a teacher uploads a report card and the file lands on the private disk', function () {
    $this->actingAs($this->teacher)
        ->post(route('portal.report-cards.store', $this->child), [
            'title' => 'Term 1 · 2026/2027',
            'issued_on' => '2026-12-12',
            'file' => $this->pdf,
        ])
        ->assertRedirect();

    $card = ReportCard::firstWhere('child_id', $this->child->id);

    expect($card)->not->toBeNull()
        ->and($card->original_name)->toBe('tunde-term-1.pdf')
        ->and($card->path)->toStartWith("report-cards/{$this->child->id}/")
        // Uploaded is not shared.
        ->and($card->isPublished())->toBeFalse();

    Storage::disk(ReportCard::DISK)->assertExists($card->path);
});

test('the stored path is not guessable from the original filename', function () {
    // Otherwise anyone could reason their way to another child's document.
    $this->actingAs($this->teacher)->post(route('portal.report-cards.store', $this->child), [
        'title' => 'Term 1',
        'file' => $this->pdf,
    ]);

    expect(ReportCard::first()->path)->not->toContain('tunde-term-1');
});

test('a card needs a file', function () {
    $this->actingAs($this->teacher)
        ->post(route('portal.report-cards.store', $this->child), ['title' => 'Term 1'])
        ->assertSessionHasErrors('file');

    expect(ReportCard::count())->toBe(0);
});

test('an executable is refused', function () {
    $this->actingAs($this->teacher)
        ->post(route('portal.report-cards.store', $this->child), [
            'title' => 'Term 1',
            'file' => UploadedFile::fake()->create('nasty.php', 4, 'application/x-php'),
        ])
        ->assertSessionHasErrors('file');
});

// ---- who can open the file ------------------------------------------------

test('a guardian can download their child\'s card once it is shared', function () {
    $card = ReportCard::factory()->create(['child_id' => $this->child->id]);
    Storage::disk(ReportCard::DISK)->put($card->path, 'pdf-bytes');

    // Not shared yet.
    $this->actingAs($this->parent)
        ->get(route('portal.report-cards.download', [$this->child, $card]))
        ->assertForbidden();

    $card->publish();

    $this->actingAs($this->parent)
        ->get(route('portal.report-cards.download', [$this->child, $card]))
        ->assertOk()
        ->assertDownload('report.pdf');
});

test('staff can download an unshared card', function () {
    $card = ReportCard::factory()->create(['child_id' => $this->child->id]);
    Storage::disk(ReportCard::DISK)->put($card->path, 'pdf-bytes');

    $this->actingAs($this->teacher)
        ->get(route('portal.report-cards.download', [$this->child, $card]))
        ->assertOk();
});

test('another family cannot download a shared card', function () {
    $stranger = User::factory()->parent()->create();
    Child::factory()->create(['classroom_id' => $this->classroom->id])
        ->guardians()->attach($stranger->id, ['relationship' => 'dad']);

    $card = ReportCard::factory()->published()->create(['child_id' => $this->child->id]);
    Storage::disk(ReportCard::DISK)->put($card->path, 'pdf-bytes');

    $this->actingAs($stranger)
        ->get(route('portal.report-cards.download', [$this->child, $card]))
        ->assertForbidden();
});

test('a teacher from another room cannot download', function () {
    $card = ReportCard::factory()->published()->create(['child_id' => $this->child->id]);
    Storage::disk(ReportCard::DISK)->put($card->path, 'pdf-bytes');

    $this->actingAs(User::factory()->teacher()->create())
        ->get(route('portal.report-cards.download', [$this->child, $card]))
        ->assertForbidden();
});

test('a guest cannot download', function () {
    $card = ReportCard::factory()->published()->create(['child_id' => $this->child->id]);

    $this->get(route('portal.report-cards.download', [$this->child, $card]))
        ->assertRedirect(route('login'));
});

// ---- lifecycle -------------------------------------------------------------

test('deleting a card removes its file from disk', function () {
    $this->actingAs($this->teacher)->post(route('portal.report-cards.store', $this->child), [
        'title' => 'Term 1',
        'file' => $this->pdf,
    ]);

    $card = ReportCard::first();
    $path = $card->path;

    $this->actingAs($this->teacher)
        ->delete(route('portal.report-cards.destroy', [$this->child, $card]))
        ->assertRedirect();

    Storage::disk(ReportCard::DISK)->assertMissing($path);
    expect(ReportCard::count())->toBe(0);
});

test('a parent sees a card in the roster only once shared', function () {
    $card = ReportCard::factory()->create(['child_id' => $this->child->id]);

    $this->actingAs($this->parent)
        ->get(route('portal.classes.students', $this->classroom))
        ->assertInertia(fn ($p) => $p->has('children.0.reportCards', 0));

    $card->publish();

    $this->actingAs($this->parent)
        ->get(route('portal.classes.students', $this->classroom))
        ->assertInertia(fn ($p) => $p
            ->has('children.0.reportCards', 1)
            ->where('children.0.reportCards.0.published', true));
});

test('staff see unshared cards', function () {
    ReportCard::factory()->create(['child_id' => $this->child->id]);

    $this->actingAs($this->teacher)
        ->get(route('portal.classes.students', $this->classroom))
        ->assertInertia(fn ($p) => $p
            ->has('children.0.reportCards', 1)
            ->where('children.0.reportCards.0.published', false));
});

test('a parent never sees another family\'s cards', function () {
    $other = Child::factory()->create(['classroom_id' => $this->classroom->id]);
    ReportCard::factory()->published()->create(['child_id' => $other->id]);

    $this->actingAs($this->parent)
        ->get(route('portal.classes.students', $this->classroom))
        ->assertInertia(fn ($p) => $p->has('children', 2)->has('children.1.reportCards', 0));
});

test('the switch shares and unshares', function () {
    $card = ReportCard::factory()->create(['child_id' => $this->child->id]);

    $this->actingAs($this->teacher)
        ->patch(route('portal.report-cards.update', [$this->child, $card]), ['published' => true]);
    expect($card->fresh()->isPublished())->toBeTrue();

    $this->actingAs($this->teacher)
        ->patch(route('portal.report-cards.update', [$this->child, $card]), ['published' => false]);
    expect($card->fresh()->isPublished())->toBeFalse();
});

test('a rename leaves sharing alone', function () {
    $card = ReportCard::factory()->published()->create(['child_id' => $this->child->id]);

    $this->actingAs($this->teacher)
        ->patch(route('portal.report-cards.update', [$this->child, $card]), ['title' => 'Term 2']);

    expect($card->fresh()->title)->toBe('Term 2')
        ->and($card->fresh()->isPublished())->toBeTrue();
});

test('a parent cannot upload, share or delete', function () {
    $card = ReportCard::factory()->create(['child_id' => $this->child->id]);

    $this->actingAs($this->parent)
        ->post(route('portal.report-cards.store', $this->child), [
            'title' => 'Fake', 'file' => $this->pdf,
        ])
        ->assertForbidden();

    $this->actingAs($this->parent)
        ->patch(route('portal.report-cards.update', [$this->child, $card]), ['published' => true])
        ->assertForbidden();

    $this->actingAs($this->parent)
        ->delete(route('portal.report-cards.destroy', [$this->child, $card]))
        ->assertForbidden();
});

test('a card belonging to another child is a 404, not a silent success', function () {
    $other = Child::factory()->create(['classroom_id' => $this->classroom->id]);
    $card = ReportCard::factory()->create(['child_id' => $other->id]);

    $this->actingAs($this->teacher)
        ->patch(route('portal.report-cards.update', [$this->child, $card]), ['published' => true])
        ->assertNotFound();
});
