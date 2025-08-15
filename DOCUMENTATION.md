# Project Documentation — ChatZone - Chat Application

This file documents the implemented features, how to use them, API documentation, frontend architecture, and known limitations for the project located at the repository root.

## Table of Contents
- Usage Instructions (features)
- Architecture & Folder Structure
- Technologies & Decisions
- API Documentation
- Frontend Guide — key pages & components
- Notes & Limitations
- Where to look in the code

---

## Usage Instructions (features)

Below are the user-facing features and steps for using them from the frontend UI. The chat UI is implemented in the admin views under `resources/js/backend/...`.

1) Start a 1:1 Conversation
   - Open the chat UI (Admin > Messages). Click the “+” button (Start Conversation modal).
   - Select a user and click Start. The frontend calls `POST /api/v1/messages/start-conversation` with JSON { participant_id }.
   - The sidebar refreshes and the conversation appears.

2) Create a Group Chat
   - Click the group icon in the sidebar header (New Group Chat).
   - Provide a group name and select participants, then click Create.
   - Frontend calls `POST /api/v1/messages/create-group-chat` with { name, participant_ids }.

3) Open a Conversation & Read Messages
   - Click a conversation in the sidebar. Frontend calls `GET /api/v1/messages/get-conversation-messages/{id}?page=1&per_page=20`.
   - Messages show in chronological order (oldest at top). The sidebar updates last message and timestamps.

4) Send a Message
   - Type in the chat input and press Enter (or Send). Frontend performs optimistic UI insert and POSTs to `POST /api/v1/messages/send` with { conversation_id, text }.
   - Server broadcasts `MessageSent` event via Echo to recipients.

5) Mark Messages as Read
   - When a conversation is opened, the frontend sets a pending mark-as-read id. After a short grace period (1.5s) or when the user interacts (scroll/click), the frontend calls `POST /api/v1/messages/mark-as-read/{conversationId}` which records read status.

6) Typing Indicator
   - While typing, frontend calls `POST /api/v1/messages/typing` with { conversation_id, is_typing } to broadcast typing status. Receipt shows typing bubble in active chat.

7) Group Members Management
   - Open group members modal from sidebar group button. The frontend calls `GET /api/v1/messages/group-members/{conversationId}` and `GET /api/v1/messages/available-users/{conversationId}`.
   - Adding/removing members uses `POST /api/v1/messages/add-group-members` and `POST /api/v1/messages/remove-group-member`.

---

## Architecture & Folder Structure

This project follows a modular, service-oriented structure on the backend and a component-driven structure on the frontend. The high-level architecture is:

- Backend (Laravel): Modular, thin-controller, action-based services. Each feature in `app/Modules/` is self-contained and includes its own routes, controller, action classes, models and helpers. This keeps business logic testable and separated from HTTP concerns.
- Frontend (Vue): Container/presentational separation. Container components (e.g. `Conversation.vue`) handle data, state, and side-effects (Echo listeners, API calls).
- Real-time layer: Laravel Echo + Pusher (or compatible WebSocket provider) for realtime events. Channels are per-user (`chat.{userId}`) and per-conversation (`conversation.{conversationId}`) to scope events.

Key folders and purpose
- `app/Modules/` — Feature modules (e.g. Management/Message). Each module typically contains `Routes/`, `Controller/`, `Actions/`, `Models/`, and `Helpers/` where applicable.
- `app/Modules/Helpers/` — Reusable helper utilities used across modules (for consistent response formats, shared logic).
- `resources/js/backend/Views/` — Vue frontend for the backend/admin UI. Subfolders follow the admin area (e.g. `SuperAdmin/Management/Message/`).
- `resources/js/` — Frontend application entry points, component libraries and shared utilities.
- `database/migrations`, `database/factories`, `database/seeders` — DB schema, factories and seed data.
- `routes/` — Application-wide route files (API route registration ultimately delegates to module route files).
- `public/` — Public assets served by the application.
- `tests/Feature` and `tests/Unit` — Automated tests (backend feature tests are present for message endpoints).

