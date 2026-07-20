<?php

namespace App\Policies;

use App\Models\Classroom;
use App\Models\User;

/**
 * Who may enter a room.
 *
 * Admins run the daycare, a teacher runs the room they're assigned to, and a
 * parent reaches a room only through their own child. Creating classes and
 * children is admin-only.
 */
class ClassroomPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // scoped by Classroom::visibleTo()
    }

    public function view(User $user, Classroom $classroom): bool
    {
        return $user->isAdmin()
            || $this->teaches($user, $classroom)
            || $classroom->hasGuardian($user);
    }

    /** Only staff post to the feed, chat as the room, or fill in reports. */
    public function staff(User $user, Classroom $classroom): bool
    {
        return $user->isAdmin() || $this->teaches($user, $classroom);
    }

    /** A room may have several teachers; the pivot is authoritative, with the
     *  legacy `teacher_id` kept as a fallback. */
    protected function teaches(User $user, Classroom $classroom): bool
    {
        return $classroom->teacher_id === $user->id
            || $classroom->teachers()->whereKey($user->id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Classroom $classroom): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Classroom $classroom): bool
    {
        return $user->isAdmin();
    }
}
