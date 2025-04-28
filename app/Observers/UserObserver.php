<?php

namespace App\Observers;

use App\Models\User;
use App\Jobs\StoreUserLocation;
use Illuminate\Support\Facades\Log;

class UserObserver
{
    /**
     * Handle the User "created" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function created(User $user): void
    {
        // Log the user's activity in User model when created
        Log::info('User created: ' . $user->id);
    }

    /**
     * Handle the User "updated" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function updated(User $user): void
    {
        // Log the user's activity in User model when updated
        if ($user->isDirty('email')) {
            Log::info("User {$user->id} updated their email.");
        }
        Log::info('User updated: ' . $user->id);
    }

    /**
     * Handle the User "deleted" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function deleted(User $user): void
    {
        // Log the user's activity in User model when deleted
        Log::info('User deleted: ' . $user->id);
    }

    /**
     * Handle the User "restored" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function restored(User $user): void
    {
        // Log the user's activity in User model when restored
        Log::info('User restored: ' . $user->id);
    }

    /**
     * Handle the User "force deleted" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function forceDeleted(User $user): void
    {
        // Log the user's activity in User model when force deleted
        Log::channel('critical_errors')->info('User forcefully deleted: ' . $user->id);
    }

    /**
     * Handle the User "loggedIn" event.
     */
    public function loggedIn(User $user): void
    {
        // Get coordinates from session
        $latitude = session('user_latitude');
        $longitude = session('user_longitude');

        if ($latitude && $longitude) {
            // Dispatch job to store the location
            StoreUserLocation::dispatch($user, $latitude, $longitude);

            // Clear the session data
            session()->forget(['user_latitude', 'user_longitude']);

            Log::info('Location update job dispatched for user', [
                'user_id' => $user->id,
                'latitude' => $latitude,
                'longitude' => $longitude
            ]);
        }
    }
}
