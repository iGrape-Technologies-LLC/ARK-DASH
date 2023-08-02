<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\User;
use App\Models\Address;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $superadmin = new User();
        $superadmin->name = 'Dnero';
        $superadmin->lastname = 'Superbowl';
        $superadmin->email = 'roney@dneroapp.com';
        $superadmin->password = Hash::make('Naa654897');
        $superadmin->email_verified_at = date('Y-m-d H:i:s');
        $superadmin->approved_at = date('Y-m-d H:i:s');
        $superadmin->city_id = 1;
        $superadmin->is_staff = 1;
        $superadmin->save();

        $role_superadmin = Role::create(['name' => 'SuperAdmin', 'is_default' => '1']);
        $role_admin = Role::create(['name' => 'Admin', 'is_staff' => '1']);
        $role_employee = Role::create(['name' => 'Empleado', 'is_staff' => '1']);

        Role::create(['name' => 'Cliente', 'is_staff' => '0']);

        $permissionsSuperadmin = Permission::pluck('id','id')->all();

        $permissionsAdmin = Permission::
        where('name', 'like', 'analytic.%')
        ->orWhere('name', 'like', 'newsletter.%')
        ->orWhere('name', 'like', 'role.%')
        ->orWhere('name', 'like', 'customer.%')
        ->orWhere('name', 'like', 'staff.%')
        ->orWhere('name', 'like', 'activity_log.%')
        ->orWhere('name', 'like', 'notification.%')
        ->orWhere('name', 'like', 'article.%')
        ->orWhere('name', 'like', 'advertisement.%')
        ->orWhere('name', 'like', 'category.%')
        ->orWhere('name', 'like', 'property.%')
        ->orWhere('name', 'like', 'feature.%')
        ->orWhere('name', 'like', 'notice.%')
        ->orWhere('name', 'like', 'notice_category.%')
        ->orWhere('name', 'like', 'import.%')
        ->orWhere('name', 'like', 'city.%')
        ->orWhere('name', 'like', 'discount.%')
        ->orWhere('name', 'like', 'brand.%')
        ->orWhere('name', 'like', 'sell.%')
        ->orWhere('name', 'like', 'whatsapp.%')
        ->orWhere('name', 'like', 'status.%')
        ->orWhere('name', 'like', 'tag.%')
        ->orWhere('name', 'like', 'subsidiary.%')
        ->orWhere('name', 'like', 'push_notification.%')
        ->get()
        ->pluck('id', 'id');

        $role_superadmin->syncPermissions($permissionsSuperadmin);
        $role_admin->syncPermissions($permissionsAdmin);
        $role_employee->syncPermissions($permissionsAdmin);

        $superadmin->roles()->attach($role_superadmin->id, ['model_type' => 'App\User']);
    }
}
