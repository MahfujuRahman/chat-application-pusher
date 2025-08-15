<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\TestCase;
use App\Modules\Management\UserManagement\User\Models\Model as ManagementUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use App\Events\MessageSent;

class MessageActionsTest extends TestCase
{
    // use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Ensure web guard is used for auth()->id()
        \Illuminate\Support\Facades\Auth::shouldUse('web');
        
    }
    
    protected function assertResponseOk($response)
    {
        $status = method_exists($response, 'getStatusCode') ? $response->getStatusCode() : null;
        if ($status !== 200) {
            if (method_exists($response, 'getContent')) {
                fwrite(STDOUT, "\nRESPONSE CONTENT: " . $response->getContent() . "\n");
            } else {
                fwrite(STDOUT, "\nRESPONSE OBJECT: " . print_r($response, true) . "\n");
            }
        }
        $this->assertEquals(200, $status);
    }
    
    protected function createTestUser($name = 'Test User', $email = null)
    {
        return ManagementUser::create([
            'name' => $name,
            'email' => $email ?: 'user+' . uniqid() . '@example.test',
            'password' => Hash::make('password'),
        ]);
    }

    /**
     * Decode various response objects returned by action classes.
     */
    protected function decodeResponse($response)
    {
        if (method_exists($response, 'getData')) {
            return $response->getData(true);
        }

        if (method_exists($response, 'getContent')) {
            $content = $response->getContent();
            $decoded = json_decode($content, true);
            if (is_array($decoded)) {
                return $decoded;
            }
            return ['data' => $content];
        }

        if (is_array($response)) {
            return $response;
        }

        return [];
    }

    
    // UNIT TESTS FOR MESSAGE ACTIONS
    
    public function test_start_conversation_action_creates_new_conversation()
    {
        $userA = $this->createTestUser('Alice');
        $userB = $this->createTestUser('Bob');

        $this->actingAs($userA, 'web');

        $req = new \Illuminate\Http\Request(['participant_id' => $userB->id]);
        $req->setUserResolver(function () use ($userA) { return $userA; });

        $response = \App\Modules\Management\Message\Actions\StartConversation::execute($req);
        $this->assertResponseOk($response);
        
        $payload = $this->decodeResponse($response);
        $this->assertArrayHasKey('data', $payload);
        $this->assertNotNull($payload['data']['id'] ?? null);
        
        // Verify conversation exists in database
        $this->assertDatabaseHas('conversation', [
            'creator' => $userA->id,
            'participant' => $userB->id,
            'is_group' => false,
        ]);
    }
    
    public function test_start_conversation_prevents_duplicate_conversations()
    {
        $userA = $this->createTestUser('Alice');
        $userB = $this->createTestUser('Bob');

        // Create existing conversation
        \App\Modules\Management\Message\Models\ConversationModel::create([
            'creator' => $userA->id,
            'participant' => $userB->id,
            'last_updated' => now(),
        ]);

        $this->actingAs($userA, 'web');

        $req = new \Illuminate\Http\Request(['participant_id' => $userB->id]);
        $req->setUserResolver(function () use ($userA) { return $userA; });

        $response = \App\Modules\Management\Message\Actions\StartConversation::execute($req);
        
        // Should return error for duplicate conversation
        $this->assertEquals(404, method_exists($response, 'getStatusCode') ? $response->getStatusCode() : null);
    }

    public function test_send_message_action_creates_message()
    {
        Event::fake([MessageSent::class]);
        
        $userA = $this->createTestUser('Alice');
        $userB = $this->createTestUser('Bob');

        $conversation = \App\Modules\Management\Message\Models\ConversationModel::create([
            'creator' => $userA->id,
            'participant' => $userB->id,
            'last_updated' => now(),
        ]);

        $this->actingAs($userA, 'web');

        $sendReq = new \Illuminate\Http\Request([
            'conversation_id' => $conversation->id,
            'text' => 'Hello Bob!'
        ]);
        $sendReq->setUserResolver(function () use ($userA) { return $userA; });

        $response = \App\Modules\Management\Message\Actions\SendMessage::execute($sendReq);
        $this->assertResponseOk($response);
        
        $payload = $this->decodeResponse($response);
        $this->assertArrayHasKey('data', $payload);
        
        // Verify message in database
        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'sender' => $userA->id,
            'receiver' => $userB->id,
            'text' => 'Hello Bob!'
        ]);
        
        // Verify event was fired
        Event::assertDispatched(MessageSent::class);
    }
    
    public function test_send_message_validates_required_fields()
    {
        $userA = $this->createTestUser('Alice');
        $this->actingAs($userA, 'web');

        // Test missing conversation_id
        $req1 = new \Illuminate\Http\Request(['text' => 'Hello']);
        $req1->setUserResolver(function () use ($userA) { return $userA; });
        $response1 = \App\Modules\Management\Message\Actions\SendMessage::execute($req1);
        $this->assertEquals(422, method_exists($response1, 'getStatusCode') ? $response1->getStatusCode() : null);

        // Test missing text
        $req2 = new \Illuminate\Http\Request(['conversation_id' => 1]);
        $req2->setUserResolver(function () use ($userA) { return $userA; });
        $response2 = \App\Modules\Management\Message\Actions\SendMessage::execute($req2);
        $this->assertEquals(422, method_exists($response2, 'getStatusCode') ? $response2->getStatusCode() : null);
    }

    public function test_get_conversation_messages_returns_paginated_messages()
    {
        $userA = $this->createTestUser('Alice');
        $userB = $this->createTestUser('Bob');

        $conversation = \App\Modules\Management\Message\Models\ConversationModel::create([
            'creator' => $userA->id,
            'participant' => $userB->id,
            'last_updated' => now(),
        ]);

        // Create test messages
        \App\Modules\Management\Message\Models\Model::create([
            'conversation_id' => $conversation->id,
            'sender' => $userA->id,
            'receiver' => $userB->id,
            'text' => 'Message 1',
            'date_time' => now(),
        ]);

        \App\Modules\Management\Message\Models\Model::create([
            'conversation_id' => $conversation->id,
            'sender' => $userB->id,
            'receiver' => $userA->id,
            'text' => 'Message 2',
            'date_time' => now(),
        ]);

        $this->actingAs($userA, 'web');
        request()->setUserResolver(function () use ($userA) { return $userA; });

        $response = \App\Modules\Management\Message\Actions\GetConversationMessages::execute($conversation->id);
        $this->assertResponseOk($response);
        
        $payload = $this->decodeResponse($response);
        $this->assertArrayHasKey('data', $payload);
        $this->assertCount(2, $payload['data']);
        
    // Check message types are set correctly (backend may return newest-first ordering)
    $this->assertEquals('theirs', $payload['data'][0]['type']);
    $this->assertEquals('mine', $payload['data'][1]['type']);
    }

    public function test_mark_messages_as_read_creates_read_status()
    {
        $userA = $this->createTestUser('Alice');
        $userB = $this->createTestUser('Bob');

        $conversation = \App\Modules\Management\Message\Models\ConversationModel::create([
            'creator' => $userA->id,
            'participant' => $userB->id,
            'last_updated' => now(),
        ]);

        $message = \App\Modules\Management\Message\Models\Model::create([
            'conversation_id' => $conversation->id,
            'sender' => $userA->id,
            'receiver' => $userB->id,
            'text' => 'Unread message',
            'date_time' => now(),
        ]);

        $this->actingAs($userB, 'web');
        request()->setUserResolver(function () use ($userB) { return $userB; });

        $response = \App\Modules\Management\Message\Actions\MarkMessagesAsRead::execute($conversation->id);
        $this->assertResponseOk($response);

        // Verify read status was created
        $this->assertDatabaseHas('message_read_status', [
            'message_id' => $message->id,
            'user_id' => $userB->id,
        ]);
    }

    public function test_get_all_conversations_returns_user_conversations()
    {
        $userA = $this->createTestUser('Alice');
        $userB = $this->createTestUser('Bob');
        $userC = $this->createTestUser('Charlie');

        // Create conversations for userA
        $conv1 = \App\Modules\Management\Message\Models\ConversationModel::create([
            'creator' => $userA->id,
            'participant' => $userB->id,
            'last_updated' => now(),
        ]);

        $conv2 = \App\Modules\Management\Message\Models\ConversationModel::create([
            'creator' => $userC->id,
            'participant' => $userA->id,
            'last_updated' => now(),
        ]);

        // Create a conversation userA is not part of
        \App\Modules\Management\Message\Models\ConversationModel::create([
            'creator' => $userB->id,
            'participant' => $userC->id,
            'last_updated' => now(),
        ]);

        $this->actingAs($userA, 'web');

        $response = \App\Modules\Management\Message\Actions\GetAllConversations::execute();
        $this->assertResponseOk($response);
        
        $payload = $this->decodeResponse($response);
        $this->assertArrayHasKey('data', $payload);
        $this->assertCount(2, $payload['data']); // Should only return userA's conversations
    }
    
    // GROUP CHAT TESTS
    
    public function test_create_group_chat_action()
    {
        $userA = $this->createTestUser('Alice');
        $userB = $this->createTestUser('Bob');
        $userC = $this->createTestUser('Charlie');

        $this->actingAs($userA, 'web');

        // backend validation expects 'name' field for group name
        $req = new \Illuminate\Http\Request([
            'name' => 'Test Group',
            'participant_ids' => [$userB->id, $userC->id]
        ]);
        $req->setUserResolver(function () use ($userA) { return $userA; });

        $response = \App\Modules\Management\Message\Actions\CreateGroupChat::execute($req);
        $this->assertResponseOk($response);
        
        $payload = $this->decodeResponse($response);
        $this->assertArrayHasKey('data', $payload);
        
        // Verify group conversation in database
        $this->assertDatabaseHas('conversation', [
            'creator' => $userA->id,
            'is_group' => true,
            'group_name' => 'Test Group',
        ]);
        
        // Verify group participants are stored
        $conversation = \App\Modules\Management\Message\Models\ConversationModel::where('creator', $userA->id)->first();
        $this->assertNotNull($conversation->group_participants);
        $this->assertContains($userA->id, $conversation->group_participants);
        $this->assertContains($userB->id, $conversation->group_participants);
        $this->assertContains($userC->id, $conversation->group_participants);
    }

    public function test_send_message_to_group_chat()
    {
        Event::fake([MessageSent::class]);
        
        $userA = $this->createTestUser('Alice');
        $userB = $this->createTestUser('Bob');
        $userC = $this->createTestUser('Charlie');

        $conversation = \App\Modules\Management\Message\Models\ConversationModel::create([
            'creator' => $userA->id,
            'is_group' => true,
            'group_name' => 'Test Group',
            'group_participants' => [$userA->id, $userB->id, $userC->id],
            'last_updated' => now(),
        ]);

        $this->actingAs($userA, 'web');

        $req = new \Illuminate\Http\Request([
            'conversation_id' => $conversation->id,
            'text' => 'Hello group!'
        ]);
        $req->setUserResolver(function () use ($userA) { return $userA; });

        $response = \App\Modules\Management\Message\Actions\SendMessage::execute($req);
        $this->assertResponseOk($response);
        
        // Verify message was created for group
        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'sender' => $userA->id,
            'receiver' => null, // Group messages don't have specific receiver
            'text' => 'Hello group!'
        ]);
    }

    public function test_get_group_members_action()
    {
        $userA = $this->createTestUser('Alice');
        $userB = $this->createTestUser('Bob');
        $userC = $this->createTestUser('Charlie');

        $conversation = \App\Modules\Management\Message\Models\ConversationModel::create([
            'creator' => $userA->id,
            'is_group' => true,
            'group_name' => 'Test Group',
            'group_participants' => [$userA->id, $userB->id, $userC->id],
            'last_updated' => now(),
        ]);

        $this->actingAs($userA, 'web');

        $response = \App\Modules\Management\Message\Actions\GetGroupMembers::execute($conversation->id);
        $this->assertResponseOk($response);
        
        $payload = $this->decodeResponse($response);
        $this->assertArrayHasKey('data', $payload);
        $this->assertCount(3, $payload['data']);
    }

    public function test_add_group_members_action()
    {
        $userA = $this->createTestUser('Alice');
        $userB = $this->createTestUser('Bob');
        $userC = $this->createTestUser('Charlie');
        $userD = $this->createTestUser('Dave');

        $conversation = \App\Modules\Management\Message\Models\ConversationModel::create([
            'creator' => $userA->id,
            'is_group' => true,
            'group_name' => 'Test Group',
            'group_participants' => [$userA->id, $userB->id],
            'last_updated' => now(),
        ]);

        $this->actingAs($userA, 'web');

        $req = new \Illuminate\Http\Request([
            'conversation_id' => $conversation->id,
            'user_ids' => [$userC->id, $userD->id]
        ]);
        $req->setUserResolver(function () use ($userA) { return $userA; });

        $response = \App\Modules\Management\Message\Actions\AddGroupMembers::execute($req);
        $this->assertResponseOk($response);
        
        // Verify new members were added
        $conversation->refresh();
        $this->assertContains($userC->id, $conversation->group_participants);
        $this->assertContains($userD->id, $conversation->group_participants);
        $this->assertCount(4, $conversation->group_participants);
    }

    public function test_remove_group_member_action()
    {
        $userA = $this->createTestUser('Alice');
        $userB = $this->createTestUser('Bob');
        $userC = $this->createTestUser('Charlie');

        $conversation = \App\Modules\Management\Message\Models\ConversationModel::create([
            'creator' => $userA->id,
            'is_group' => true,
            'group_name' => 'Test Group',
            'group_participants' => [$userA->id, $userB->id, $userC->id],
            'last_updated' => now(),
        ]);

        $this->actingAs($userA, 'web');

        $req = new \Illuminate\Http\Request([
            'conversation_id' => $conversation->id,
            'user_id' => $userB->id
        ]);
        $req->setUserResolver(function () use ($userA) { return $userA; });

        $response = \App\Modules\Management\Message\Actions\RemoveGroupMember::execute($req);
        $this->assertResponseOk($response);
        
        // Verify member was removed
        $conversation->refresh();
        $this->assertNotContains($userB->id, $conversation->group_participants);
        $this->assertCount(2, $conversation->group_participants);
    }

    public function test_update_group_action()
    {
        $userA = $this->createTestUser('Alice');

        $conversation = \App\Modules\Management\Message\Models\ConversationModel::create([
            'creator' => $userA->id,
            'is_group' => true,
            'group_name' => 'Old Group Name',
            'group_participants' => [$userA->id],
            'last_updated' => now(),
        ]);

        $this->actingAs($userA, 'web');

        $req = new \Illuminate\Http\Request([
            'group_name' => 'New Group Name'
        ]);
        $req->setUserResolver(function () use ($userA) { return $userA; });

        $response = \App\Modules\Management\Message\Actions\UpdateGroup::execute($req, $conversation->id);
        $this->assertResponseOk($response);
        
        // Verify group name was updated
        $this->assertDatabaseHas('conversation', [
            'id' => $conversation->id,
            'group_name' => 'New Group Name'
        ]);
    }

    public function test_delete_group_action()
    {
        $userA = $this->createTestUser('Alice');

        $conversation = \App\Modules\Management\Message\Models\ConversationModel::create([
            'creator' => $userA->id,
            'is_group' => true,
            'group_name' => 'Test Group',
            'group_participants' => [$userA->id],
            'last_updated' => now(),
        ]);

        $this->actingAs($userA, 'web');

        $response = \App\Modules\Management\Message\Actions\DeleteGroup::execute($conversation->id);
        $this->assertResponseOk($response);
        
        // Verify group was soft deleted
        $this->assertSoftDeleted('conversation', [
            'id' => $conversation->id
        ]);
    }

    public function test_get_available_users_action()
    {
        $userA = $this->createTestUser('Alice');
        $userB = $this->createTestUser('Bob');
        $userC = $this->createTestUser('Charlie');
        $userD = $this->createTestUser('Dave');

        $conversation = \App\Modules\Management\Message\Models\ConversationModel::create([
            'creator' => $userA->id,
            'is_group' => true,
            'group_name' => 'Test Group',
            'group_participants' => [$userA->id, $userB->id],
            'last_updated' => now(),
        ]);

        $this->actingAs($userA, 'web');

        $response = \App\Modules\Management\Message\Actions\GetAvailableUsers::execute($conversation->id);
        $this->assertResponseOk($response);
        
        $payload = $this->decodeResponse($response);
        $this->assertArrayHasKey('data', $payload);
        
    // Ensure response is an array and contains the expected available users (C and D)
    $this->assertIsArray($payload['data']);
    $userIds = collect($payload['data'])->pluck('id')->toArray();
    $this->assertGreaterThanOrEqual(2, count($userIds));
    $this->assertContains($userC->id, $userIds);
    $this->assertContains($userD->id, $userIds);
    }

}
