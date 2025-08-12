<?php

namespace App\Modules\Management\Notification\Actions;

class GetAllData
{
    static $model = \App\Modules\Management\Notification\Models\Model::class;

    public static function execute()
    {
        try {

            $pageLimit = request()->input('limit') ?? 10;
            $orderByColumn = request()->input('sort_by_col') ?? 'id';
            $orderByType = request()->input('sort_type') ?? 'desc';
            $status = request()->input('status') ?? 'active';
            $fields = request()->input('fields') ?? '*';
            $start_date = request()->input('start_date');
            $end_date = request()->input('end_date');
            $with = [];
            $condition = [];

            $data = self::$model::query();

            if (auth()->check()) {
                if (request()->has('not_seen') && request()->input('not_seen')) {
                    $data = $data->whereHas('notification_users', function ($q) {
                        $q->where('user_id', auth()->id())->where('is_seen', 0);
                    })
                    ->with(['notification_users' => function ($q) {
                        $q->where('user_id', auth()->id())->where('is_seen', 0);
                    }]);
                } else {
                    $data = $data->whereHas('notification_users', function ($q) {
                        $q->where('user_id', auth()->id());
                    })
                    ->with(['notification_users' => function ($q) {
                        $q->where('user_id', auth()->id());
                    }]);
                }
            }




            if (request()->has('search') && request()->input('search')) {
                $searchKey = request()->input('search');
                $data = $data->where(function ($q) use ($searchKey) {
                    $q->where('type', 'like', '%' . $searchKey . '%');

                    $q->orWhere('title', 'like', '%' . $searchKey . '%');

                    $q->orWhere('link', 'like', '%' . $searchKey . '%');
                });
            }

            if ($start_date && $end_date) {
                if ($end_date > $start_date) {
                    $data->whereBetween('created_at', [$start_date . ' 00:00:00', $end_date . ' 23:59:59']);
                } elseif ($end_date == $start_date) {
                    $data->whereDate('created_at', $start_date);
                }
            }

            if ($status == 'trased') {
                $data = $data->trased();
            }


            if (request()->has('get_all') && (int)request()->input('get_all') === 1) {
                $data = $data
                    ->with($with)
                    ->select($fields)
                    ->where($condition)
                    ->where('status', $status)
                    ->limit($pageLimit)
                    ->orderBy($orderByColumn, $orderByType)
                    ->get();
                // Convert notification_users array to object and add is_seen
                $data->transform(function ($item) {
                    $item->notification_user = isset($item->notification_users[0]) ? $item->notification_users[0] : null;
                    unset($item->notification_users);
                    $item->is_seen = $item->notification_user ? $item->notification_user->is_seen : null;
                    return $item;
                });
                return entityResponse($data);
            } else if ($status == 'trased') {
                $data = $data
                    ->with($with)
                    ->select($fields)
                    ->where($condition)
                    ->orderBy($orderByColumn, $orderByType)
                    ->paginate($pageLimit);
                $data->getCollection()->transform(function ($item) {
                    $item->notification_user = isset($item->notification_users[0]) ? $item->notification_users[0] : null;
                    unset($item->notification_users);
                    $item->is_seen = $item->notification_user ? $item->notification_user->is_seen : null;
                    return $item;
                });
            } else {
                $data = $data
                    ->with($with)
                    ->select($fields)
                    ->where($condition)
                    ->where('status', $status)
                    ->orderBy($orderByColumn, $orderByType)
                    ->paginate($pageLimit);
                $data->getCollection()->transform(function ($item) {
                    $item->notification_user = isset($item->notification_users[0]) ? $item->notification_users[0] : null;
                    unset($item->notification_users);
                    $item->is_seen = $item->notification_user ? $item->notification_user->is_seen : null;
                    return $item;
                });
            }

            return entityResponse([
                ...$data->toArray(),
                "active_data_count" => self::$model::active()->count(),
                "inactive_data_count" => self::$model::inactive()->count(),
                "trased_data_count" => self::$model::trased()->count(),
            ]);
        } catch (\Exception $e) {
            return messageResponse($e->getMessage(), [], 500, 'server_error');
        }
    }
}
