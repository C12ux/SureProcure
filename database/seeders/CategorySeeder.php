<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /** Starter list: edit freely, re-running only adds missing names. */
    public function run(): void
    {
        $names = [
            'IT & Electronics',
            'Office Supplies & Furniture',
            'Construction & Civil Works',
            'Facilities & Maintenance',
            'Logistics & Transportation',
            'Professional Services',
            'Energy & Renewables',
            'Manufacturing Inputs & Raw Materials',
            'Safety & PPE',
            'Marketing & Printing',
        ];

        foreach ($names as $name) {
            Category::firstOrCreate(['name' => $name], ['is_active' => true]);
        }
    }
}
