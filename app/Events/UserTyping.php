<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserTyping implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $conversationId;
    public $user;
    public $isTyping;

    public function __construct($conversationId, $user, $isTyping)
    {
        $this->conversationId = $conversationId;
        $this->user = $user;
        $this->isTyping = $isTyping;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('conversation.' . $this->conversationId);
    }

    public function broadcastWith()
    {
        return [
            'conversation_id' => $this->conversationId,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
                'avatar' => $this->user->avatar ?? null,
            ],
            'is_typing' => $this->isTyping,
        ];
    }

    public function broadcastAs()
    {
        return 'UserTyping';
    }
}
