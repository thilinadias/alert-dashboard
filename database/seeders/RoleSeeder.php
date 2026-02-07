<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        Permission::firstOrCreate(['name' => 'manage_users']);
        Permission::firstOrCreate(['name' => 'view_reports']);
        Permission::firstOrCreate(['name' => 'handle_alerts']); // Take, Resolve, Close
        Permission::firstOrCreate(['name' => 'manage_settings']); // SLAs, Rules, Clients

        // Create roles and assign existing permissions
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->syncPermissions(Permission::all());

        $managerRole = Role::firstOrCreate(['name' => 'manager']);
        $managerRole->syncPermissions(['view_reports', 'handle_alerts', 'manage_users']);

        $handlerRole = Role::firstOrCreate(['name' => 'alert_handler']);
        $handlerRole->syncPermissions(['handle_alerts']);

        $analystRole = Role::firstOrCreate(['name' => 'noc_analyst']); // Keep for backward compatibility
        $analystRole->syncPermissions(['handle_alerts']);

        $reportUserRole = Role::firstOrCreate(['name' => 'report_user']);
        $reportUserRole->syncPermissions(['view_reports']);
    }
}
