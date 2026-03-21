<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    // All permissions in the system — format: module-action
    private const PERMISSIONS = [
        'categories-view',
        'categories-create',
        'categories-edit',
        'categories-delete',
        'categories-status',
        'articles-view',
        'articles-save',
        'digest-view',
        'digest-settings',
        'preferences-view',
        'preferences-edit',
    ];

    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create all permissions
        foreach (self::PERMISSIONS as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // --- Roles ---

        // admin: all permissions (managed via hasRole check in abilityRules)
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions(self::PERMISSIONS);

        // editor: content management, no delete/status change on categories
        $editor = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);
        $editor->syncPermissions([
            'categories-view',
            'categories-create',
            'categories-edit',
            'articles-view',
            'articles-save',
            'digest-view',
            'digest-settings',
            'preferences-view',
            'preferences-edit',
        ]);

        // user: read-only on content, full access to own digest & preferences
        $user = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);
        $user->syncPermissions([
            'articles-view',
            'articles-save',
            'digest-view',
            'digest-settings',
            'preferences-view',
            'preferences-edit',
        ]);

        // --- Seed Users ---

        $adminUser = User::updateOrCreate(
            ['email' => 'admin@ainewsfilter.com'],
            ['name' => 'Admin', 'password' => bcrypt('admin123')]
        );
        $adminUser->syncRoles(['admin']);

        $editorUser = User::updateOrCreate(
            ['email' => 'editor@ainewsfilter.com'],
            ['name' => 'Editor', 'password' => bcrypt('editor123')]
        );
        $editorUser->syncRoles(['editor']);

        $regularUser = User::updateOrCreate(
            ['email' => 'user@ainewsfilter.com'],
            ['name' => 'User', 'password' => bcrypt('user1234')]
        );
        $regularUser->syncRoles(['user']);
    }
}
