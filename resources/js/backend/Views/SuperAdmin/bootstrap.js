import "bootstrap";

import Echo from "laravel-echo";
import Pusher from "pusher-js";

window.Pusher = Pusher;
Pusher.logToConsole = true;

const token = localStorage.getItem("admin_token"); // ← Get your stored token

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
