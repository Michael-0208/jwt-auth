<?php

namespace App\Console\Commands;

use App\Jobs\StoreUserLocation;
use App\Models\User;
use App\Models\UserCoordinate;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateUserLocations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:update-locations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get and store current locations for all active users';

    /**
     * Execute the console command.
     */
    protected function handle()
    {
        $this->info('Starting user location updates...');
        
        while (true) {
            try {
                $users = User::all();
                $this->info('Processing ' . $users->count() . ' users...');
                
                foreach ($users as $user) {
                    try {
                        $newCoordinates = $this->getNewCoordinates($user);
                        
                        if ($newCoordinates) {
                            // Store in user_coordinates table
                            UserCoordinate::create([
                                'user_id' => $user->id,
                                'latitude' => $newCoordinates['latitude'],
                                'longitude' => $newCoordinates['longitude']
                            ]);
                            
                            // Update users table
                            $user->update([
                                'latitude' => $newCoordinates['latitude'],
                                'longitude' => $newCoordinates['longitude']
                            ]);
                            
                            $this->info("Updated location for user: {$user->email}");
                        }
                    } catch (\Exception $e) {
                        $this->error("Error processing user {$user->email}: " . $e->getMessage());
                        \Log::error("Error processing user {$user->email}: " . $e->getMessage());
                    }
                }
                
                // Sleep for 5 minutes before next update
                $this->info('Sleeping for 5 minutes...');
                sleep(300);
                
            } catch (\Exception $e) {
                $this->error('Error in location update process: ' . $e->getMessage());
                \Log::error('Error in location update process: ' . $e->getMessage());
                // Sleep for 1 minute before retrying
                sleep(60);
            }
        }
    }

    /**
     * Simulate getting new coordinates from user's device
     * In a real application, this would be replaced with actual device communication
     */
    private function getNewCoordinates(User $user)
    {
        // This is a placeholder for the actual implementation
        // In a real application, you would:
        // 1. Send push notification to user's device
        // 2. Device responds with current location
        // 3. Return the new coordinates

        // For simulation purposes, we'll generate random coordinates near the last known location
        $lastCoordinate = $user->coordinates()->latest()->first();
        
        if (!$lastCoordinate) {
            return null;
        }

        // Add small random variation to simulate movement
        $latitude = $lastCoordinate->latitude + (rand(-100, 100) / 1000000);
        $longitude = $lastCoordinate->longitude + (rand(-100, 100) / 1000000);

        return [
            'latitude' => $latitude,
            'longitude' => $longitude
        ];
    }
} 