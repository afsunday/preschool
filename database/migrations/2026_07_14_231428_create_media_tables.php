<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();

            // Where the file physically lives.
            $table->string('disk')->default('public');
            $table->string('path');
            $table->string('filename');
            $table->string('original_name');
            $table->string('extension', 32)->nullable();
            $table->string('mime_type')->nullable();

            // High-level bucket, drives the filter chips.
            $table->string('kind', 16)->default('other')->index();

            $table->unsignedBigInteger('size')->default(0);

            // Images only; used for the card aspect ratio (native getimagesize()).
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();

            // Editable, searchable metadata.
            $table->string('title')->nullable();
            $table->string('alt')->nullable();
            $table->text('description')->nullable();

            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['title', 'alt', 'original_name']);
        });

        // Polymorphic pivot: which model/field a media item is attached to.
        // Lets us answer "where is this used?" and block deleting in-use files.
        Schema::create('mediables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_id')->constrained('media')->cascadeOnDelete();
            $table->morphs('mediable');
            $table->string('collection')->default('default');
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->unique(['media_id', 'mediable_type', 'mediable_id', 'collection'], 'mediables_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mediables');
        Schema::dropIfExists('media');
    }
};
