<?php

namespace Database\Seeders;

use App\Enums\PlanAudience;
use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * PLACEHOLDERS: price 0 and inactive, so nobody can subscribe until you set
     * real prices and flip is_active. firstOrCreate never overwrites your edits.
     */
    public function run(): void
    {
        $plans = [
            [
                'code' => 'client-annual',
                'name' => 'Customer Annual Subscription (PLACEHOLDER)',
                'audience' => PlanAudience::Client,
            ],
            [
                'code' => 'vendor-annual',
                'name' => 'Vendor Annual Subscription (PLACEHOLDER)',
                'audience' => PlanAudience::Vendor,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::firstOrCreate(
                ['code' => $plan['code']],
                [
                    'name' => $plan['name'],
                    'audience' => $plan['audience']->value,
                    'price' => 0,
                    'currency' => 'PHP',
                    'duration_months' => 12,
                    'is_active' => false,
                ]
            );
        }
    }
}
