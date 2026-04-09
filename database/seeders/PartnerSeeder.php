<?php

namespace Database\Seeders;

use App\Models\Partner;
use Illuminate\Database\Seeder;

class PartnerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $partners = [
            ['name' => 'IKEA', 'logo' => 'IKEA.png', 'order' => 1, 'is_active' => true],
            ['name' => 'INFORMA', 'logo' => 'INFORMA.png', 'order' => 2, 'is_active' => true],
            ['name' => 'Mitra10', 'logo' => 'Mitra10.png', 'order' => 3, 'is_active' => true],
            ['name' => 'BJ Home', 'logo' => 'BJHome.png', 'order' => 4, 'is_active' => true],
            ['name' => 'Qhomemart', 'logo' => 'Qhomemart.png', 'order' => 5, 'is_active' => true],
            ['name' => 'Kanggo', 'logo' => 'Kanggo.png', 'order' => 6, 'is_active' => true],
            ['name' => 'Tukang.com', 'logo' => 'tukangcom.png', 'order' => 7, 'is_active' => true],
        ];

        foreach ($partners as $partner) {
            // Check if partner already exists to avoid duplicates
            if (!Partner::where('name', $partner['name'])->exists()) {
                Partner::create($partner);
            }
        }
    }
}
