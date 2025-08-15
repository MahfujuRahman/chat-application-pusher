<?php

namespace App\Modules\Management\Message\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConversationModel extends EloquentModel
{
    use SoftDeletes;
    protected $table = "conversation";
    protected $guarded = [];

    protected $casts = [
        'is_group' => 'boolean',
        'group_participants' => 'array',
    ];


    public function participantUser()
    {
        return $this->belongsTo(\App\Models\User::class, 'participant', 'id');
    }


    public function creatorUser()
    {
        return $this->belongsTo(\App\Modules\Management\UserManagement\User\Models\Model::class, 'creator');
    }

    public function messages()
    {
        return $this->hasMany(\App\Modules\Management\Message\Models\Model::class, 'conversation_id');
    }

    // Helper method to get all participants for group chats
    public function getAllParticipants()
    {
        if ($this->is_group && $this->group_participants) {
            return \App\Models\User::whereIn('id', $this->group_participants)->get();
        }
        
        // For regular conversations, return both creator and participant
        $userIds = array_filter([$this->creator, $this->participant]);
        return \App\Models\User::whereIn('id', $userIds)->get();
    }

    // Helper method to check if user is part of conversation
    public function hasParticipant($userId)
    {
        if ($this->is_group && $this->group_participants) {
            return in_array($userId, $this->group_participants);
        }
        
        return $this->creator == $userId || $this->participant == $userId;
    }
}
