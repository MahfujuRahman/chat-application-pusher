<?php

namespace App\Modules\Management\Message\Actions;

use App\Events\MessageSent;

class SendMessage
{
    static $messageModel = \App\Modules\Management\Message\Models\Model::class;
    static $conversationModel = \App\Modules\Management\Message\Models\ConversationModel::class;

    public static function execute($request)
    {
        try {

            // dd($request->all());

            $conversationId = $request['conversation_id'] ?? $request->conversation_id ?? null;
            $text = $request['text'] ?? $request->text ?? null;

            if (!$conversationId || !$text) {
                return messageResponse('Missing conversation_id or text', [], 422, 'validation_error');
            }

            $conversation = self::$conversationModel::findOrFail($conversationId);

            $authId = auth()->id();
            
            // Check if user is part of the conversation (regular or group)
            if ($conversation->is_group) {
                if (!$conversation->hasParticipant($authId)) {
                    return messageResponse('You are not part of this group conversation', [], 403);
                }
                $receiverId = null; // For group chats, no specific receiver
            } else {
                if (!in_array($authId, [$conversation->creator, $conversation->participant])) {
                    return messageResponse('You are not part of this conversation', [], 403);
                }
                $receiverId = $conversation->creator === $authId
                    ? $conversation->participant
                    : $conversation->creator;
            }

            $message = self::$messageModel::create([
                'conversation_id' => $conversationId,
                'sender' => $authId,
                'receiver' => $receiverId,
                'text' => $text,
                'date_time' => now(),
            ]);

            // Load the sender relationship for the response
            $message->load('sender');

            // Broadcast the message
            if ($conversation->is_group) {
                // For group chats, broadcast to all participants except sender
                $participants = $conversation->group_participants ?? [];
                foreach ($participants as $participantId) {
                    if ($participantId != $authId) {
                        // Create individual events for each participant
                        $tempMessage = clone $message;
                        $tempMessage->receiver = $participantId;
                        event(new MessageSent($tempMessage, auth()->user()));
                    }
                }
            } else {
                // For regular conversations, broadcast to the receiver
                event(new MessageSent($message, auth()->user()));
            }

            $conversation->update(['last_updated' => now()]);

            return entityResponse($message);
        } catch (\Exception $e) {
            return messageResponse($e->getMessage(), [], 500);
        }
    }
}
