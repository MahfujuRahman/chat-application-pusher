<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\TestCase;
use App\Models\User;
use App\Modules\Management\Message\Actions\StartConversation;
use App\Modules\Management\Message\Actions\SendMessage as SendMessageAction;
use App\Modules\Management\Message\Actions\MarkMessagesAsRead as MarkMessagesAsReadAction;
use Illuminate\Support\Facades\Auth;

class MessageTest extends TestCase
{
    use RefreshDatabase;

    protected function assertResponseOk($response)
    {
        $status = method_exists($response, 'getStatusCode') ? $response->getStatusCode() : null;
        if ($status !== 200) {
            // print full payload for debugging
            if (method_exists($response, 'getContent')) {
                fwrite(STDOUT, "\nRESPONSE CONTENT: " . $response->getContent() . "\n");
            } else {
                fwrite(STDOUT, "\nRESPONSE OBJECT: " . print_r($response, true) . "\n");
            }
        }
        $this->assertEquals(200, $status);
    }

    protected function setUp(): void
    {
        parent::setUp();
    // force tests to use the session (web) guard so auth()->id() uses the test user
    Auth::shouldUse('web');
        // ensure minimal schema exists for in-memory tests
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('remember_token', 100)->nullable();
            $table->timestamps();
        });

        Schema::create('conversation', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('creator')->nullable();
            $table->unsignedBigInteger('participant')->nullable();
            $table->boolean('is_group')->default(false);
            $table->json('group_participants')->nullable();
            $table->timestamp('last_updated')->nullable();
            $table->timestamps();
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('conversation_id');
            $table->unsignedBigInteger('sender');
            $table->unsignedBigInteger('receiver')->nullable();
            $table->text('text')->nullable();
            $table->timestamp('date_time')->nullable();
            $table->timestamps();
        });

        Schema::create('message_read_status', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('message_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function test_can_start_conversation_and_fetch_conversations()
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
    $this->actingAs($userA, 'web');

    // Directly call action to start conversation - ensure the request resolves the test user
    $req = new \Illuminate\Http\Request(['participant_id' => $userB->id]);
    $req->setUserResolver(function () use ($userA) { return $userA; });
    $response = StartConversation::execute($req);
    $this->assertResponseOk($response);
    $payload = $response->getData(true);
    $this->assertArrayHasKey('data', $payload);
    }

    public function test_can_send_message_and_fetch_messages()
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        // Create conversation record directly
        $conversation = \App\Modules\Management\Message\Models\ConversationModel::create([
            'creator' => $userA->id,
            'participant' => $userB->id,
            'last_updated' => now(),
        ]);

        $this->actingAs($userA, 'web');

        // send message via action - ensure request user resolver returns the acting user
        $sendReq = new \Illuminate\Http\Request([
            'conversation_id' => $conversation->id,
            'text' => 'Hello there'
        ]);
        $sendReq->setUserResolver(function () use ($userA) { return $userA; });
        $sendResp = SendMessageAction::execute($sendReq);
    $this->assertResponseOk($sendResp);
    $sendPayload = $sendResp->getData(true);
    $this->assertArrayHasKey('data', $sendPayload);

        // fetch messages via action class GetConversationMessages
    // ensure auth user present for get action as well
    request()->setUserResolver(function () use ($userA) { return $userA; });
    $getResp = \App\Modules\Management\Message\Actions\GetConversationMessages::execute($conversation->id);
    $this->assertResponseOk($getResp);
    $this->assertNotEmpty($getResp->getData(true)['data']);
    }

    public function test_mark_messages_as_read()
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $conversation = \App\Modules\Management\Message\Models\ConversationModel::create([
            'creator' => $userA->id,
            'participant' => $userB->id,
            'last_updated' => now(),
        ]);

        // create a message from userA to userB
        $message = \App\Modules\Management\Message\Models\Model::create([
            'conversation_id' => $conversation->id,
            'sender' => $userA->id,
            'receiver' => $userB->id,
            'text' => 'Test unread',
            'date_time' => now(),
        ]);

    // act as userB and mark as read
    $this->actingAs($userB, 'web');
    \Illuminate\Support\Facades\Auth::login($userB);

    // ensure request user for any request() usage
    request()->setUserResolver(function () use ($userB) { return $userB; });

    $resp = MarkMessagesAsReadAction::execute($conversation->id);
    $this->assertResponseOk($resp);

        // Check database has an entry in message_read_status
        $this->assertDatabaseHas('message_read_status', [
            'message_id' => $message->id,
            'user_id' => $userB->id,
        ]);
    }
}
