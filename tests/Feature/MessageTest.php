<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\TestCase;
use App\Modules\Management\UserManagement\User\Models\Model as ManagementUser;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Passport;
use Illuminate\Support\Facades\Event;
use App\Events\MessageSent;

class MessageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create database schema
        $this->createTestSchema();
    }
    
    protected function createTestSchema()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('remember_token', 100)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('conversation', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('creator')->nullable();
            $table->unsignedBigInteger('participant')->nullable();
            $table->boolean('is_group')->default(false);
            $table->string('group_name')->nullable();
            $table->json('group_participants')->nullable();
            $table->timestamp('last_updated')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('conversation_id');
            $table->unsignedBigInteger('sender');
            $table->unsignedBigInteger('receiver')->nullable();
            $table->text('text')->nullable();
            $table->string('file_path')->nullable();
            $table->timestamp('date_time')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('message_read_status', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('message_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
        
        // Create OAuth tables for Passport
        Schema::create('oauth_auth_codes', function (Blueprint $table) {
            $table->string('id', 100)->primary();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('client_id');
            $table->text('scopes')->nullable();
            $table->boolean('revoked');
            $table->dateTime('expires_at')->nullable();
        });

        Schema::create('oauth_access_tokens', function (Blueprint $table) {
            $table->string('id', 100)->primary();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->unsignedBigInteger('client_id');
            $table->string('name')->nullable();
            $table->text('scopes')->nullable();
            $table->boolean('revoked');
            $table->timestamps();
            $table->dateTime('expires_at')->nullable();
        });

        Schema::create('oauth_refresh_tokens', function (Blueprint $table) {
            $table->string('id', 100)->primary();
            $table->string('access_token_id', 100)->index();
            $table->boolean('revoked');
            $table->dateTime('expires_at')->nullable();
        });

        Schema::create('oauth_clients', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('name');
            $table->string('secret', 100)->nullable();
            $table->string('provider')->nullable();
            $table->text('redirect');
            $table->boolean('personal_access_client');
            $table->boolean('password_client');
            $table->boolean('revoked');
            $table->timestamps();
        });

        Schema::create('oauth_personal_access_clients', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('client_id');
            $table->timestamps();
        });
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
    
    // FEATURE TESTS FOR API ENDPOINTS
    
    public function test_api_get_all_conversations()
    {
        $user = $this->createTestUser('Alice');
        $otherUser = $this->createTestUser('Bob');
        
        Passport::actingAs($user);
        
        // Create a conversation
        \App\Modules\Management\Message\Models\ConversationModel::create([
            'creator' => $user->id,
            'participant' => $otherUser->id,
            'last_updated' => now(),
        ]);
        
        $response = $this->getJson('/api/conversations');
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'creator',
                            'participant',
                            'unread_count',
                            'last_message',
                            'last_updated'
                        ]
                    ]
                ]);
    }
    
    public function test_api_start_conversation()
    {
        $user = $this->createTestUser('Alice');
        $otherUser = $this->createTestUser('Bob');
        
        Passport::actingAs($user);
        
        $response = $this->postJson('/api/conversations', [
            'participant_id' => $otherUser->id
        ]);
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'creator',
                        'participant',
                        'last_updated'
                    ]
                ]);
                
        $this->assertDatabaseHas('conversation', [
            'creator' => $user->id,
            'participant' => $otherUser->id
        ]);
    }
    
    public function test_api_get_conversation_messages()
    {
        $user = $this->createTestUser('Alice');
        $otherUser = $this->createTestUser('Bob');
        
        $conversation = \App\Modules\Management\Message\Models\ConversationModel::create([
            'creator' => $user->id,
            'participant' => $otherUser->id,
            'last_updated' => now(),
        ]);
        
        // Create test message
        \App\Modules\Management\Message\Models\Model::create([
            'conversation_id' => $conversation->id,
            'sender' => $user->id,
            'receiver' => $otherUser->id,
            'text' => 'Test message',
            'date_time' => now(),
        ]);
        
        Passport::actingAs($user);
        
        $response = $this->getJson("/api/conversations/{$conversation->id}/messages");
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'conversation_id',
                            'sender',
                            'receiver',
                            'text',
                            'type'
                        ]
                    ]
                ]);
    }
    
    public function test_api_send_message()
    {
        Event::fake([MessageSent::class]);
        
        $user = $this->createTestUser('Alice');
        $otherUser = $this->createTestUser('Bob');
        
        $conversation = \App\Modules\Management\Message\Models\ConversationModel::create([
            'creator' => $user->id,
            'participant' => $otherUser->id,
            'last_updated' => now(),
        ]);
        
        Passport::actingAs($user);
        
        $response = $this->postJson('/api/messages', [
            'conversation_id' => $conversation->id,
            'text' => 'Hello Bob!'
        ]);
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'conversation_id',
                        'sender',
                        'text'
                    ]
                ]);
                
        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'sender' => $user->id,
            'text' => 'Hello Bob!'
        ]);
        
        Event::assertDispatched(MessageSent::class);
    }
    
    public function test_api_mark_messages_as_read()
    {
        $user = $this->createTestUser('Alice');
        $otherUser = $this->createTestUser('Bob');
        
        $conversation = \App\Modules\Management\Message\Models\ConversationModel::create([
            'creator' => $user->id,
            'participant' => $otherUser->id,
            'last_updated' => now(),
        ]);
        
        $message = \App\Modules\Management\Message\Models\Model::create([
            'conversation_id' => $conversation->id,
            'sender' => $otherUser->id,
            'receiver' => $user->id,
            'text' => 'Unread message',
            'date_time' => now(),
        ]);
        
        Passport::actingAs($user);
        
        $response = $this->postJson("/api/conversations/{$conversation->id}/mark-read");
        
        $response->assertStatus(200);
        
        $this->assertDatabaseHas('message_read_status', [
            'message_id' => $message->id,
            'user_id' => $user->id
        ]);
    }
    
    // GROUP CHAT API TESTS
    
    public function test_api_create_group_chat()
    {
        $user = $this->createTestUser('Alice');
        $userB = $this->createTestUser('Bob');
        $userC = $this->createTestUser('Charlie');
        
        Passport::actingAs($user);
        
        $response = $this->postJson('/api/groups', [
            'group_name' => 'Test Group',
            'participant_ids' => [$userB->id, $userC->id]
        ]);
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'creator',
                        'is_group',
                        'group_name',
                        'group_participants'
                    ]
                ]);
                
        $this->assertDatabaseHas('conversation', [
            'creator' => $user->id,
            'is_group' => true,
            'group_name' => 'Test Group'
        ]);
    }
    
    public function test_api_get_group_members()
    {
        $user = $this->createTestUser('Alice');
        $userB = $this->createTestUser('Bob');
        
        $conversation = \App\Modules\Management\Message\Models\ConversationModel::create([
            'creator' => $user->id,
            'is_group' => true,
            'group_name' => 'Test Group',
            'group_participants' => [$user->id, $userB->id],
            'last_updated' => now(),
        ]);
        
        Passport::actingAs($user);
        
        $response = $this->getJson("/api/groups/{$conversation->id}/members");
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'email'
                        ]
                    ]
                ]);
    }
    
    public function test_api_add_group_members()
    {
        $user = $this->createTestUser('Alice');
        $userB = $this->createTestUser('Bob');
        $userC = $this->createTestUser('Charlie');
        
        $conversation = \App\Modules\Management\Message\Models\ConversationModel::create([
            'creator' => $user->id,
            'is_group' => true,
            'group_name' => 'Test Group',
            'group_participants' => [$user->id],
            'last_updated' => now(),
        ]);
        
        Passport::actingAs($user);
        
        $response = $this->postJson("/api/groups/{$conversation->id}/members", [
            'user_ids' => [$userB->id, $userC->id]
        ]);
        
        $response->assertStatus(200);
        
        $conversation->refresh();
        $this->assertContains($userB->id, $conversation->group_participants);
        $this->assertContains($userC->id, $conversation->group_participants);
    }
    
    public function test_api_remove_group_member()
    {
        $user = $this->createTestUser('Alice');
        $userB = $this->createTestUser('Bob');
        
        $conversation = \App\Modules\Management\Message\Models\ConversationModel::create([
            'creator' => $user->id,
            'is_group' => true,
            'group_name' => 'Test Group',
            'group_participants' => [$user->id, $userB->id],
            'last_updated' => now(),
        ]);
        
        Passport::actingAs($user);
        
        $response = $this->deleteJson("/api/groups/{$conversation->id}/members/{$userB->id}");
        
        $response->assertStatus(200);
        
        $conversation->refresh();
        $this->assertNotContains($userB->id, $conversation->group_participants);
    }
    
    public function test_api_update_group()
    {
        $user = $this->createTestUser('Alice');
        
        $conversation = \App\Modules\Management\Message\Models\ConversationModel::create([
            'creator' => $user->id,
            'is_group' => true,
            'group_name' => 'Old Name',
            'group_participants' => [$user->id],
            'last_updated' => now(),
        ]);
        
        Passport::actingAs($user);
        
        $response = $this->putJson("/api/groups/{$conversation->id}", [
            'group_name' => 'New Group Name'
        ]);
        
        $response->assertStatus(200);
        
        $this->assertDatabaseHas('conversation', [
            'id' => $conversation->id,
            'group_name' => 'New Group Name'
        ]);
    }
    
    public function test_api_delete_group()
    {
        $user = $this->createTestUser('Alice');
        
        $conversation = \App\Modules\Management\Message\Models\ConversationModel::create([
            'creator' => $user->id,
            'is_group' => true,
            'group_name' => 'Test Group',
            'group_participants' => [$user->id],
            'last_updated' => now(),
        ]);
        
        Passport::actingAs($user);
        
        $response = $this->deleteJson("/api/groups/{$conversation->id}");
        
        $response->assertStatus(200);
        
        $this->assertSoftDeleted('conversation', [
            'id' => $conversation->id
        ]);
    }
    
    public function test_api_get_available_users()
    {
        $user = $this->createTestUser('Alice');
        $userB = $this->createTestUser('Bob');
        $userC = $this->createTestUser('Charlie');
        
        $conversation = \App\Modules\Management\Message\Models\ConversationModel::create([
            'creator' => $user->id,
            'is_group' => true,
            'group_name' => 'Test Group',
            'group_participants' => [$user->id, $userB->id],
            'last_updated' => now(),
        ]);
        
        Passport::actingAs($user);
        
        $response = $this->getJson("/api/groups/{$conversation->id}/available-users");
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'email'
                        ]
                    ]
                ]);
    }
    
    // AUTHENTICATION & AUTHORIZATION TESTS
    
    public function test_api_requires_authentication()
    {
        $response = $this->getJson('/api/conversations');
        $response->assertStatus(401);
    }
    
    public function test_api_conversation_access_control()
    {
        $user = $this->createTestUser('Alice');
        $otherUser = $this->createTestUser('Bob');
        $unauthorizedUser = $this->createTestUser('Charlie');
        
        $conversation = \App\Modules\Management\Message\Models\ConversationModel::create([
            'creator' => $user->id,
            'participant' => $otherUser->id,
            'last_updated' => now(),
        ]);
        
        Passport::actingAs($unauthorizedUser);
        
        // Unauthorized user should not be able to access conversation messages
        $response = $this->getJson("/api/conversations/{$conversation->id}/messages");
        $response->assertStatus(403);
    }
    
    // VALIDATION TESTS
    
    public function test_api_send_message_validation()
    {
        $user = $this->createTestUser('Alice');
        Passport::actingAs($user);
        
        // Missing required fields
        $response = $this->postJson('/api/messages', []);
        $response->assertStatus(422);
        
        // Missing text
        $response = $this->postJson('/api/messages', [
            'conversation_id' => 1
        ]);
        $response->assertStatus(422);
        
        // Missing conversation_id
        $response = $this->postJson('/api/messages', [
            'text' => 'Hello'
        ]);
        $response->assertStatus(422);
    }
    
    public function test_api_create_group_validation()
    {
        $user = $this->createTestUser('Alice');
        Passport::actingAs($user);
        
        // Missing group_name
        $response = $this->postJson('/api/groups', [
            'participant_ids' => [1, 2]
        ]);
        $response->assertStatus(422);
        
        // Missing participant_ids
        $response = $this->postJson('/api/groups', [
            'group_name' => 'Test Group'
        ]);
        $response->assertStatus(422);
    }
    
    // EDGE CASES & ERROR HANDLING
    
    public function test_api_nonexistent_conversation()
    {
        $user = $this->createTestUser('Alice');
        Passport::actingAs($user);
        
        $response = $this->getJson('/api/conversations/999/messages');
        $response->assertStatus(404);
    }
    
    public function test_api_duplicate_conversation_prevention()
    {
        $user = $this->createTestUser('Alice');
        $otherUser = $this->createTestUser('Bob');
        
        // Create existing conversation
        \App\Modules\Management\Message\Models\ConversationModel::create([
            'creator' => $user->id,
            'participant' => $otherUser->id,
            'last_updated' => now(),
        ]);
        
        Passport::actingAs($user);
        
        // Try to create duplicate
        $response = $this->postJson('/api/conversations', [
            'participant_id' => $otherUser->id
        ]);
        
        $response->assertStatus(404); // Should return error
    }

    // protected function assertResponseOk($response)
    // {
    //     $status = method_exists($response, 'getStatusCode') ? $response->getStatusCode() : null;
    //     if ($status !== 200) {
    //         // print full payload for debugging
    //         if (method_exists($response, 'getContent')) {
    //             fwrite(STDOUT, "\nRESPONSE CONTENT: " . $response->getContent() . "\n");
    //         } else {
    //             fwrite(STDOUT, "\nRESPONSE OBJECT: " . print_r($response, true) . "\n");
    //         }
    //     }
    //     $this->assertEquals(200, $status);
    // }

    // public function test_can_start_conversation_and_fetch_conversations()
    // {
    //     $userA = User::factory()->create();
    //     $userB = User::factory()->create();
    //     $this->actingAs($userA, 'web');

    //     // Directly call action to start conversation - ensure the request resolves the test user
    //     $req = new \Illuminate\Http\Request(['participant_id' => $userB->id]);
    //     $req->setUserResolver(function () use ($userA) {
    //         return $userA;
    //     });
    //     $response = StartConversation::execute($req);
    //     $this->assertResponseOk($response);
    //     $payload = $response->getData(true);
    //     $this->assertArrayHasKey('data', $payload);
    // }

    // public function test_can_send_message_and_fetch_messages()
    // {
    //     $userA = User::factory()->create();
    //     $userB = User::factory()->create();

    //     // Create conversation record directly
    //     $conversation = \App\Modules\Management\Message\Models\ConversationModel::create([
    //         'creator' => $userA->id,
    //         'participant' => $userB->id,
    //         'last_updated' => now(),
    //     ]);

    //     $this->actingAs($userA, 'web');

    //     // send message via action - ensure request user resolver returns the acting user
    //     $sendReq = new \Illuminate\Http\Request([
    //         'conversation_id' => $conversation->id,
    //         'text' => 'Hello there'
    //     ]);
    //     $sendReq->setUserResolver(function () use ($userA) {
    //         return $userA;
    //     });
    //     $sendResp = SendMessageAction::execute($sendReq);
    //     $this->assertResponseOk($sendResp);
    //     $sendPayload = $sendResp->getData(true);
    //     $this->assertArrayHasKey('data', $sendPayload);

    //     // fetch messages via action class GetConversationMessages
    //     // ensure auth user present for get action as well
    //     request()->setUserResolver(function () use ($userA) {
    //         return $userA;
    //     });
    //     $getResp = \App\Modules\Management\Message\Actions\GetConversationMessages::execute($conversation->id);
    //     $this->assertResponseOk($getResp);
    //     $this->assertNotEmpty($getResp->getData(true)['data']);
    // }

    // public function test_mark_messages_as_read()
    // {
    //     $userA = User::factory()->create();
    //     $userB = User::factory()->create();

    //     $conversation = \App\Modules\Management\Message\Models\ConversationModel::create([
    //         'creator' => $userA->id,
    //         'participant' => $userB->id,
    //         'last_updated' => now(),
    //     ]);

    //     // create a message from userA to userB
    //     $message = \App\Modules\Management\Message\Models\Model::create([
    //         'conversation_id' => $conversation->id,
    //         'sender' => $userA->id,
    //         'receiver' => $userB->id,
    //         'text' => 'Test unread',
    //         'date_time' => now(),
    //     ]);

    //     // act as userB and mark as read
    //     $this->actingAs($userB, 'web');
    //     \Illuminate\Support\Facades\Auth::login($userB);

    //     // ensure request user for any request() usage
    //     request()->setUserResolver(function () use ($userB) {
    //         return $userB;
    //     });

    //     $resp = MarkMessagesAsReadAction::execute($conversation->id);
    //     $this->assertResponseOk($resp);

    //     // Check database has an entry in message_read_status
    //     $this->assertDatabaseHas('message_read_status', [
    //         'message_id' => $message->id,
    //         'user_id' => $userB->id,
    //     ]);
    // }
}
