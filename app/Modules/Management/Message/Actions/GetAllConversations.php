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
                        ->whereDoesntHave('readStatus', function ($q) use ($userId) {
                            $q->where('user_id', $userId);
                        })
                        ->count();

                    // Determine participant info
                    if ($conversation->is_group) {
                        $participant = (object)[
                            'name' => $conversation->group_name,
                            'image' => null,
                            'is_group' => true,
                            'participants_count' => count($conversation->group_participants ?? []),
                        ];
                    } else {
                        $participant = $userId == $conversation->creator
                            ? $conversation->participantUser
                            : $conversation->creatorUser;
                    }

                    // Get the last message for this conversation
                    $lastMessage = \App\Modules\Management\Message\Models\Model::where('conversation_id', $conversation->id)
                        ->orderBy('created_at', 'desc')
                        ->first();

                    $lastMessageText = null;
                    $lastUpdated = $conversation->updated_at;
                    if ($lastMessage) {
                        $lastMessageText = $lastMessage->text ?? null;
                        $lastUpdated = $lastMessage->created_at ?? $lastUpdated;
                    }

                    // Build a plain object to return (avoid mutating the Eloquent model)
                    $conversationData = (object) [
                        'id' => $conversation->id,
                        'creator' => $conversation->creator,
                        'is_group' => $conversation->is_group,
                        'group_name' => $conversation->group_name ?? null,
                        'participant' => $participant,
                        'unread_count' => $unreadCount,
                        'last_message' => $lastMessageText,
                        'last_updated' => $lastUpdated,
                        'updated_at' => $conversation->updated_at,
                    ];

                    return $conversationData;
                });

            // ✅ Return the final data as response
            return entityResponse($data);
        } catch (\Exception $e) {
            return messageResponse($e->getMessage(), [], 500, 'server_error');
        }
    }
}
