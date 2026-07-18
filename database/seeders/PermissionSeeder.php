<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\PermissionGroup;
use App\Models\User;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * The permission catalogue — grouped for the staff-editing UI. Each
     * permission's `name` is what a route and User::hasPermission() check.
     */
    public const CATALOG = [
        'Content' => [
            'content.pages' => 'Manage pages',
            'content.resources' => 'Manage resources',
            'content.media' => 'Manage media',
        ],
        'Communication' => [
            'comms.messages' => 'Manage contact messages',
            'comms.newsletter' => 'Manage newsletter',
        ],
        'Team' => [
            'team.staff' => 'Manage staff & permissions',
        ],
    ];

    public function run(): void
    {
        $position = 0;

        foreach (self::CATALOG as $groupName => $permissions) {
            $group = PermissionGroup::updateOrCreate(
                ['name' => $groupName],
                ['position' => $position++],
            );

            foreach ($permissions as $name => $displayName) {
                Permission::updateOrCreate(
                    ['name' => $name],
                    ['permission_group_id' => $group->id, 'display_name' => $displayName],
                );
            }
        }

        // Existing admins keep full access as super users.
        User::query()->where('user_type', User::ADMIN)->update(['is_super' => true]);
    }
}
