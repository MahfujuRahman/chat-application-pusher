<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\MessageSent;
use App\Modules\Management\UserManagement\User\Models\Model as User;
use App\Modules\Management\Message\Models\Model as Message;
use Illuminate\Support\Facades\Log;

class TestBroadcastController extends Controller
{
    public function showTestPage()
    {
        return view('test-broadcast');
    }
    
    public function triggerTestBroadcast(Request $request)
    {
        try {
            $request->validate([
                'sender_id' => 'required|integer',
                'receiver_id' => 'required|integer',
                'conversation_id' => 'required|integer',
                'text' => 'required|string'
            ]);
            
            $sender = User::find($request->sender_id);
            $receiver = User::find($request->receiver_id);
            
            if (!$sender) {
                return response()->json(['message' => 'Sender not found'], 404);
            }
            
            if (!$receiver) {
                return response()->json(['message' => 'Receiver not found'], 404);
            }
            
            // Create a test message object
            $message = new Message([
                'id' => rand(1000, 9999), // Random test ID
                'sender' => $request->sender_id,
                'receiver' => $request->receiver_id,
                'text' => $request->text,
                'date_time' => now(),
                'conversation_id' => $request->conversation_id
            ]);
            
            Log::info('🧪 TEST: Triggering manual broadcast', [
                'sender_id' => $request->sender_id,
                'receiver_id' => $request->receiver_id,
                'conversation_id' => $request->conversation_id,
                'text' => $request->text
            ]);
            
            // Fire the event
            event(new MessageSent($message, $sender));
            
            return response()->json([
                'message' => 'Broadcast triggered successfully',
                'data' => [
                    'channel' => "private-chat.{$request->receiver_id}",
                    'event' => 'MessageSent',
                    'sender' => $sender->name,
                    'receiver' => $receiver->name
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('🧪 TEST: Broadcast test failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
