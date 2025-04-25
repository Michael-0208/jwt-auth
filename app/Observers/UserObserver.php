<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class UserObserver
{
    /**
     * Handle the User "created" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function created(User $user)
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
    public function updated(User $user)
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
    public function deleted(User $user)
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
    public function restored(User $user)
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
    public function forceDeleted(User $user)
    {
        // Log the user's activity in User model when force deleted
        Log::channel('critical_errors')->info('User forcefully deleted: ' . $user->id);
    }
}