Data & flow contract (tiny contract)
- Inputs: authenticated API requests (Bearer token / Sanctum cookie) carrying JSON payloads for message operations.
- Outputs: standardized helper responses (`entityResponse`, `messageResponse`) with `{ status, statusCode, data, message }` shapes.
- Error modes: validation errors return `messageResponse` with appropriate statusCode; unauthenticated requests are blocked by `auth:api` middleware.

Edge cases considered
- Empty conversations (no messages) return an empty array.
- Group chats require name and participant IDs for creation; large groups may result in many broadcast events.
- Mark-as-read is delayed on the frontend (1.5s) to avoid false-positive reads when a user briefly opens a conversation.

## Technologies & Decisions

Technologies
- Backend: Laravel 10.x running on PHP 8.x, organized using the `app/Modules/` layout for feature isolation and clearer ownership.
- Real-time: Laravel Echo + Pusher (or compatible WebSocket provider). Broadcast drivers are configurable via `BROADCAST_DRIVER`.
- Auth: Laravel Passport or Sanctum for API token / session authentication. Endpoints use `auth:api` middleware.
- Frontend: Vue 3, Pinia for state management, Inertia.js for server-driven SPA pages, and Vite for bundling and dev server.
- Database & cache: MySQL (or compatible). Redis recommended for queues and cache to improve performance and enable pub/sub for websocket scaling.
- Queues & Jobs: Laravel queues used for broadcasts and background jobs; Laravel Horizon recommended for Redis-backed queue monitoring.

Design decisions & trade-offs (detailed)
- Modular Actions & Thin Controllers
  - Decision: Business rules live in `Actions/*` classes; controllers delegate to actions.
  - Rationale: Improves testability, separates HTTP concerns from domain logic, and allows reuse of actions by CLI commands and jobs.
  - Trade-off: More files and indirection; small/simple endpoints require additional files.

- Standardized Responses
  - Decision: Use `entityResponse` and `messageResponse` helpers to standardize API payloads.
  - Rationale: Frontend code can rely on predictable shapes, simplifying error/display handling.
  - Trade-off: Coupling to helper shapes; changing the contract requires coordinated updates across frontend and backend.

- Real-time Channels and Broadcast Strategy
  - Decision: Use per-user channels (`chat.{userId}`) for general notifications and per-conversation channels (`conversation.{conversationId}`) for typing/presence and conversation-scoped events.
  - Group messages: the backend currently emits `MessageSent` events per recipient to ensure permission checks per user.
  - Rationale: Ensures secure delivery and simple subscription logic on clients.
  - Trade-off: Emits N events for a group of N users. For large groups consider switching to a single conversation channel broadcast or a hybrid approach to reduce event volume.

- Frontend UX choices
  - Optimistic UI: messages are inserted immediately with a temporary id and reconciled once the server responds to reduce perceived latency.
  - Read marking: frontend delays marking a conversation read (~1.5s) to avoid accidental reads when a conversation is briefly opened.
  - Trade-off: Temporary inconsistency between client and backend state; acceptable for improved UX.

- Attachment handling
  - Decision: Attachments/files are intentionally excluded from the initial design to reduce scope and complexity.
  - Recommendation: If needed, add multipart upload endpoints, storage (S3), virus scanning, and file size/type validation.

- Testing
  - Current: backend feature tests exist for message endpoints (`tests/Feature/MessageTest.php`).
  - Recommendation: add frontend unit tests for presentational components and integration tests for Echo event flows.

Operational runbook (developer & ops)
- Local developer quick start
  1. copy `.env.example` -> `.env` and configure DB and broadcast (Pusher) credentials.
  2. composer install; npm install; php artisan key:generate; php artisan migrate --seed; npm run dev; php artisan serve

- Running workers & websockets
  - Development queues: `php artisan queue:work --tries=3`.
  - Production: use Supervisor or systemd to run queue workers persistently, or use Laravel Horizon for Redis with process control and metrics.
  - Self-hosted websockets: if using `beyondcode/laravel-websockets`, start the websocket server and ensure routing/load balancer supports it.

- Debugging
  - Use `php artisan tinker` to dispatch events and confirm listeners.
  - Tail logs and monitor `php artisan queue:failed` for job failures.
  - Use browser devtools to inspect Echo subscriptions and socket traffic.

