<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = [
           'analytic.view',
           'newsletter.list',
           'role.list',
           'role.create',
           'role.edit',
           'role.delete',
           'role.view',

           'staff.list',
           'staff.create',
           'staff.edit',
           'staff.delete',
           'staff.view',

           'customer.list',
           'customer.create',
           'customer.edit',
           'customer.delete',
           'customer.view',

           'activity_log.list',
           'activity_log.create',
           'activity_log.edit',
           'activity_log.delete',
           'activity_log.view',

           'notification.list',
           'notification.create',
           'notification.edit',
           'notification.delete',
           'notification.view',

           'article.list',
           'article.create',
           'article.edit',
           'article.delete',
           'article.view',

           'advertisement.list',
           'advertisement.create',
           'advertisement.edit',
           'advertisement.delete',
           'advertisement.view',

           'category.list',
           'category.create',
           'category.edit',
           'category.delete',
           'category.view',

           'property.list',
           'property.create',
           'property.edit',
           'property.delete',
           'property.view',

           'feature.list',
           'feature.create',
           'feature.edit',
           'feature.delete',
           'feature.view',

           'notice.list',
           'notice.view',
           'notice.create',
           'notice.edit',
           'notice.delete',

           'notice_category.list',
           'notice_category.view',
           'notice_category.create',
           'notice_category.edit',
           'notice_category.delete',

           'import.products',
           'city.list',
           'city.view',
           'city.create',
           'city.edit',
           'city.delete',

           'discount.list',
           'discount.create',
           'discount.edit',
           'discount.delete',
           'discount.view',

           'brand.list',
           'brand.view',
           'brand.create',
           'brand.edit',
           'brand.delete',

           'sell.list',
           'sell.view',
           'sell.create',
           'sell.edit',
           'sell.delete',

           'whatsapp.list',
           'whatsapp.view',
           'whatsapp.create',
           'whatsapp.edit',
           'whatsapp.delete',

           'status.list',
           'status.view',
           'status.create',
           'status.edit',
           'status.delete',

           'tag.list',
           'tag.view',
           'tag.create',
           'tag.edit',
           'tag.delete',

           'subsidiary.list',
           'subsidiary.view',
           'subsidiary.create',
           'subsidiary.edit',
           'subsidiary.delete',

           'push_notification.list',
           'push_notification.view',
           'push_notification.create',
           'push_notification.edit',
           'push_notification.delete',

           'api.list',
           'api.create',
           'api.edit',
           'api.delete',
           'api.view'
        ];

        foreach ($permissions as $permission) {
             Permission::create(['name' => $permission]);
        }
    }
}
