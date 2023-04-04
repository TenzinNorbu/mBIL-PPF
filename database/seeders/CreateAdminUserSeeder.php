<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CreateAdminUserSeeder extends Seeder
{
    public function run()
    {
        $user = User::create([
            'name' => 'Ugyen Rangdrol',
            'email' => 'admin@bil.bt',
            'password' => bcrypt('admin@bil_123'),
            'dob' => '1996/06/23',
            'designation' => 'Development Officer',
            'employee_id' => 'BIL/2021/E0174',
            'mobile_number' => '17585568',
            'phone_number' => '0234785',
            'users_branch_id' => 1,
            'users_department_id' => 1,
            'cid' => '10203002021',
            'gender' => 'Male',
            'status' => "Active",
        ]);

        $role = Role::create(['name' => 'Admin', 'guard_name' => 'api']);

        $permissions = Permission::pluck('id', 'id')->all();

        $role->syncPermissions($permissions);

        $user->assignRole([$role->id]);
    }
}
