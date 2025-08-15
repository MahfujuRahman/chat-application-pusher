<?php

namespace App\Modules\Management\UserManagement\User\Actions;

class GetUsersByProjectId
{
    static $model = \App\Modules\Management\UserManagement\User\Models\Model::class;

    public static function execute($project_id)
    {
        try {
            // Assuming pivot table is 'project_user' with columns 'project_id' and 'user_id'
            $data = self::$model::whereHas('project', function($q) use ($project_id) {
                $q->where('project_id', $project_id);
            })->get();
            return entityResponse($data);
        } catch (\Exception $e) {
            return messageResponse($e->getMessage(), [], 500, 'server_error');
        }
    }
}
