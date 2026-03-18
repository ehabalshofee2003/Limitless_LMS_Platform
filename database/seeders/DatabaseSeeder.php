<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // الترتيب مهم جداً هنا
        $this->call([
            RoleSeeder::class,      // 1. إنشاء الأدوار والمشرف
            DemoCourseSeeder::class,// 2. إنشاء الدورة التجريبية (التي تعتمد على وجود دور institution)
        ]);
    }
}