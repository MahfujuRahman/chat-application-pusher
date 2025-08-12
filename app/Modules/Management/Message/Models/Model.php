<?php

namespace App\Modules\Management\Message\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;

class Model extends EloquentModel
{
    use SoftDeletes;
    protected $table = "messages";
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



    public function sender()
    {
        return $this->belongsTo(\App\Modules\Management\UserManagement\User\Models\Model::class, 'sender');
    }

    public function receiver()
    {
        return $this->belongsTo(\App\Modules\Management\UserManagement\User\Models\Model::class, 'receiver');
    }
    public function conversation()
    {
        return $this->belongsTo(\App\Modules\Management\Message\Models\ConversationModel::class, 'conversation_id');
    }

    public function readStatus()
    {
        return $this->hasMany(\App\Modules\Management\Message\Models\MessageReadStatusModel::class, 'message_id');
    }

    public function isReadBy($userId)
    {
        return $this->readStatus()->where('user_id', $userId)->exists();
    }
}
