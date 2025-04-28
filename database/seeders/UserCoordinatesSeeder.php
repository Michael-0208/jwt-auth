<?php

namespace Database\Seeders;

use App\Models\UserCoordinate;
use Illuminate\Database\Seeder;

class UserCoordinatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Sample coordinates for user ID 2
        $coordinates = [
            // Starting point
            [
                'user_id' => 2,
                'latitude' => 40.7128,
                'longitude' => -74.0060,
                'created_at' => now()->subHours(5),
            ],
            // First stop
            [
                'user_id' => 2,
                'latitude' => 40.7306,
                'longitude' => -73.9352,
                'created_at' => now()->subHours(4),
            ],
            // Second stop
            [
                'user_id' => 2,
                'latitude' => 40.7589,
                'longitude' => -73.9851,
                'created_at' => now()->subHours(3),
            ],
            // Third stop
            [
                'user_id' => 2,
                'latitude' => 40.7484,
                'longitude' => -73.9857,
                'created_at' => now()->subHours(2),
            ],
            // Fourth stop
            [
                'user_id' => 2,
                'latitude' => 40.6892,
                'longitude' => -74.0445,
                'created_at' => now()->subHours(1),
            ],
            // Current location
            [
                'user_id' => 2,
                'latitude' => 40.7128,
                'longitude' => -74.0060,
                'created_at' => now(),
            ],
        ];

        // Insert the coordinates
        foreach ($coordinates as $coordinate) {
            UserCoordinate::create($coordinate);
        }
    }
} 