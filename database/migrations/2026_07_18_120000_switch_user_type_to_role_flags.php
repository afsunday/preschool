<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Collapse the single `user_type` label into the flag model: a person is a
 * `parent` (a family) or `staff` (an employee), and `has_admin_access` is the
 * separate back-office door. An old admin becomes staff *with* access; an old
 * teacher becomes staff *without* it. Parent-ness is expressed through having a
 * linked child, so a family account carries no extra flag.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('has_admin_access')->default(false)->after('user_type');
        });

        DB::table('users')->where('user_type', 'admin')->update([
            'user_type' => 'staff',
            'has_admin_access' => true,
        ]);
        DB::table('users')->where('user_type', 'teacher')->update([
            'user_type' => 'staff',
        ]);
        DB::table('users')->whereIn('user_type', ['parent', 'user'])->update([
            'user_type' => 'parent',
        ]);
    }

    public function down(): void
    {
        DB::table('users')->where('has_admin_access', true)->update([
            'user_type' => 'admin',
        ]);
        DB::table('users')->where('user_type', 'staff')->update([
            'user_type' => 'teacher',
        ]);

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('has_admin_access');
        });
    }
};
