<?php

namespace App\Modules\Management\Notification\Actions;

class StoreData
{
    static $model = \App\Modules\Management\Notification\Models\Model::class;

    public static function execute($notification, $notificationUsers)
    {
        try {
            $data = self::$model::query()->create($notification);
            if ($data) {
                // Create notification users
                foreach ($notificationUsers as $user) {
                    $data->notification_users()->create([
                        'user_id' => $user,
                        'notification_id' => $data->id,
                        'is_seen' => 0,
                        'creator' => auth()->user()->id,
                        'slug' => $data->slug,
                    ]);
                }
            }
        } catch (\Exception $e) {
            return messageResponse($e->getMessage(), [], 500, 'server_error');
        }
    }
}
