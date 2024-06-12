<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('categories')->insert([
            'id' => 'GADGET',
            'name' => 'Smartphone',
            'desc' => 'Smartphone desc',
            'created_at' => '2024-06-07 14:00:00' 
        ]);

        DB::table('categories')->insert([
            'id' => 'ATK',
            'name' => 'Pencil',
            'desc' => 'Pencil desc',
            'created_at' => '2024-06-08 14:00:00' 
        ]);

        DB::table('categories')->insert([
            'id' => 'FOOD',
            'name' => 'Ramen',
            'created_at' => '2024-06-09 14:00:00' 
        ]);
        
        DB::table('categories')->insert([
            'id' => 'FASHION',
            'name' => 'Hat',
            'created_at' => '2024-06-10 14:00:00' 
        ]);
    }
}
