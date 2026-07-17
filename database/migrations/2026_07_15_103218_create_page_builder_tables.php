<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('status', 16)->default('draft'); // draft | published
            $table->timestamp('published_at')->nullable();

            // SEO
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->foreignId('og_media_id')->nullable()->constrained('media')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });

        // A page's content: an ordered list of blocks. Each names a block type
        // (see the `blockTypes` map in the blueprints) and carries that type's
        // field values in `settings`.
        Schema::create('page_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained('pages')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('page_blocks')->cascadeOnDelete();
            $table->string('type'); // block type key, e.g. "home_hero"

            // Stable identity from the blueprint, so `pull` can tell an existing
            // block from a new one without guessing by type. Null for blocks
            // added in the editor — sync must never touch those.
            $table->string('key')->nullable();

            $table->string('name')->nullable();   // human label in the editor
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->json('settings')->nullable(); // this block's field values
            $table->unsignedInteger('schema_version')->default(1);
            $table->timestamps();

            $table->index(['page_id', 'position']);
            $table->index(['page_id', 'name']);
            $table->unique(['page_id', 'key']);
        });

        Schema::create('page_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained('pages')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('snapshot'); // full page + blocks at save time
            $table->timestamps();

            $table->index(['page_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_revisions');
        Schema::dropIfExists('page_blocks');
        Schema::dropIfExists('pages');
    }
};
