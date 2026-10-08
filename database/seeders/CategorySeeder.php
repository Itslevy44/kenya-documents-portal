<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Seed 7 document categories.
     */
    public function run(): void
    {
        $categories = [
            [
                'name'        => 'Letters',
                'slug'        => 'letters',
                'description' => 'Official letters and correspondence',
                'icon'        => '✉️',
                'is_active'   => true,
                'sort_order'  => 1,
            ],
            [
                'name'        => 'Affidavits',
                'slug'        => 'affidavits',
                'description' => 'Sworn legal statements and affidavits',
                'icon'        => '⚖️',
                'is_active'   => true,
                'sort_order'  => 2,
            ],
            [
                'name'        => 'Business Documents',
                'slug'        => 'business',
                'description' => 'Business letters, introductions, and agreements',
                'icon'        => '💼',
                'is_active'   => true,
                'sort_order'  => 3,
            ],
            [
                'name'        => 'School Documents',
                'slug'        => 'school',
                'description' => 'School letters and education-related documents',
                'icon'        => '🎓',
                'is_active'   => true,
                'sort_order'  => 4,
            ],
            [
                'name'        => 'Legal Agreements',
                'slug'        => 'agreements',
                'description' => 'Legally binding agreements and contracts',
                'icon'        => '📋',
                'is_active'   => true,
                'sort_order'  => 5,
            ],
            [
                'name'        => 'Employment',
                'slug'        => 'employment',
                'description' => 'Employment letters and work-related documents',
                'icon'        => '👔',
                'is_active'   => true,
                'sort_order'  => 6,
            ],
            [
                'name'        => 'Community',
                'slug'        => 'community',
                'description' => 'Community and CBO documents',
                'icon'        => '🤝',
                'is_active'   => true,
                'sort_order'  => 7,
            ],
        ];

        foreach ($categories as $data) {
            Category::updateOrCreate(['slug' => $data['slug']], $data);
        }
    }
}
