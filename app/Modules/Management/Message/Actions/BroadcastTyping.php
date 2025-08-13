<?php

namespace App\Modules\Management\Message\Actions;

use App\Events\UserTyping;

class BroadcastTyping
{
    static $conversationModel = \App\Modules\Management\Message\Models\ConversationModel::class;

    public static function execute($request)
    {
        try {
            $conversationId = $request['conversation_id'] ?? $request->conversation_id ?? null;
            $isTyping = $request['is_typing'] ?? $request->is_typing ?? false;

            if (!$conversationId) {
                return messageResponse('Missing conversation_id', [], 422, 'validation_error');
            }

            // Verify the conversation exists and user has access
            $conversation = self::$conversationModel::findOrFail($conversationId);
            $authId = auth()->id();
            
            // Check if user is part of the conversation
            if ($conversation->is_group) {
                if (!$conversation->hasParticipant($authId)) {
                    return messageResponse('You are not part of this group conversation', [], 403);
                }
            } else {
                if (!in_array($authId, [$conversation->creator, $conversation->participant])) {
                    return messageResponse('You are not part of this conversation', [], 403);
                }
            }

            // Broadcast typing event
            event(new UserTyping($conversationId, auth()->user(), $isTyping));

            return messageResponse('Typing status broadcasted successfully', [], 200);
            
        } catch (\Exception $e) {
            return messageResponse($e->getMessage(), [], 500);
        }
    }
}
