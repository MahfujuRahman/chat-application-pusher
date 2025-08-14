<?php

namespace App\Modules\Management\Auth\Actions;



class CheckUser
{
    static $model = \App\Modules\Management\UserManagement\User\Models\Model::class;
    public static function execute()
    {
        try {
            if (auth()->check()) {
                $user = self::$model::where('id', auth()->user()->id)
                    ->select([
                        'id',
                        'slug',
                        'name',
                        'email',
                        'address',
                        'phone_number',
                        'image',
                        'social_media',
                        'designation',
                    ])
                    ->first();
                auth()->guard('web')->login($user, 1);
                return entityResponse($user);
            }
            return response()->json(["User not found"], 404);
        } catch (\Exception $e) {
            return messageResponse($e->getMessage(), [], 500, 'server_error');
        }
    }
}
