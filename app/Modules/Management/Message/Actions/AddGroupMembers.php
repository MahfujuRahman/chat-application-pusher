<?php

namespace App\Modules\Management\Message\Actions;

class AddGroupMembers
{
    static $conversationModel = \App\Modules\Management\Message\Models\ConversationModel::class;

    public static function execute($request)
    {
        try {
            $userId = auth()->id();
            $groupId = $request->conversation_id;
            $newMemberIds = $request->user_ids;

            // Validate input
            if (!$groupId || empty($newMemberIds) || !is_array($newMemberIds)) {
                return messageResponse('Group ID and member IDs are required', [], 400, 'error');
            }

            // Get the group conversation
            $conversation = self::$conversationModel::where('id', $groupId)
                ->where('is_group', true)
                ->first();

            if (!$conversation) {
                return messageResponse('Group not found', [], 404, 'error');
            }

            // Check if user is part of the group (only group members can add others)
            if (!$conversation->hasParticipant($userId)) {
                return messageResponse('You are not part of this group', [], 403, 'error');
            }

            // Get current participants
            $currentParticipants = $conversation->group_participants ?? [];

            // Filter out users who are already in the group
            $newMemberIds = array_filter($newMemberIds, function($memberId) use ($currentParticipants) {
                return !in_array($memberId, $currentParticipants);
            });

            if (empty($newMemberIds)) {
                return messageResponse('All selected users are already in the group', [], 400, 'error');
            }

            // Verify that all new member IDs exist
            $validUsers = \App\Models\User::whereIn('id', $newMemberIds)->pluck('id')->toArray();
            $invalidIds = array_diff($newMemberIds, $validUsers);

            if (!empty($invalidIds)) {
                return messageResponse('Some user IDs are invalid', [], 400, 'error');
            }

            // Add new members to the group
            $updatedParticipants = array_merge($currentParticipants, $newMemberIds);
            
            $conversation->update([
                'group_participants' => $updatedParticipants,
                'last_updated' => now()
            ]);

            // Get the updated member list with details
            $newMembers = \App\Models\User::whereIn('id', $newMemberIds)
                ->select('id', 'name', 'email')
                ->get();

            return messageResponse('Members added successfully', [
                'added_members' => $newMembers,
                'total_members' => count($updatedParticipants)
            ], 200, 'success');

        } catch (\Exception $e) {
            return messageResponse($e->getMessage(), [], 500, 'error');
        }
    }
}
