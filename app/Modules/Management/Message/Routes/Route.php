<?php

use App\Modules\Management\Message\Controller\Controller;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::prefix('messages')->middleware('auth:api')->group(function () {
        Route::get('get-all-conversations', [Controller::class, 'GetAllConversations']);
        Route::get('get-conversation-messages/{id}', [Controller::class, 'GetConversationMessages']);
        Route::post('start-conversation', [Controller::class, 'StartConversation']);
        Route::post('create-group-chat', [Controller::class, 'CreateGroupChat']);
        Route::get('group-members/{conversationId}', [Controller::class, 'GetGroupMembers']);
        Route::get('available-users/{conversationId}', [Controller::class, 'GetAvailableUsers']);
        Route::post('add-group-members', [Controller::class, 'AddGroupMembers']);
        Route::post('remove-group-member', [Controller::class, 'RemoveGroupMember']);
        Route::put('conversations/{id}/group', [Controller::class, 'UpdateGroup']);
        Route::delete('conversations/{id}/group', [Controller::class, 'DeleteGroup']);
        Route::post('mark-as-read/{conversationId}', [Controller::class, 'MarkMessagesAsRead']);
        Route::post('send', [Controller::class, 'SendMessage']);
    });
});
