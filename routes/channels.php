<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
//     return (int) $user->id === (int) $id;
// });



Broadcast::channel('chat.{userId}', function ($user, $userId) {
    Log::info('Broadcast auth check', [
        'user_id' => $user->id,
        'requested_userId' => $userId,
        'match' => (int) $user->id === (int) $userId
    ]);
    return (int) $user->id === (int) $userId;
});

// Conversation channels for typing indicators
Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    Log::info('Conversation channel auth check', [
        'user_id' => $user->id,
        'conversation_id' => $conversationId
    ]);
    
    // Check if user is part of this conversation
    $conversation = \App\Modules\Management\Message\Models\ConversationModel::find($conversationId);
    
    if (!$conversation) {
        Log::info('Conversation not found', ['conversation_id' => $conversationId]);
        return false;
    }
    
    // Check if user is part of the conversation
    if ($conversation->is_group) {
        $isParticipant = $conversation->hasParticipant($user->id);
        Log::info('Group conversation auth', [
            'is_participant' => $isParticipant
        ]);
        return $isParticipant;
    } else {
        $isParticipant = in_array($user->id, [$conversation->creator, $conversation->participant]);
        Log::info('Individual conversation auth', [
            'is_participant' => $isParticipant,
            'creator' => $conversation->creator,
            'participant' => $conversation->participant
        ]);
        return $isParticipant;
    }
});