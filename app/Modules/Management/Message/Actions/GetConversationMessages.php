<?php

namespace App\Modules\Management\Message\Actions;

class GetConversationMessages
{
    static $model = \App\Modules\Management\Message\Models\Model::class;

    public static function execute($id)
    {
        try {
            $userId = auth()->id();
            
            // Get pagination parameters from request
            $page = (int) request('page', 1);
            $perPage = (int) request('per_page', 20);
            $offset = ($page - 1) * $perPage;
            
            $data = self::$model::with('conversation', 'sender', 'receiver')
                ->where('conversation_id', $id)
                ->orderBy('created_at', 'desc') // Get newest first for pagination
                ->offset($offset)
                ->limit($perPage)
                ->get()
                ->reverse() // Reverse to show oldest first in the UI
                ->values() // Reset array keys after reverse
                ->map(function ($message) use ($userId) {
                    $messageArray = $message->toArray();
                    $messageArray['type'] = $message->sender == $userId ? 'mine' : 'theirs';
                    return $messageArray;
                });

            if (!$data) {
                return messageResponse('Conversation not found', [], 404, 'not_found');
            }

            return entityResponse($data);
        } catch (\Exception $e) {
            return messageResponse($e->getMessage(), [], 500, 'server_error');
        }
    }
}
