<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Events\MessageSent;
use App\Modules\Management\UserManagement\User\Models\Model as User;
use App\Modules\Management\Message\Models\Model as Message;
use Illuminate\Support\Facades\Log;

class TestBroadcast extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'broadcast:test {userId} {receiverId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test broadcasting a message event';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('userId');
        $receiverId = $this->argument('receiverId');

        $this->info("Testing broadcast from user $userId to user $receiverId");

        try {
            // Get users
            $sender = User::find($userId);
            $receiver = User::find($receiverId);

            if (!$sender) {
                $this->error("Sender user with ID $userId not found");
                return;
            }

            if (!$receiver) {
                $this->error("Receiver user with ID $receiverId not found");
                return;
            }

            // Create a test message
            $message = new Message([
                'sender' => $userId,
                'receiver' => $receiverId,
                'text' => 'Test message from command at ' . now(),
                'date_time' => now(),
                'conversation_id' => 1 // You might need to adjust this
            ]);

            $this->info("Broadcasting test message...");
            
            // Fire the event
            event(new MessageSent($message, $sender));

            $this->info("✅ Message broadcast event fired successfully!");
            $this->info("Check your logs for broadcast details.");
            $this->info("Channel: chat.$receiverId");

        } catch (\Exception $e) {
            $this->error("❌ Error broadcasting message: " . $e->getMessage());
            $this->error($e->getTraceAsString());
        }
    }
}
