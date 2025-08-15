<?php

namespace App\Modules\Management\Message\Actions;

class RemoveGroupMember
{
    static $conversationModel = \App\Modules\Management\Message\Models\ConversationModel::class;

    public static function execute($request)
    {
        try {
            $userId = auth()->id();
            $groupId = $request->conversation_id;
            $memberToRemove = $request->user_id;

            if (!$groupId || !$memberToRemove) {
                return messageResponse('Group ID and member ID are required', [], 400, 'error');
            }

            // Get the group conversation
            $conversation = self::$conversationModel::where('id', $groupId)
                ->where('is_group', true)
                ->first();

            if (!$conversation) {
                return messageResponse('Group not found', [], 404, 'error');
            }

            // Get current participants
            $currentParticipants = $conversation->group_participants ?? [];

            // Check if user is part of the group using the actual participants array
            if (!in_array($userId, $currentParticipants)) {
                return messageResponse('You are not part of this group', [], 403, 'error');
            }

            // Check if user is the creator or SuperAdmin (only creators/SuperAdmin can remove members)
            $currentUser = \Illuminate\Support\Facades\Auth::user();
            $isSuperAdmin = $currentUser && $currentUser->role_id == 1;
            $isCreator = $conversation->creator == $userId;
            
            if (!$isCreator && !$isSuperAdmin) {
                return messageResponse('Only group creator or SuperAdmin can remove members', [], 403, 'error');
            }

            // Cannot remove the group creator
            if ($memberToRemove == $conversation->creator) {
                return messageResponse('Group creator cannot be removed', [], 400, 'error');
            }

            // Check if member to remove is in the group
            if (!in_array($memberToRemove, $currentParticipants)) {
                return messageResponse('User is not in this group', [], 400, 'error');
            }

            // Remove the member
            $updatedParticipants = array_filter($currentParticipants, function($id) use ($memberToRemove) {
                return $id != $memberToRemove;
            });

            $conversation->update([
                'group_participants' => array_values($updatedParticipants),
                'last_updated' => now()
            ]);

            return messageResponse('Member removed successfully', [
                'total_members' => count($updatedParticipants)
            ], 200, 'success');

        } catch (\Exception $e) {
            return messageResponse($e->getMessage(), [], 500, 'error');
        }
    }
}
