<?php

namespace App\Modules\Management\Message\Controller;

use App\Modules\Management\Message\Actions\GetAllConversations;
use App\Modules\Management\Message\Actions\GetConversationMessages;
use App\Modules\Management\Message\Actions\StartConversation;
use App\Modules\Management\Message\Actions\CreateGroupChat;
use App\Modules\Management\Message\Actions\GetGroupMembers;
use App\Modules\Management\Message\Actions\GetAvailableUsers;
use App\Modules\Management\Message\Actions\AddGroupMembers;
use App\Modules\Management\Message\Actions\RemoveGroupMember;
use App\Modules\Management\Message\Actions\UpdateGroup;
use App\Modules\Management\Message\Actions\DeleteGroup;
use App\Modules\Management\Message\Actions\MarkMessagesAsRead;
use App\Modules\Management\Message\Actions\SendMessage;
use App\Http\Controllers\Controller as ControllersController;
use Illuminate\Http\Request;

class Controller extends ControllersController
{

    public function GetAllConversations()
    {

        $data = GetAllConversations::execute();
        return $data;
    }
    public function GetConversationMessages($id)
    {

        $data = GetConversationMessages::execute($id);
        return $data;
    }
    public function StartConversation(Request $request)
    {
        $data = StartConversation::execute($request);
        return $data;
    }
    
    public function CreateGroupChat(Request $request)
    {
        $data = CreateGroupChat::execute($request);
        return $data;
    }
    
    public function GetGroupMembers($conversationId)
    {
        $data = GetGroupMembers::execute($conversationId);
        return $data;
    }
    
    public function GetAvailableUsers($conversationId)
    {
        $data = GetAvailableUsers::execute($conversationId);
        return $data;
    }
    
    public function AddGroupMembers(Request $request)
    {
        $data = AddGroupMembers::execute($request);
        return $data;
    }
    
    public function RemoveGroupMember(Request $request)
    {
        $data = RemoveGroupMember::execute($request);
        return $data;
    }
    
    public function MarkMessagesAsRead($conversationId)
    {
        $data = MarkMessagesAsRead::execute($conversationId);
        return $data;
    }
    
    public function SendMessage(Request $request)
    {
        $data = SendMessage::execute($request);
        return $data;
    }
    
    public function UpdateGroup(Request $request, $id)
    {
        $data = UpdateGroup::execute($request, $id);
        return $data;
    }
    
    public function DeleteGroup($id)
    {
        $data = DeleteGroup::execute($id);
        return $data;
    }
}
