import "bootstrap";

import Echo from "laravel-echo";
import Pusher from "pusher-js";

window.Pusher = Pusher;
Pusher.logToConsole = true;

const token = localStorage.getItem("admin_token"); // ← Get your stored token

console.log("🔧 Initializing Echo with token:", token ? "Present" : "Missing");
console.log("🔧 Pusher Key:", import.meta.env.VITE_PUSHER_APP_KEY);
console.log("🔧 Pusher Cluster:", import.meta.env.VITE_PUSHER_APP_CLUSTER);

window.Echo = new Echo({
  broadcaster: "pusher",
  key: import.meta.env.VITE_PUSHER_APP_KEY,
  cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
  forceTLS: true,
  authEndpoint: "/broadcasting/auth",
  encrypted: true,
  auth: {
    headers: {
      Authorization: `Bearer ${token}`,
    },
  },
});

// Add global error handlers
window.Echo.connector.pusher.connection.bind('error', function(err) {
  console.error('🚨 Pusher connection error:', err);
});

window.Echo.connector.pusher.connection.bind('connected', function() {
  console.log('✅ Pusher connected successfully');
});

window.Echo.connector.pusher.connection.bind('disconnected', function() {
  console.log('⚠️ Pusher disconnected');
});
