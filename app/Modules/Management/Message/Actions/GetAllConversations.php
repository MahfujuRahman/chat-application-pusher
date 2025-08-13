<?php

namespace App\Modules\Management\Message\Actions;

class GetAllConversations
{
    static $model = \App\Modules\Management\Message\Models\ConversationModel::class;

    public static function execute()
    {
        try {
            $userId = auth()->id();
            
            $data = self::$model::with(['creatorUser', 'participantUser'])
                ->where(function ($q) use ($userId) {
                    $q->where('creator', $userId)
                        ->orWhere('participant', $userId)
                        ->orWhereJsonContains('group_participants', $userId);
                })
                ->get()
                ->map(function ($conversation) use ($userId) {
                    // Count unread messages for this user in this conversation
                    $unreadCount = \App\Modules\Management\Message\Models\Model::where('conversation_id', $conversation->id)
                        ->where('sender', '!=', $userId) // Don't count own messages
                        ->whereDoesntHave('readStatus', function($q) use ($userId) {
                            $q->where('user_id', $userId);
                        })
                        ->count();
                    
                    if ($conversation->is_group) {
                        // For group chats, set the participant as group info
                        $conversation->participant = (object) [
                            'name' => $conversation->group_name,
                            'image' => null, // You can add group avatar logic here
                            'is_group' => true,
                            'participants_count' => count($conversation->group_participants ?? [])
                        ];
                    } else {
                        // For regular conversations, determine who the "other user" is
                        $conversation->participant = $userId == $conversation->creator
                            ? $conversation->participantUser
                            : $conversation->creatorUser;
                    }

                    // Add unread count to conversation
                    $conversation->unread_count = $unreadCount;

                    // Get the last message for this conversation
                    $lastMessage = \App\Modules\Management\Message\Models\Model::where('conversation_id', $conversation->id)
                        ->orderBy('created_at', 'desc')
                        ->first();
                    
                    if ($lastMessage) {
                        $conversation->last_message = $lastMessage->text;
                        $conversation->last_updated = $lastMessage->created_at;
                    } else {
                        $conversation->last_message = null;
                        $conversation->last_updated = $conversation->updated_at;
                    }

                    unset($conversation->creatorUser);
                    unset($conversation->participantUser);

                    return $conversation;
                })
                ->sortByDesc(function ($conversation) {
                    return $conversation->last_updated ?? $conversation->updated_at;
                })
                ->values(); // Reset array keys after sorting

            // ✅ Return the final data as response
            return entityResponse($data);
        } catch (\Exception $e) {
            return messageResponse($e->getMessage(), [], 500, 'server_error');
        }
    }
}
