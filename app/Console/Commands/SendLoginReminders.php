<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\LoginReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendLoginReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:send-login-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send login reminder emails to users who haven\'t logged in for 3 days';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $threeDaysAgo = now()->subDays(3);
        
        $inactiveUsers = User::where('last_login_at', '<', $threeDaysAgo)
            ->orWhereNull(column: 'last_login_at')
            ->get();

        $count = 0;
        foreach ($inactiveUsers as $user) {
            try {
                $user->notify(new LoginReminderNotification());
                $count++;
                Log::info('Login reminder sent to user: ' . $user->email);
            } catch (\Exception $e) {
                Log::error('Failed to send login reminder to user ' . $user->email . ': ' . $e->getMessage());
            }
        }

        $this->info("Sent {$count} login reminder emails successfully.");
        return 0;
    }
} 