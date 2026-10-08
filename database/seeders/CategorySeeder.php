<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Seed document categories.
     * Full data populated in FEAT-002.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Affidavits',          'icon' => '⚖️',  'description' => 'Sworn legal statements'],
            ['name' => 'Letters',             'icon' => '✉️',  'description' => 'Official correspondence'],
            ['name' => 'Land & Property',     'icon' => '🏡',  'description' => 'Land transfer and property documents'],
            ['name' => 'Business',            'icon' => '💼',  'description' => 'Business registration and agreements'],
            ['name' => 'Personal',            'icon' => '👤',  'description' => 'Personal documents and declarations'],
            ['name' => 'Government Forms',    'icon' => '🏛️',  'description' => 'Standard government application forms'],
        ];

        foreach ($categories as $cat) {
            DB::table('categories')->updateOrInsert(
                ['slug' => Str::slug($cat['name'])],
                array_merge($cat, [
                    'slug'       => Str::slug($cat['name']),
                    'is_active'  => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
