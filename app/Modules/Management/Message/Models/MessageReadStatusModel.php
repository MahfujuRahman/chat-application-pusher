<?php

namespace App\Modules\Management\Message\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;

class MessageReadStatusModel extends EloquentModel
{
    protected $table = "message_read_status";
    protected $guarded = [];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function message()
    {
        return $this->belongsTo(\App\Modules\Management\Message\Models\Model::class, 'message_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
