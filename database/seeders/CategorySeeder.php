<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Category::factory()->createMany([
            [
                'type' => $type = 'phone',
                'description' => 'IP Phones',
                'vlan' => $type,
            ],
            [
                'type' => $type = 'printer',
                'description' => 'Network Printers',
                'vlan' => $type,
            ],
            [
                'type' => $type = 'vc',
                'description' => 'Videoconferencing Devices',
                'vlan' => $type,
            ],
            [
                'type' => $type = 'fallback',
                'description' => 'Fallback VLAN',
                'vlan' => $type,
            ],
            [
                'type' => $type = 'blind',
                'description' => 'Blind Devices',
                'vlan' => $type,
            ],
        ]);
    }
}
