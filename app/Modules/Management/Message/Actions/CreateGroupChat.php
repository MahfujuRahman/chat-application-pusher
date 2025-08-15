<?php

namespace App\Modules\Management\Message\Actions;

use App\Modules\Management\Message\Validations\CreateGroupChatValidation;

class CreateGroupChat
{
    static $conversationModel = \App\Modules\Management\Message\Models\ConversationModel::class;

    public static function execute($request)
    {
        try {
            // Validate the request
            $validatedData = $request->validate((new CreateGroupChatValidation())->rules());
            
            $groupName = $validatedData['name'];
            $participantIds = $validatedData['participant_ids'];
            $creatorId = auth()->id();

            // Remove creator from participants if included
            $participantIds = array_filter($participantIds, function($id) use ($creatorId) {
                return $id != $creatorId;
            });

            if (empty($participantIds)) {
                return messageResponse('At least one participant other than yourself is required', [], 400, 'error');
            }

            // Create group conversation
            $conversation = self::$conversationModel::create([
                'creator' => $creatorId,
                'participant' => null, // null indicates group chat
                'last_updated' => now(),
                'is_group' => true,
                'group_name' => $groupName,
                'group_participants' => array_merge([$creatorId], array_values($participantIds))
            ]);

            // Load the conversation with relationships for response
            $conversation->load(['creatorUser', 'messages']);

            return messageResponse('Group chat created successfully', $conversation, 200, 'success');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return messageResponse('Validation failed', $e->errors(), 422, 'error');
        } catch (\Exception $e) {
            return messageResponse($e->getMessage(), [], 500, 'error');
        }
    }
}
