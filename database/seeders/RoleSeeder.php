<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role; // مهم جداً
use App\Models\User; // مهم جداً
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // مسح الكاش للأدوار (لضمان التحديث)
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. إنشاء الأدوار
        Role::firstOrCreate(['name' => 'super_admin']);
        Role::firstOrCreate(['name' => 'institution']);
        Role::firstOrCreate(['name' => 'instructor']);
        Role::firstOrCreate(['name' => 'student']);

        // 2. إنشاء المشرف
        $admin = User::firstOrCreate(
            ['email' => 'admin@edutrack.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password')
            ]
        );
        $admin->assignRole('super_admin');
    }
}