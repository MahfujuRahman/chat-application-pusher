<?php

namespace App\Modules\Management\Message\Actions;

use App\Models\User;
use App\Modules\Management\Message\Models\ConversationModel;
use Exception;

class GetAvailableUsers
{
    static $model = ConversationModel::class;

    public static function execute($conversationId)
    {
        try {
            // Basic validation
            if (!$conversationId || !is_numeric($conversationId)) {
                return messageResponse('Invalid conversation ID', [], 422, 'error');
            }

            // Get the conversation
            $conversation = self::$model::find($conversationId);
            if (!$conversation) {
                return messageResponse('Conversation not found', [], 404, 'error');
            }

            // Get current group members
            $currentMemberIds = [];
            if ($conversation->participants) {
                $participants = json_decode($conversation->participants, true);
                $currentMemberIds = array_column($participants, 'id');
            }

            // Get all users except current group members
            $availableUsers = User::whereNotIn('id', $currentMemberIds)
                ->select('id', 'name', 'email', 'image')
                ->get();

            return messageResponse('Available users retrieved successfully', $availableUsers, 200, 'success');
        } catch (Exception $e) {
            return messageResponse($e->getMessage(), [], 500, 'server_error');
        }
    }
}
