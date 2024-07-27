<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => \Hash::make('1234'),
        ]);

        $planMonthly = \App\Models\Plan::create([
            'name' => 'Monthly',
            'description' => 'Monthly subscription',
            'price' => 2,
            'currency' => 'USD',
            'duration' => 1,
            'trial_period_days' => 0,
        ]);

        $planYearly = \App\Models\Plan::create([
            'name' => 'Yearly',
            'description' => 'Yearly subscription',
            'price' => 18,
            'currency' => 'USD',
            'duration' => 12,
            'trial_period_days' => 0,
        ]);

        $planTrial = \App\Models\Plan::create([
            'name' => 'Trial',
            'description' => 'Free trial subscription',
            'price' => 0,
            'currency' => 'USD',
            'duration' => 1,
            'trial_period_days' => 30,
        ]);
    }
}
