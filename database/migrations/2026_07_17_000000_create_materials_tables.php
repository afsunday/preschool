<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The learning-materials library: downloadable forms/guides, videos, and
 * articles for families, shown on the home teaser and the resources page.
 *
 * This is relational domain data, NOT page-builder content — it grows, it's
 * searched and filtered, and the same rows surface on two pages. A resources
 * block owns the *query* (which category, how many, the layout); the tables
 * own the *data*. Named `materials`, not `resources`, to avoid colliding with
 * Laravel's resource controllers / API resources.
 */
return new class extends Migration
{
    public function up(): void
    {
        // The filter tabs on the resources page. Admin-managed so a new topic
        // ("Toddlers", "Parenting") doesn't need a code change.
        Schema::create('material_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()
                ->constrained('material_categories')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            // download | video | article — drives the CTA label/icon and whether
            // the card points at a file or a link.
            $table->string('type')->default('article');
            $table->string('url')->nullable();
            $table->string('image_path')->nullable();
            $table->boolean('is_featured')->default(false)->index();
            $table->unsignedInteger('position')->default(0);
            // Visibility gate, like pages: null = draft, hidden from the site.
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('materials');
        Schema::dropIfExists('material_categories');
    }
};
