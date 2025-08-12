<?php

namespace App\Modules\Management\Message\Actions;

class GetConversationMessages
{
    static $model = \App\Modules\Management\Message\Models\Model::class;

    public static function execute($id)
    {
        try {
            $userId = auth()->id();
            $data = self::$model::with('conversation', 'sender', 'receiver')
                ->where('conversation_id', $id)
                ->get()
                ->map(function ($message) use ($userId) {
                    $messageArray = $message->toArray();
                    $messageArray['type'] = $message->sender == $userId ? 'mine' : 'their';
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
