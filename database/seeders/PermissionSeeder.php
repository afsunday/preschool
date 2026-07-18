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
            'team.staff' => 'Manage team & permissions',
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

        // Everyone with back-office access keeps full access as a super user.
        User::query()->where('has_admin_access', true)->update(['is_super' => true]);
    }
}
