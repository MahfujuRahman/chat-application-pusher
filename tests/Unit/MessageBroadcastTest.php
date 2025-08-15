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

class MessageBroadcastTest extends TestCase
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

    // BROADCASTING TESTS
    
    public function test_broadcast_typing_action()
    {
        $userA = $this->createTestUser('Alice');
        $userB = $this->createTestUser('Bob');

        $conversation = \App\Modules\Management\Message\Models\ConversationModel::create([
            'creator' => $userA->id,
            'participant' => $userB->id,
            'last_updated' => now(),
        ]);

        $this->actingAs($userA, 'web');

        $req = new \Illuminate\Http\Request([
            'conversation_id' => $conversation->id,
            'typing' => true
        ]);
        $req->setUserResolver(function () use ($userA) { return $userA; });

        $response = \App\Modules\Management\Message\Actions\BroadcastTyping::execute($req);
        
        $this->assertEquals(200, method_exists($response, 'getStatusCode') ? $response->getStatusCode() : null);
        
        $payload = $this->decodeResponse($response);
        $this->assertArrayHasKey('data', $payload);
    }

    public function test_broadcast_typing_to_group()
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
            'typing' => true
        ]);
        $req->setUserResolver(function () use ($userA) { return $userA; });

        $response = \App\Modules\Management\Message\Actions\BroadcastTyping::execute($req);
        
        $this->assertEquals(200, method_exists($response, 'getStatusCode') ? $response->getStatusCode() : null);
        
        $payload = $this->decodeResponse($response);
        $this->assertArrayHasKey('data', $payload);
    }

    public function test_broadcast_typing_validates_conversation_access()
    {
        $userA = $this->createTestUser('Alice');
        $userB = $this->createTestUser('Bob');
        $unauthorizedUser = $this->createTestUser('Charlie');

        $conversation = \App\Modules\Management\Message\Models\ConversationModel::create([
            'creator' => $userA->id,
            'participant' => $userB->id,
            'last_updated' => now(),
        ]);

        $this->actingAs($unauthorizedUser, 'web');

        $req = new \Illuminate\Http\Request([
            'conversation_id' => $conversation->id,
            'typing' => true
        ]);
        $req->setUserResolver(function () use ($unauthorizedUser) { return $unauthorizedUser; });

        $response = \App\Modules\Management\Message\Actions\BroadcastTyping::execute($req);
        
        // Should return error for unauthorized access
        $statusCode = method_exists($response, 'getStatusCode') ? $response->getStatusCode() : null;
        $this->assertNotEquals(200, $statusCode);
    }

    public function test_broadcast_typing_stop()
    {
        $userA = $this->createTestUser('Alice');
        $userB = $this->createTestUser('Bob');

        $conversation = \App\Modules\Management\Message\Models\ConversationModel::create([
            'creator' => $userA->id,
            'participant' => $userB->id,
            'last_updated' => now(),
        ]);

        $this->actingAs($userA, 'web');

        $req = new \Illuminate\Http\Request([
            'conversation_id' => $conversation->id,
            'typing' => false
        ]);
        $req->setUserResolver(function () use ($userA) { return $userA; });

        $response = \App\Modules\Management\Message\Actions\BroadcastTyping::execute($req);
        
        $this->assertEquals(200, method_exists($response, 'getStatusCode') ? $response->getStatusCode() : null);
        
        $payload = $this->decodeResponse($response);
        $this->assertArrayHasKey('data', $payload);
    }

    public function test_broadcast_typing_validates_required_fields()
    {
        $userA = $this->createTestUser('Alice');
        $this->actingAs($userA, 'web');

        // Test missing conversation_id
        $req1 = new \Illuminate\Http\Request(['typing' => true]);
        $req1->setUserResolver(function () use ($userA) { return $userA; });
        
        $response1 = \App\Modules\Management\Message\Actions\BroadcastTyping::execute($req1);
        $statusCode1 = method_exists($response1, 'getStatusCode') ? $response1->getStatusCode() : null;
        $this->assertNotEquals(200, $statusCode1);

        // Test missing typing field
        $req2 = new \Illuminate\Http\Request(['conversation_id' => 1]);
        $req2->setUserResolver(function () use ($userA) { return $userA; });
        
        $response2 = \App\Modules\Management\Message\Actions\BroadcastTyping::execute($req2);
        $statusCode2 = method_exists($response2, 'getStatusCode') ? $response2->getStatusCode() : null;
        $this->assertNotEquals(200, $statusCode2);
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
}
