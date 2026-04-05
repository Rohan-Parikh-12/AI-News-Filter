<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Technology',     'slug' => 'technology',     'api_keyword' => 'technology',     'icon' => 'ri-computer-line',       'sort_order' => 1],
            ['name' => 'Business',       'slug' => 'business',       'api_keyword' => 'business',       'icon' => 'ri-briefcase-line',      'sort_order' => 2],
            ['name' => 'Science',        'slug' => 'science',        'api_keyword' => 'science',        'icon' => 'ri-flask-line',          'sort_order' => 3],
            ['name' => 'Health',         'slug' => 'health',         'api_keyword' => 'health',         'icon' => 'ri-heart-pulse-line',    'sort_order' => 4],
            ['name' => 'Sports',         'slug' => 'sports',         'api_keyword' => 'sports',         'icon' => 'ri-football-line',       'sort_order' => 5],
            ['name' => 'Entertainment',  'slug' => 'entertainment',  'api_keyword' => 'entertainment',  'icon' => 'ri-movie-line',          'sort_order' => 6],
            ['name' => 'Politics',       'slug' => 'politics',       'api_keyword' => 'politics',       'icon' => 'ri-government-line',     'sort_order' => 7],
            ['name' => 'World',          'slug' => 'world',          'api_keyword' => 'world news',     'icon' => 'ri-earth-line',          'sort_order' => 8],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(
                ['slug' => $cat['slug']],
                array_merge($cat, ['status' => '1'])
            );
        }
    }
}
