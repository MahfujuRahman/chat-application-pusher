<?php

namespace App\Modules\Management\Message\Validation;

use Illuminate\Support\Facades\Validator;

class GetAvailableUsersValidation
{
    public static function validate($conversationId)
    {
        $validator = Validator::make(['conversation_id' => $conversationId], [
            'conversation_id' => ['required', 'numeric'],
        ]);

        if ($validator->fails()) {
            return messageResponse('Validation Error', $validator->errors(), 422, 'error');
        }
        return messageResponse('Validation Success', [], 200, 'success');
    }
}
