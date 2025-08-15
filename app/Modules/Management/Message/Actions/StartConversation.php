<?php

namespace App\Modules\Management\Message\Actions;

class StartConversation
{
    static $model = \App\Modules\Management\Message\Models\ConversationModel::class;

    public static function execute($request)
    {
        try {

            $participantId = $request->participant_id;
            $existing = self::$model::where(function ($q) use ($participantId) {
                $q->where('creator', auth()->id())
                    ->where('participant', $participantId);
            })->orWhere(function ($q) use ($participantId) {
                $q->where('participant', auth()->id())
                    ->where('creator', $participantId);
            })->first();

            if ($existing) {
                return messageResponse('Already added this conversation', [], 404, 'error');
            }

            $conversation = self::$model::create([
                'creator' => auth()->id(),
                'participant' => $participantId,
                'last_updated' => now(),
            ]);

            return entityResponse($conversation);
        } catch (\Exception $e) {
            return messageResponse($e->getMessage(), [], 500);
        }
    }
}
