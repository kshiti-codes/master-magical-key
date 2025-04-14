<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SubscriptionPlan;
use Carbon\Carbon;

class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create lifetime plan (available for the first three months)
        SubscriptionPlan::create([
            'name' => 'Early Lifetime Access',
            'description' => 'One-time payment for lifetime access to all content including chapters, spells and training videos. This special offer is only available for a limited time.',
            'price' => 222.00,
            'currency' => 'AUD',
            'billing_interval' => 'one-time',
            'is_active' => true,
            'is_lifetime' => true,
            'available_until' => Carbon::now()->addMonths(3), // Available for three months from now
        ]);
        
        // Create monthly subscription plan (will be available after the initial three months)
        SubscriptionPlan::create([
            'name' => 'Monthly Subscription',
            'description' => 'Monthly access to all chapters and spells. Training videos can be purchased separately.',
            'price' => 49.95,
            'currency' => 'AUD',
            'billing_interval' => 'month',
            'is_active' => true,
            'is_lifetime' => false,
            'available_until' => null, // Always available
        ]);
    }
}