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
            $table->string('status', 16)->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->boolean('is_system')->default(false);
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('og_image')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('page_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained('pages')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('page_blocks')->cascadeOnDelete();
            $table->string('type');

            $table->string('key')->nullable();

            $table->string('name')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->json('settings')->nullable();
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
            $table->json('snapshot');
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
