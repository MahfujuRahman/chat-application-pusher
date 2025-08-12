<?php

namespace App\Modules\Management\Notification\Actions;

class MarkAsSeen
{
    static $model = \App\Modules\Management\Notification\Models\NotificationUserModel::class;

    public static function execute($request = null)
    {
        try {

            $requestData = json_decode(json_encode($request->data), false);



            if (!$data = self::$model::where('id',  $requestData->notification_user?->id)->first()) {
                return messageResponse('Data not found...', $data, 404, 'error');
            }
            $data->is_seen = 1;
            $data->update();
        } catch (\Exception $e) {
            return messageResponse($e->getMessage(), [], 500, 'server_error');
        }
    }
}
