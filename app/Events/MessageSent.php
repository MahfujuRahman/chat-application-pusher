<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
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
        return new PrivateChannel('chat.' . $this->message->receiver);
    }

    public function broadcastWith()
    {
        return [
            'message' => $this->message,
            'sender' => $this->sender,
        ];
    }
}
