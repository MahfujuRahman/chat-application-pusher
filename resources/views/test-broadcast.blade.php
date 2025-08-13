<!DOCTYPE html>
<html>
<head>
    <title>Message Broadcasting Test</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #1a1a1a; color: #fff; }
        .container { max-width: 600px; margin: 0 auto; }
        .form-group { margin: 15px 0; }
        label { display: block; margin-bottom: 5px; }
        input, button { padding: 10px; margin: 5px; border: 1px solid #444; background: #333; color: white; border-radius: 4px; }
        button { background: #007bff; cursor: pointer; }
        .log { background: #2a2a2a; padding: 15px; margin: 10px 0; border-radius: 5px; font-family: monospace; max-height: 300px; overflow-y: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Message Broadcasting Test</h1>
        
        <div class="form-group">
            <label>Sender User ID:</label>
            <input type="number" id="senderId" value="1">
        </div>
        
        <div class="form-group">
            <label>Receiver User ID:</label>
            <input type="number" id="receiverId" value="6">
        </div>
        
        <div class="form-group">
            <label>Conversation ID:</label>
            <input type="number" id="conversationId" value="2">
        </div>
        
        <div class="form-group">
            <label>Message Text:</label>
            <input type="text" id="messageText" value="Test broadcast message">
        </div>
        
        <button onclick="triggerBroadcast()">🚀 Trigger Broadcast</button>
        <button onclick="clearLog()">🧹 Clear Log</button>
        
        <div id="log" class="log"></div>
    </div>

    <script>
        function log(message, type = 'info') {
            const timestamp = new Date().toLocaleTimeString();
            const logDiv = document.getElementById('log');
            const color = type === 'error' ? '#f44336' : type === 'success' ? '#4CAF50' : '#2196F3';
            logDiv.innerHTML += `<div style="color: ${color};">[${timestamp}] ${message}</div>`;
            logDiv.scrollTop = logDiv.scrollHeight;
        }
        
        function clearLog() {
            document.getElementById('log').innerHTML = '';
        }
        
        async function triggerBroadcast() {
            const senderId = document.getElementById('senderId').value;
            const receiverId = document.getElementById('receiverId').value;
            const conversationId = document.getElementById('conversationId').value;
            const messageText = document.getElementById('messageText').value;
            
            if (!senderId || !receiverId || !conversationId || !messageText) {
                log('❌ Please fill all fields', 'error');
                return;
            }
            
            log(`🚀 Triggering broadcast from User ${senderId} to User ${receiverId}`);
            
            try {
                const response = await fetch('/api/test-broadcast', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        sender_id: parseInt(senderId),
                        receiver_id: parseInt(receiverId),
                        conversation_id: parseInt(conversationId),
                        text: messageText
                    })
                });
                
                const result = await response.json();
                
                if (response.ok) {
                    log(`✅ Broadcast triggered successfully!`, 'success');
                    log(`📡 Channel: private-chat.${receiverId}`, 'success');
                    log(`📨 Check User ${receiverId}'s browser console for the event`, 'success');
                } else {
                    log(`❌ Error: ${result.message || 'Unknown error'}`, 'error');
                }
            } catch (error) {
                log(`❌ Network error: ${error.message}`, 'error');
            }
        }
    </script>
</body>
</html>
