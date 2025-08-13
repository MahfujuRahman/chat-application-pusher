<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Support\Facades\Log;
use App\Modules\Management\UserManagement\User\Models\Model as User;
use App\Modules\Management\Message\Models\Model as Message;

class MessageSent implements ShouldBroadcast
{
    use InteractsWithSockets;

    public $message;
    public $sender;

    public function __construct(Message $message, User $sender)
    {
        $this->message = $message;
        $this->sender = $sender;
    }

    public function broadcastOn()
    {
        $channelName = 'chat.' . $this->message->receiver;
        Log::info('Broadcasting on channel: ' . $channelName, [
            'receiver_id' => $this->message->receiver,
            'sender_id' => $this->sender->id ?? null
        ]);
        return new PrivateChannel($channelName);
    }

    public function broadcastWith()
    {
        $data = [
            'id' => $this->message->id,
            'conversation_id' => $this->message->conversation_id,
            'text' => $this->message->text,
            'date_time' => $this->message->date_time,
            'sender' => [
                'id' => $this->sender->id,
                'name' => $this->sender->name ?? $this->sender->first_name . ' ' . $this->sender->last_name,
                'avatar' => $this->sender->avatar ?? null
            ]
        ];
        
        Log::info('Broadcasting message data', $data);
        return $data;
    }

    public function broadcastAs()
    {
        return 'MessageSent';
    }
}
