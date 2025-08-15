<?php

namespace App\Modules\Management\Message\Actions;

use Illuminate\Support\Facades\Auth;

class DeleteGroup
{
    static $conversationModel = \App\Modules\Management\Message\Models\ConversationModel::class;
    static $messageModel = \App\Modules\Management\Message\Models\Model::class;

    public static function execute($id)
    {
        try {
            $conversation = self::$conversationModel::find($id);
            
            if (!$conversation) {
                return messageResponse('Group not found', [], 404, 'error');
            }

            // Check if it's a group conversation
            if (!$conversation->is_group) {
                return messageResponse('This is not a group conversation', [], 400, 'error');
            }

            // Check if user is the creator of the group or SuperAdmin
            $currentUser = Auth::user();
            $isSuperAdmin = $currentUser && $currentUser->role_id == 1;
            $isCreator = $conversation->creator === Auth::id();
            
            if (!$isCreator && !$isSuperAdmin) {
                return messageResponse('Only the group creator or SuperAdmin can delete the group', [], 403, 'error');
            }

            // Delete all messages in the conversation
            self::$messageModel::where('conversation_id', $id)->delete();

            // Delete the conversation
            $conversation->delete();

            return messageResponse('Group deleted successfully', [], 200, 'success');

        } catch (\Exception $e) {
            return messageResponse($e->getMessage(), [], 500, 'error');
        }
    }
}
