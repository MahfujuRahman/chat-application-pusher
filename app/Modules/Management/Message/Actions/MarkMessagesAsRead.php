<?php

namespace App\Modules\Management\Message\Actions;

use App\Modules\Management\Message\Models\MessageReadStatusModel;

class MarkMessagesAsRead
{
    public static function execute($conversationId)
    {
        try {
            $userId = auth()->id();
            
            // Get all unread messages in this conversation for this user
            $messages = \App\Modules\Management\Message\Models\Model::where('conversation_id', $conversationId)
                ->where('sender', '!=', $userId) // Don't mark own messages as read
                ->whereDoesntHave('readStatus', function($q) use ($userId) {
                    $q->where('user_id', $userId);
                })
                ->get();

            // Mark all as read
            foreach ($messages as $message) {
                MessageReadStatusModel::create([
                    'message_id' => $message->id,
                    'user_id' => $userId,
                    'read_at' => now(),
                ]);
            }

            return messageResponse('Messages marked as read', [], 200, 'success');

        } catch (\Exception $e) {
            return messageResponse($e->getMessage(), [], 500, 'error');
        }
    }
}