Deployment notes
- Build & asset handling
  - CI: run `npm ci` and `npm run build` and include built assets in release artifacts or deploy them to a CDN.

- Composer & PHP
  - Run `composer install --no-dev --optimize-autoloader` and `php artisan config:cache`, `php artisan route:cache`, `php artisan view:cache` in the deploy step.

- Database & migrations
  - Run `php artisan migrate --force` on deploy; schedule schema changes in maintenance windows when needed.

- Queues & workers
  - Ensure workers are restarted after deployment so they use the new code (Supervisor restart or Horizon restart).

- Websocket considerations
  - Pusher: no special LB config.
  - Self-hosted: ensure sticky sessions or direct websocket routing; use Redis pub/sub to propagate events between app nodes.

- Secrets & environment
  - Store secrets in environment variables or a secrets manager; never commit `.env` to source control.

- Monitoring & scaling
  - Monitor queue length, failed jobs, broadcast errors, websocket connection counts, and system metrics. Add alerts for abnormal spikes.

Production checklist (minimum)
- HTTPS and secure env
- Redis for queues/cache
- Supervisor/Horizon for queue workers
- CDN or static asset hosting
- Regular DB backups and disaster recovery plan
- Application monitoring and alerting (Sentry/Prometheus/etc.)
---

## API Documentation

Base URL (examples):
- Logged-in API base prefix: `/api/v1/messages`

All endpoints require authentication (middleware `auth:api`). Responses use helper payload formats:
- `entityResponse($data)` => { status, statusCode, data }
- `messageResponse($message, $data)` => { status, statusCode, message, data }

1) GET /api/v1/messages/get-all-conversations
- Description: Returns conversations available to the authenticated user (1:1 and group). Includes unread counts and last message.
- Params: none
- Example response (200):

```json
{
  "status": "success",
  "statusCode": 200,
  "data": [
    {
      "id": 12,
      "creator": 5,
      "is_group": false,
      "group_name": null,
      "participant": { "name": "Alice", "image": null, "is_group": false },
      "unread_count": 2,
      "last_message": "Hello",
      "last_updated": "2025-08-15T12:34:56Z"
    }
  ]
}
```

2) GET /api/v1/messages/get-conversation-messages/{id}?page={page}&per_page={per_page}
- Description: Fetch paginated messages for a conversation.
- Path params: id (conversation id)
- Query params: page (default 1), per_page (default 20)
- Example response (200): entityResponse(array of messages). Each message has `type` set to 'mine' or 'theirs'.

3) POST /api/v1/messages/start-conversation
- Description: Create a 1:1 conversation with a participant.
- Body (JSON): { participant_id: number }
- Response: entityResponse(created conversation object) or messageResponse on validation error.

4) POST /api/v1/messages/create-group-chat
- Description: Create a group chat.
- Body (JSON): { name: string, participant_ids: array<number> }
- Response: messageResponse('Group chat created successfully', conversation)

5) POST /api/v1/messages/send
- Description: Send a message in a conversation.
- Body (JSON): { conversation_id: number, text: string }
- Response: entityResponse(message)
- Notes: For group chats this action broadcasts a `MessageSent` event to each participant (except sender).

6) POST /api/v1/messages/mark-as-read/{conversationId}
- Description: Mark all unread messages for this user in conversation as read.
- Path params: conversationId
- Response: messageResponse('Messages marked as read')

7) POST /api/v1/messages/typing
- Description: Broadcast typing status for a conversation.
- Body (JSON): { conversation_id: number, is_typing: boolean }
- Response: messageResponse('Typing status broadcasted successfully')

8) GET /api/v1/messages/group-members/{conversationId}
- Description: Return group members for a group conversation.

9) GET /api/v1/messages/available-users/{conversationId}
- Description: Return users that may be added to the group.

10) POST /api/v1/messages/add-group-members
- Body: { conversation_id: number, user_ids: array<number> }

11) POST /api/v1/messages/remove-group-member
- Body: { conversation_id: number, user_id: number }

12) PUT /api/v1/messages/conversations/{id}/group
- Description: Update group name.
- Body: { group_name: string }

13) DELETE /api/v1/messages/conversations/{id}/group
- Description: Delete a group conversation.

