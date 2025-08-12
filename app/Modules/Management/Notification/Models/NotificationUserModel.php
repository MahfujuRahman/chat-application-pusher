<?php

namespace App\Modules\Management\Notification\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;

class NotificationUserModel extends EloquentModel
{
    use SoftDeletes;
    protected $table = "notification_users";
    protected $guarded = [];
}
