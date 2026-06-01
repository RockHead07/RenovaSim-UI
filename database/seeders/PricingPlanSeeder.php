<?php

namespace Database\Seeders;

use App\Models\PlanFeature;
use App\Models\PricingPlan;
use Illuminate\Database\Seeder;

class PricingPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'slug'        => 'free',
                'name'        => 'Free Plan',
                'description' => 'Cocok untuk memulai perencanaan renovasi.',
                'price'       => 0,
                'original_price' => 0,
                'is_popular'  => false,
                'is_active'   => true,
                'features'    => [
                    ['feature_key' => 'max_projects',                'feature_label' => 'Maksimal project',           'feature_value' => '2',         'is_available' => true],
                    ['feature_key' => 'max_estimations_per_project', 'feature_label' => 'Estimasi per project',       'feature_value' => '3',         'is_available' => true],
                    ['feature_key' => 'ai_estimation',               'feature_label' => 'AI Estimation (RAI)',        'feature_value' => 'true',      'is_available' => true],
                    ['feature_key' => 'rab_export',                  'feature_label' => 'Export RAB ke XLSX',        'feature_value' => 'false',     'is_available' => false],
                    ['feature_key' => 'share_rab',                   'feature_label' => 'Bagikan RAB ke kontraktor', 'feature_value' => 'false',     'is_available' => false],
                    ['feature_key' => 'multi_user',                  'feature_label' => 'Multi-user collaboration',  'feature_value' => 'false',     'is_available' => false],
                ],
            ],
            [
                'slug'        => 'pro',
                'name'        => 'Pro Plan',
                'description' => 'Untuk renovasi serius dengan fitur lengkap.',
                'price'       => 99000,
                'original_price' => 149000,
                'is_popular'  => true,
                'is_active'   => true,
                'features'    => [
                    ['feature_key' => 'max_projects',                'feature_label' => 'Maksimal project',           'feature_value' => 'unlimited', 'is_available' => true],
                    ['feature_key' => 'max_estimations_per_project', 'feature_label' => 'Estimasi per project',       'feature_value' => 'unlimited', 'is_available' => true],
                    ['feature_key' => 'ai_estimation',               'feature_label' => 'AI Estimation (RAI)',        'feature_value' => 'true',      'is_available' => true],
                    ['feature_key' => 'rab_export',                  'feature_label' => 'Export RAB ke XLSX',        'feature_value' => 'true',      'is_available' => true],
                    ['feature_key' => 'share_rab',                   'feature_label' => 'Bagikan RAB ke kontraktor', 'feature_value' => 'true',      'is_available' => true],
                    ['feature_key' => 'multi_user',                  'feature_label' => 'Multi-user collaboration',  'feature_value' => 'false',     'is_available' => false],
                ],
            ],
            [
                'slug'        => 'enterprise',
                'name'        => 'Enterprise',
                'description' => 'Untuk tim dan kontraktor profesional.',
                'price'       => 299000,
                'original_price' => 299000,
                'is_popular'  => false,
                'is_active'   => true,
                'features'    => [
                    ['feature_key' => 'max_projects',                'feature_label' => 'Maksimal project',           'feature_value' => 'unlimited', 'is_available' => true],
                    ['feature_key' => 'max_estimations_per_project', 'feature_label' => 'Estimasi per project',       'feature_value' => 'unlimited', 'is_available' => true],
                    ['feature_key' => 'ai_estimation',               'feature_label' => 'AI Estimation (RAI)',        'feature_value' => 'true',      'is_available' => true],
                    ['feature_key' => 'rab_export',                  'feature_label' => 'Export RAB ke XLSX',        'feature_value' => 'true',      'is_available' => true],
                    ['feature_key' => 'share_rab',                   'feature_label' => 'Bagikan RAB ke kontraktor', 'feature_value' => 'true',      'is_available' => true],
                    ['feature_key' => 'multi_user',                  'feature_label' => 'Multi-user collaboration',  'feature_value' => 'true',      'is_available' => true],
                ],
            ],
        ];

        foreach ($plans as $planData) {
            $features = $planData['features'];
            unset($planData['features']);

            // Skip if already exists (idempotent)
            $plan = PricingPlan::firstOrCreate(
                ['slug' => $planData['slug']],
                $planData
            );

            // Clear and re-seed features
            $plan->features()->delete();
            foreach ($features as $feature) {
                $plan->features()->create(array_merge($feature, [
                    'feature' => $feature['feature_label'], // legacy compat
                ]));
            }
        }
    }
}
