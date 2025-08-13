<?php

require_once __DIR__ . '/vendor/autoload.php';

use Pusher\Pusher;

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "🔍 Pusher Configuration Debug\n";
echo "============================\n\n";

// Check environment variables
echo "Environment Variables:\n";
echo "PUSHER_APP_ID: " . ($_ENV['PUSHER_APP_ID'] ?? 'NOT SET') . "\n";
echo "PUSHER_APP_KEY: " . ($_ENV['PUSHER_APP_KEY'] ?? 'NOT SET') . "\n";
echo "PUSHER_APP_SECRET: " . (isset($_ENV['PUSHER_APP_SECRET']) ? str_repeat('*', strlen($_ENV['PUSHER_APP_SECRET'])) : 'NOT SET') . "\n";
echo "PUSHER_APP_CLUSTER: " . ($_ENV['PUSHER_APP_CLUSTER'] ?? 'NOT SET') . "\n";
echo "BROADCAST_DRIVER: " . ($_ENV['BROADCAST_DRIVER'] ?? 'NOT SET') . "\n\n";

if (!isset($_ENV['PUSHER_APP_ID']) || !isset($_ENV['PUSHER_APP_KEY']) || !isset($_ENV['PUSHER_APP_SECRET'])) {
    echo "❌ Missing Pusher credentials in .env file\n";
    exit(1);
}

try {
    // Test Pusher connection
    echo "Testing Pusher Connection:\n";
    $pusher = new Pusher(
        $_ENV['PUSHER_APP_KEY'],
        $_ENV['PUSHER_APP_SECRET'],
        $_ENV['PUSHER_APP_ID'],
        [
            'cluster' => $_ENV['PUSHER_APP_CLUSTER'],
            'useTLS' => true
        ]
    );

    // Test basic trigger
    $result = $pusher->trigger('test-channel', 'test-event', [
        'message' => 'Hello from debug script',
        'timestamp' => date('Y-m-d H:i:s')
    ]);

    if ($result) {
        echo "✅ Successfully sent test event to Pusher\n";
        echo "Response: " . json_encode($result, JSON_PRETTY_PRINT) . "\n\n";
    } else {
        echo "❌ Failed to send test event\n\n";
    }

    // Test private channel authentication
    echo "Testing Private Channel:\n";
    $privateResult = $pusher->trigger('private-test-channel', 'private-test-event', [
        'message' => 'Private channel test',
        'timestamp' => date('Y-m-d H:i:s')
    ]);

    if ($privateResult) {
        echo "✅ Successfully sent private channel event\n";
        echo "Response: " . json_encode($privateResult, JSON_PRETTY_PRINT) . "\n\n";
    } else {
        echo "❌ Failed to send private channel event\n\n";
    }

    // Get channel info
    echo "Getting channel info:\n";
    try {
        $channelInfo = $pusher->getChannelInfo('presence-test');
        echo "Channel info: " . json_encode($channelInfo, JSON_PRETTY_PRINT) . "\n";
    } catch (Exception $e) {
        echo "Channel info error: " . $e->getMessage() . "\n";
    }

} catch (Exception $e) {
    echo "❌ Pusher Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n🔍 Debug Complete\n";
