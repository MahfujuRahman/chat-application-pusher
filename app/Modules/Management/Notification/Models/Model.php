<?php

namespace App\Modules\Management\Notification\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;

class Model extends EloquentModel
{
    use SoftDeletes;
    protected $table = "notifications";
    protected $guarded = [];



    public function scopeActive($q)
    {
        return $q->where('status', 'active');
    }

    public function scopeInactive($q)
    {
        return $q->where('status', 'inactive');
    }
    public function scopeTrased($q)
    {
        return $q->onlyTrashed();
    }
    public function notification_users()
    {
        return $this->hasMany(\App\Modules\Management\Notification\Models\NotificationUserModel::class, 'notification_id', 'id');
    }
}
