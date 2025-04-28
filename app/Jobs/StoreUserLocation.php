<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\UserCoordinate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class StoreUserLocation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $latitude;
    protected $longitude;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user, $latitude, $longitude)
    {
        $this->user = $user;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            UserCoordinate::create([
                'user_id' => $this->user->id,
                'latitude' => $this->latitude,
                'longitude' => $this->longitude
            ]);

            Log::info('User location stored successfully', [
                'user_id' => $this->user->id,
                'latitude' => $this->latitude,
                'longitude' => $this->longitude
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to store user location', [
                'user_id' => $this->user->id,
                'error' => $e->getMessage()
            ]);
        }
    }
} 