Authentication notes
- These endpoints use `auth:api` middleware (Passport token-based API auth is available in composer.json). For curl examples include `Authorization: Bearer <token>` header.

Example curl (send message):

```bash
curl -X POST "http://127.0.0.1:8000/api/v1/messages/send" \
  -H "Authorization: Bearer <TOKEN>" \
  -H "Content-Type: application/json" \
  -d '{"conversation_id":123, "text":"Hello from curl"}'
```

---

## Frontend Guide — key pages & components

Location (main chat files): `resources/js/backend/Views/SuperAdmin/Management/Message/`

Top-level component
- `Conversation.vue`
  - Container (data + logic) for the chat UI.
  - Responsibilities: load conversations, open conversation, load messages, send messages (optimistic UI), setup Laravel Echo listeners (`chat.{userId}` personal channel and `conversation.{conversationId}` conversation channels), broadcast typing, mark-as-read flow, group management interactions.
  - Uses `auth_store` from Pinia for `auth_info`.

Presentational components (recent refactor)
- `Sidebar.vue`
  - Renders the list of conversations and unread badges. Emits actions to parent: open modal, create group, load messages, open group members.
- `MessageBubble.vue`
  - Renders a single message. Shows sender name for group messages and relative time.
- `TypingIndicator.vue`
  - Shows typing animation when `isTyping` is true from the parent.

Modals
- `StartConversationModal.vue` — select a user to start 1:1 chat.
- `GroupChatModal.vue` — create a new group.
- `GroupMembersModal.vue` — manage members and rename/delete group.

Event & channel mapping
- Personal channel: `chat.{userId}` — listens for `MessageSent` general notifications for the user.
- Conversation channel: `conversation.{conversationId}` — listens for `UserTyping` events for typing indicator.

Frontend-to-backend mappings
- `loadConversations()` => GET `/messages/get-all-conversations`
- `createConversation()` => POST `/messages/start-conversation` with `{ participant_id }`
- `createGroupChat()` => POST `/messages/create-group-chat` with `{ name, participant_ids }`
- `loadMessages(convo)` => GET `/messages/get-conversation-messages/{id}`
- `sendMessage()` => POST `/messages/send` with `{ conversation_id, text }`
- `checkAndMarkAsRead()` => POST `/messages/mark-as-read/{conversationId}`
- `broadcastTyping()` => POST `/messages/typing` with `{ conversation_id, is_typing }`

UX notes implemented in the frontend
- Optimistic message UI (temporary message id, replaced after server response).
- Pending mark-as-read: when a convo opens with unread_count > 0, frontend delays mark-as-read 1.5s to ensure user is actively viewing before calling API.
- Typing indicator will remain visible until a stop-typing event is received from server (no auto-hide on frontend other than the server event).
---

## Notes & Limitations

- Authentication: API endpoints require authenticated API tokens (`auth:api`) — tests and API calls use `/api/v1/...` prefix.
- Delivery guarantees: Broadcasts depend on Pusher/Echo configuration; ensure broadcast credentials are correct. The backend broadcasts a `MessageSent` event per participant for groups (creates per-recipient events), which may result in many events for large groups.
- Read marking behavior: frontend defers marking messages as read by 1.5s when conversation is opened; this can be adjusted in `Conversation.vue`.
- Attachments: No attachment/file upload support for messages is implemented by default.
- Rate limiting: There is no explicit rate limiter applied to messaging endpoints in the module; consider adding rate limiting to protect against spam.
- Tests: Backend feature tests exist under `tests/Feature/MessageTest.php` and exercise most message endpoints; frontend tests are not present by default.

---

## Where to look in the code

- Routes: `app/Modules/Management/Message/Routes/Route.php`
- Controller (thin): `app/Modules/Management/Message/Controller/Controller.php` — delegates to Action classes
- Actions (business logic): `app/Modules/Management/Message/Actions/*.php` (SendMessage, GetAllConversations, CreateGroupChat, etc.)
- Models: `app/Modules/Management/Message/Models/`
- Frontend chat UI: `resources/js/backend/Views/SuperAdmin/Management/Message/` (Conversation.vue and new presentational components)
- Helpers (response): `app/Modules/Helpers/HelperFiles/ResponseMessage.php`

---
