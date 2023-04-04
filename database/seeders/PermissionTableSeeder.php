<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionTableSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
            'user-list',
            'user-create',
            'user-edit',
            'user-delete',
            'role-list',
            'role-create',
            'role-edit',
            'role-delete',
            'accountgroup-list',
            'accountgroup-create',
            'accountgroup-edit',
            'accountgroup-delete',
            'accountposting',
            'accounttype-list',
            'accounttype-create',
            'accounttype-edit',
            'accounttype-delete',
            'bank-list',
            'bank-create',
            'bank-edit',
            'bank-delete',
            'stakeholder-list',
            'stakeholder-create',
            'stakeholder-edit',
            'stakeholder-delete',
            'branch-list',
            'branch-create',
            'branch-edit',
            'branch-delete',
            'department-list',
            'department-create',
            'department-edit',
            'department-delete',
            'company-list',
            'company-create',
            'company-edit',
            'company-delete',
            'contactperson-list',
            'contactperson-create',
            'contactperson-edit',
            'contactperson-delete',
            'introducer-list',
            'introducer-create',
            'introducer-edit',
            'introducer-delete',
            'nominee-list',
            'nominee-create',
            'nominee-edit',
            'nominee-delete',
            'collection-list',
            'collection-create',
            'collection-edit',
            'collection-delete',
            'view-pending-deposits',
            'view-approved-deposits',
            'create-deposits',
            'reverse-collection',
            'employee-list',
            'employee-create',
            'employee-edit',
            'employee-delete',
            'employee-transfer',
            'proprietor-list',
            'proprietor-create',
            'proprietor-edit',
            'proprietor-delete',
            'refund-process',
            'refund-payments',
            'approve-refunds',
            'refund-excess-payment',
            'account-ledger-report',
            'fund-balance-report',
            'companywise-statement-report',
            'daily-collection-report',
            'individual-statement-report',
            'view-reports',
            'monthly-deposit-report',
            'monthly-refund-report',
            'renewal-list-report',
            'trial-balance-report'
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission, 'guard_name' => 'api']);
        }
    }
}
