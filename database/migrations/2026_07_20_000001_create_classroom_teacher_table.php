<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classroom_teacher', function (Blueprint $table) {
            $table->foreignId('classroom_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->primary(['classroom_id', 'user_id']);
        });

        // A room can now have many teachers; carry the existing single teacher
        // into the pivot so nobody loses access to the room they run.
        DB::statement(
            'INSERT INTO classroom_teacher (classroom_id, user_id)
             SELECT id, teacher_id FROM classrooms WHERE teacher_id IS NOT NULL'
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('classroom_teacher');
    }
};
