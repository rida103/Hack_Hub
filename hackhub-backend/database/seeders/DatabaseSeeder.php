<?php

namespace Database\Seeders;

use App\Models\InvestorProfile;
use App\Models\OrganizerProfile;
use App\Models\ParticipantProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    // Creates one demo account per role so you can log in immediately.
    // Run with: php artisan db:seed
    public function run(): void
    {
        $organizer = User::create([
            'name' => 'Demo Organizer',
            'email' => 'organizer@demo.com',
            'password' => Hash::make('password'),
            'role' => 'organizer',
        ]);
        OrganizerProfile::create(['user_id' => $organizer->id, 'organization_name' => 'Demo Org']);

        $participant = User::create([
            'name' => 'Demo Participant',
            'email' => 'participant@demo.com',
            'password' => Hash::make('password'),
            'role' => 'participant',
        ]);
        ParticipantProfile::create([
            'user_id' => $participant->id,
            'university' => 'Demo University',
            'skills' => 'React,Node.js',
            'experience_level' => 'Intermediate',
        ]);

        $investor = User::create([
            'name' => 'Demo Investor',
            'email' => 'investor@demo.com',
            'password' => Hash::make('password'),
            'role' => 'investor',
        ]);
        InvestorProfile::create(['user_id' => $investor->id, 'company_name' => 'Demo Capital']);
    }
}
