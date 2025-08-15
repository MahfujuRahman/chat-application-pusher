<?php

namespace App\Modules\Management\Message\Actions;

class GetGroupMembers
{
    static $conversationModel = \App\Modules\Management\Message\Models\ConversationModel::class;

    public static function execute($conversationId)
    {
        try {
            $userId = auth()->id();
            
            // Get the group conversation
            $conversation = self::$conversationModel::where('id', $conversationId)
                ->where('is_group', true)
                ->first();

            if (!$conversation) {
                return messageResponse('Group not found', [], 404, 'error');
            }

            // Check if user is part of the group
            if (!$conversation->hasParticipant($userId)) {
                return messageResponse('You are not part of this group', [], 403, 'error');
            }

            // Get all members with their details
            $memberIds = $conversation->group_participants ?? [];
            $members = \App\Models\User::whereIn('id', $memberIds)
                ->select('id', 'name', 'email', 'image')
                ->get()
                ->map(function($user) use ($conversation) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'image' => $user->image,
                        'is_creator' => $user->id == $conversation->creator
                    ];
                });

            return messageResponse('Group members retrieved successfully', [
                'group_name' => $conversation->group_name,
                'members' => $members,
                'total_members' => $members->count()
            ], 200, 'success');

        } catch (\Exception $e) {
            return messageResponse($e->getMessage(), [], 500, 'error');
        }
    }
}
