<?php

namespace Database\Seeders;

use App\Models\Partner;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class PartnerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $partners = [
            ['name' => 'IKEA', 'logo' => 'I', 'color' => '0051BA', 'order' => 1, 'is_active' => true],
            ['name' => 'INFORMA', 'logo' => 'IN', 'color' => 'FF6B00', 'order' => 2, 'is_active' => true],
            ['name' => 'Mitra10', 'logo' => 'M', 'color' => 'E31E24', 'order' => 3, 'is_active' => true],
            ['name' => 'BJ Home', 'logo' => 'BJ', 'color' => '1F4788', 'order' => 4, 'is_active' => true],
            ['name' => 'Qhomemart', 'logo' => 'Q', 'color' => '00A651', 'order' => 5, 'is_active' => true],
            ['name' => 'Kanggo', 'logo' => 'K', 'color' => 'FF9900', 'order' => 6, 'is_active' => true],
            ['name' => 'Tukang.com', 'logo' => 'T', 'color' => '004D8C', 'order' => 7, 'is_active' => true],
        ];

        foreach ($partners as $partnerData) {
            // Check if partner already exists to avoid duplicates
            if (!Partner::where('name', $partnerData['name'])->exists()) {
                // Extract color before creating placeholder (don't store in DB)
                $color = $partnerData['color'];
                unset($partnerData['color']);
                
                // Create placeholder image URL using placehold.co service
                $initials = $partnerData['logo'];
                $placeholderUrl = "https://placehold.co/200x200/{$color}/FFFFFF?text={$initials}";
                
                // Download and store the placeholder image
                $logoImagePath = $this->storeRemoteImage($placeholderUrl, $partnerData['name']);
                $partnerData['logo_image'] = $logoImagePath;
                
                Partner::create($partnerData);
            }
        }
    }

    /**
     * Download and store remote image locally
     */
    private function storeRemoteImage(string $url, string $partnerName): string
    {
        try {
            $imageContent = @file_get_contents($url);
            
            if ($imageContent === false) {
                // Fallback: create a simple SVG placeholder
                $imageContent = $this->createSvgPlaceholder($partnerName);
            }
            
            $filename = strtolower(str_replace(' ', '_', $partnerName)) . '.png';
            $path = 'partners/' . $filename;
            
            // Ensure directory exists
            Storage::disk('public')->makeDirectory('partners', 0755, true);
            Storage::disk('public')->put($path, $imageContent);
            
            return $path;
        } catch (\Exception $e) {
            // Fallback: return a basic SVG
            return $this->createLocalSvgPlaceholder($partnerName);
        }
    }

    /**
     * Create a simple SVG placeholder
     */
    private function createSvgPlaceholder(string $partnerName): string
    {
        $initials = implode('', array_map(fn($word) => $word[0], explode(' ', $partnerName)));
        $initials = substr(strtoupper($initials), 0, 2);
        
        $color = '#' . substr(md5($partnerName), 0, 6);
        
        $svg = <<<SVG
<svg width="200" height="200" xmlns="http://www.w3.org/2000/svg">
  <rect width="200" height="200" fill="{$color}"/>
  <text x="50%" y="50%" font-size="60" font-weight="bold" fill="white" text-anchor="middle" dominant-baseline="central">{$initials}</text>
</svg>
SVG;
        
        return $svg;
    }

    /**
     * Create and store a local SVG placeholder
     */
    private function createLocalSvgPlaceholder(string $partnerName): string
    {
        $svg = $this->createSvgPlaceholder($partnerName);
        $filename = strtolower(str_replace(' ', '_', $partnerName)) . '.svg';
        $path = 'partners/' . $filename;
        
        Storage::disk('public')->makeDirectory('partners', 0755, true);
        Storage::disk('public')->put($path, $svg);
        
        return $path;
    }
}
