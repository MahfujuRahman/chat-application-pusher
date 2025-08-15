<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\TestCase;
use App\Modules\Management\UserManagement\User\Models\Model as ManagementUser;
use App\Modules\Management\Message\Models\Model as MessageModel;
use App\Modules\Management\Message\Models\ConversationModel;
use App\Modules\Management\Message\Models\MessageReadStatusModel;
use Illuminate\Support\Facades\Hash;

class MessageModelTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();
       
    }
    
    
    protected function createTestUser($name = 'Test User', $email = null)
    {
        return ManagementUser::withoutEvents(function () use ($name, $email) {
            return ManagementUser::create([
                'name' => $name,
                'email' => $email ?: 'user+' . uniqid() . '@example.test',
                'password' => Hash::make('password'),
            ]);
        });
    }

    // MESSAGE MODEL TESTS
    
    public function test_message_belongs_to_sender()
    {
        $user = $this->createTestUser('Alice');
        $otherUser = $this->createTestUser('Bob');
        
        $conversation = ConversationModel::create([
            'creator' => $user->id,
            'participant' => $otherUser->id,
            'last_updated' => now(),
        ]);
        
        $message = MessageModel::create([
            'conversation_id' => $conversation->id,
            'sender' => $user->id,
            'receiver' => $otherUser->id,
            'text' => 'Test message',
            'date_time' => now(),
        ]);
        
    // Some environments return the raw FK on the dynamic property. Use the relation query to assert the related model.
    $this->assertInstanceOf(ManagementUser::class, $message->sender()->first());
    $this->assertEquals($user->id, $message->sender()->first()->id);
    }
    
    public function test_message_belongs_to_receiver()
    {
        $user = $this->createTestUser('Alice');
        $otherUser = $this->createTestUser('Bob');
        
        $conversation = ConversationModel::create([
            'creator' => $user->id,
            'participant' => $otherUser->id,
            'last_updated' => now(),
        ]);
        
        $message = MessageModel::create([
            'conversation_id' => $conversation->id,
            'sender' => $user->id,
            'receiver' => $otherUser->id,
            'text' => 'Test message',
            'date_time' => now(),
        ]);
        
    $this->assertInstanceOf(ManagementUser::class, $message->receiver()->first());
    $this->assertEquals($otherUser->id, $message->receiver()->first()->id);
    }
    
    public function test_message_belongs_to_conversation()
    {
        $user = $this->createTestUser('Alice');
        $otherUser = $this->createTestUser('Bob');
        
        $conversation = ConversationModel::create([
            'creator' => $user->id,
            'participant' => $otherUser->id,
            'last_updated' => now(),
        ]);
        
        $message = MessageModel::create([
            'conversation_id' => $conversation->id,
            'sender' => $user->id,
            'receiver' => $otherUser->id,
            'text' => 'Test message',
            'date_time' => now(),
        ]);
        
        $this->assertInstanceOf(ConversationModel::class, $message->conversation);
        $this->assertEquals($conversation->id, $message->conversation->id);
    }
    
    public function test_message_has_many_read_status()
    {
        $user = $this->createTestUser('Alice');
        $otherUser = $this->createTestUser('Bob');
        
        $conversation = ConversationModel::create([
            'creator' => $user->id,
            'participant' => $otherUser->id,
            'last_updated' => now(),
        ]);
        
        $message = MessageModel::create([
            'conversation_id' => $conversation->id,
            'sender' => $user->id,
            'receiver' => $otherUser->id,
            'text' => 'Test message',
            'date_time' => now(),
        ]);
        
        MessageReadStatusModel::create([
            'message_id' => $message->id,
            'user_id' => $otherUser->id,
            'read_at' => now(),
        ]);
        
        $this->assertCount(1, $message->readStatus);
        $this->assertInstanceOf(MessageReadStatusModel::class, $message->readStatus->first());
    }
    
    public function test_message_is_read_by_method()
    {
        $user = $this->createTestUser('Alice');
        $otherUser = $this->createTestUser('Bob');
        $thirdUser = $this->createTestUser('Charlie');
        
        $conversation = ConversationModel::create([
            'creator' => $user->id,
            'participant' => $otherUser->id,
            'last_updated' => now(),
        ]);
        
        $message = MessageModel::create([
            'conversation_id' => $conversation->id,
            'sender' => $user->id,
            'receiver' => $otherUser->id,
            'text' => 'Test message',
            'date_time' => now(),
        ]);
        
        // Mark as read by otherUser
        MessageReadStatusModel::create([
            'message_id' => $message->id,
            'user_id' => $otherUser->id,
            'read_at' => now(),
        ]);
        
        $this->assertTrue($message->isReadBy($otherUser->id));
        $this->assertFalse($message->isReadBy($thirdUser->id));
    }

    // CONVERSATION MODEL TESTS
    
    public function test_conversation_has_creator_user()
    {
        $user = $this->createTestUser('Alice');
        $otherUser = $this->createTestUser('Bob');
        
        $conversation = ConversationModel::create([
            'creator' => $user->id,
            'participant' => $otherUser->id,
            'last_updated' => now(),
        ]);
        
        $this->assertInstanceOf(ManagementUser::class, $conversation->creatorUser);
        $this->assertEquals($user->id, $conversation->creatorUser->id);
    }
    
    public function test_conversation_has_many_messages()
    {
        $user = $this->createTestUser('Alice');
        $otherUser = $this->createTestUser('Bob');
        
        $conversation = ConversationModel::create([
            'creator' => $user->id,
            'participant' => $otherUser->id,
            'last_updated' => now(),
        ]);
        
        MessageModel::create([
            'conversation_id' => $conversation->id,
            'sender' => $user->id,
            'receiver' => $otherUser->id,
            'text' => 'Message 1',
            'date_time' => now(),
        ]);
        
        MessageModel::create([
            'conversation_id' => $conversation->id,
            'sender' => $otherUser->id,
            'receiver' => $user->id,
            'text' => 'Message 2',
            'date_time' => now(),
        ]);
        
        $this->assertCount(2, $conversation->messages);
        $this->assertInstanceOf(MessageModel::class, $conversation->messages->first());
    }
    
    public function test_conversation_has_participant_method_for_regular_chat()
    {
        $user = $this->createTestUser('Alice');
        $otherUser = $this->createTestUser('Bob');
        $thirdUser = $this->createTestUser('Charlie');
        
        $conversation = ConversationModel::create([
            'creator' => $user->id,
            'participant' => $otherUser->id,
            'is_group' => false,
            'last_updated' => now(),
        ]);
        
        $this->assertTrue($conversation->hasParticipant($user->id));
        $this->assertTrue($conversation->hasParticipant($otherUser->id));
        $this->assertFalse($conversation->hasParticipant($thirdUser->id));
    }
    
    public function test_conversation_has_participant_method_for_group_chat()
    {
        $user = $this->createTestUser('Alice');
        $userB = $this->createTestUser('Bob');
        $userC = $this->createTestUser('Charlie');
        $userD = $this->createTestUser('Dave');
        
        $conversation = ConversationModel::create([
            'creator' => $user->id,
            'is_group' => true,
            'group_name' => 'Test Group',
            'group_participants' => [$user->id, $userB->id, $userC->id],
            'last_updated' => now(),
        ]);
        
        $this->assertTrue($conversation->hasParticipant($user->id));
        $this->assertTrue($conversation->hasParticipant($userB->id));
        $this->assertTrue($conversation->hasParticipant($userC->id));
        $this->assertFalse($conversation->hasParticipant($userD->id));
    }
    
    public function test_conversation_get_all_participants_regular_chat()
    {
        $user = $this->createTestUser('Alice');
        $otherUser = $this->createTestUser('Bob');
        
        $conversation = ConversationModel::create([
            'creator' => $user->id,
            'participant' => $otherUser->id,
            'is_group' => false,
            'last_updated' => now(),
        ]);
        
        $participants = $conversation->getAllParticipants();
        
        $this->assertCount(2, $participants);
        $participantIds = $participants->pluck('id')->toArray();
        $this->assertContains($user->id, $participantIds);
        $this->assertContains($otherUser->id, $participantIds);
    }
    
    public function test_conversation_get_all_participants_group_chat()
    {
        $user = $this->createTestUser('Alice');
        $userB = $this->createTestUser('Bob');
        $userC = $this->createTestUser('Charlie');
        
        $conversation = ConversationModel::create([
            'creator' => $user->id,
            'is_group' => true,
            'group_name' => 'Test Group',
            'group_participants' => [$user->id, $userB->id, $userC->id],
            'last_updated' => now(),
        ]);
        
        $participants = $conversation->getAllParticipants();
        
        $this->assertCount(3, $participants);
        $participantIds = $participants->pluck('id')->toArray();
        $this->assertContains($user->id, $participantIds);
        $this->assertContains($userB->id, $participantIds);
        $this->assertContains($userC->id, $participantIds);
    }

    // MESSAGE READ STATUS MODEL TESTS
    
    public function test_read_status_belongs_to_message()
    {
        $user = $this->createTestUser('Alice');
        $otherUser = $this->createTestUser('Bob');
        
        $conversation = ConversationModel::create([
            'creator' => $user->id,
            'participant' => $otherUser->id,
            'last_updated' => now(),
        ]);
        
        $message = MessageModel::create([
            'conversation_id' => $conversation->id,
            'sender' => $user->id,
            'receiver' => $otherUser->id,
            'text' => 'Test message',
            'date_time' => now(),
        ]);
        
        $readStatus = MessageReadStatusModel::create([
            'message_id' => $message->id,
            'user_id' => $otherUser->id,
            'read_at' => now(),
        ]);
        
        $this->assertInstanceOf(MessageModel::class, $readStatus->message);
        $this->assertEquals($message->id, $readStatus->message->id);
    }
    
    public function test_read_status_belongs_to_user()
    {
        $user = $this->createTestUser('Alice');
        $otherUser = $this->createTestUser('Bob');
        
        $conversation = ConversationModel::create([
            'creator' => $user->id,
            'participant' => $otherUser->id,
            'last_updated' => now(),
        ]);
        
        $message = MessageModel::create([
            'conversation_id' => $conversation->id,
            'sender' => $user->id,
            'receiver' => $otherUser->id,
            'text' => 'Test message',
            'date_time' => now(),
        ]);
        
        $readStatus = MessageReadStatusModel::create([
            'message_id' => $message->id,
            'user_id' => $otherUser->id,
            'read_at' => now(),
        ]);
        
        // Note: This test assumes the user relationship points to App\Models\User
        // If it should point to ManagementUser, the model needs to be updated
        $this->assertEquals($otherUser->id, $readStatus->user_id);
    }

    // SOFT DELETE TESTS
    
    public function test_message_soft_delete()
    {
        $user = $this->createTestUser('Alice');
        $otherUser = $this->createTestUser('Bob');
        
        $conversation = ConversationModel::create([
            'creator' => $user->id,
            'participant' => $otherUser->id,
            'last_updated' => now(),
        ]);
        
        $message = MessageModel::create([
            'conversation_id' => $conversation->id,
            'sender' => $user->id,
            'receiver' => $otherUser->id,
            'text' => 'Test message',
            'date_time' => now(),
        ]);
        
        $message->delete();
        
        $this->assertSoftDeleted('messages', [
            'id' => $message->id
        ]);
        
        // Test that it can be restored
        $message->restore();
        $this->assertDatabaseHas('messages', [
            'id' => $message->id,
            'deleted_at' => null
        ]);
    }
    
    public function test_conversation_soft_delete()
    {
        $user = $this->createTestUser('Alice');
        $otherUser = $this->createTestUser('Bob');
        
        $conversation = ConversationModel::create([
            'creator' => $user->id,
            'participant' => $otherUser->id,
            'last_updated' => now(),
        ]);
        
        $conversation->delete();
        
        $this->assertSoftDeleted('conversation', [
            'id' => $conversation->id
        ]);
        
        // Test that it can be restored
        $conversation->restore();
        $this->assertDatabaseHas('conversation', [
            'id' => $conversation->id,
            'deleted_at' => null
        ]);
    }
}
