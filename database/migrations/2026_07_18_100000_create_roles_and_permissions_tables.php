<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-user permissions for staff (back-office users). Permissions are assigned
 * directly to each user as a JSON array of names — no roles. The catalogue
 * (permission_groups + permissions) exists only to drive the admin UI; the
 * user's `permissions` array is the source of truth. `is_super` grants all.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permission_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permission_group_id')->constrained()->cascadeOnDelete();
            $table->string('name')->unique();
            $table->string('display_name');
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_super')->default(false)->after('user_type');
            $table->json('permissions')->nullable()->after('is_super');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_super', 'permissions']);
        });
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('permission_groups');
    }
};
