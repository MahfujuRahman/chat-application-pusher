

# 🚀 ChatZone — Quick Start

Welcome to **ChatZone** — a modular Laravel + Vue (Inertia) admin panel with a built-in real-time chat module (Laravel Echo / Pusher).

📖 **Full documentation:** See [`DOCUMENTATION.md`](./DOCUMENTATION.md) for features, API, architecture, and deployment.

---

## 🛠️ Requirements

- PHP >= 8.x^
- Composer
- Node.js & npm
- MySQL (or other supported database)

---

## ✨ Setup — Step by Step


<strong>1️⃣ Clone the repository</strong>

```powershell
git clone https://github.com/MahfujuRahman/chat-application-pusher.git
cd chat-application-pusher
```


<strong>2️⃣ Install PHP dependencies</strong>

```powershell
composer install
```


<strong>3️⃣ Install Node.js dependencies</strong>

```powershell
npm install
```


<strong>4️⃣ Copy environment file</strong>

```powershell
copy .env.example .env
```


<strong>5️⃣ Generate Laravel app key</strong>

```powershell
php artisan key:generate
```


<strong>6️⃣ Edit <code>.env</code> manually</strong>

Set these values:

```env
APP_NAME="ChatZone"
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=chat_project_management
DB_USERNAME=root
DB_PASSWORD=your_db_password

# Pusher (realtime)
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your-pusher-app-id
PUSHER_APP_KEY=your-pusher-app-key
PUSHER_APP_SECRET=your-pusher-secret
PUSHER_APP_CLUSTER=your-pusher-cluster

# Vite frontend variables (used by Echo client)
VITE_PUSHER_APP_KEY=${PUSHER_APP_KEY}
VITE_PUSHER_APP_CLUSTER=${PUSHER_APP_CLUSTER}
VITE_PUSHER_HOST="${PUSHER_HOST}"
VITE_PUSHER_PORT="${PUSHER_PORT}"
VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"

# Mail (SMTP)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@example.com
MAIL_PASSWORD=your-email-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@example.com
```

<strong>7️⃣ Full reload: wipes DB Run migrations and seed database</strong>

```powershell
npm run reload
```

<strong>8️⃣ Start development servers</strong>

```powershell
npm run dev
php artisan serve
```
✨ And Boom Everything ready to work


## 📧 Email (SMTP) setup

Set these values in your `.env` (do NOT commit secrets):

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@example.com
MAIL_PASSWORD=your-email-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@example.com
MAIL_FROM_NAME="${APP_NAME}"
```

**Notes:**
- For Gmail use an App Password (recommended).
- Test: trigger a password reset or use `php artisan tinker` to send a test mail.

---

## 📡 Pusher (Realtime) setup

Set these values in your `.env` (get them from your Pusher dashboard):

```env
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your-pusher-app-id
PUSHER_APP_KEY=your-pusher-app-key
PUSHER_APP_SECRET=your-pusher-app-secret
PUSHER_APP_CLUSTER=your-pusher-cluster
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https

# Vite frontend variables (used by Echo client)
VITE_PUSHER_APP_KEY=${PUSHER_APP_KEY}
VITE_PUSHER_APP_CLUSTER=${PUSHER_APP_CLUSTER}
VITE_PUSHER_HOST="${PUSHER_HOST}"
VITE_PUSHER_PORT="${PUSHER_PORT}"
VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"
```

**Notes:**
- Create an app at [Pusher dashboard](https://dashboard.pusher.com/) and copy keys into `.env`.
- For local dev without Pusher, use `beyondcode/laravel-websockets` and run `php artisan websockets:serve`.
- Ensure `config/broadcasting.php` is set to use `pusher` and your frontend Echo client uses the Vite `VITE_` keys.

---

## ⚡ Developer quick reference (sanitized)

Copy this template into your local `.env` (do NOT commit secrets). Replace placeholders with your own values.

```env
APP_NAME="ChatZone"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=chat_project_management
DB_USERNAME=root
DB_PASSWORD=            # <-- set your DB password

BROADCAST_DRIVER=pusher
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@example.com
MAIL_PASSWORD=your-email-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@example.com
MAIL_FROM_NAME="${APP_NAME}"

PUSHER_APP_ID=your-pusher-app-id
PUSHER_APP_KEY=your-pusher-app-key
PUSHER_APP_SECRET=your-pusher-secret
PUSHER_APP_CLUSTER=your-pusher-cluster
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https

VITE_PUSHER_APP_KEY=${PUSHER_APP_KEY}
VITE_PUSHER_APP_CLUSTER=${PUSHER_APP_CLUSTER}
VITE_PUSHER_HOST="${PUSHER_HOST}"
VITE_PUSHER_PORT="${PUSHER_PORT}"
VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"
```

---

## 🧭 Where to look next
- [`DOCUMENTATION.md`](./DOCUMENTATION.md) — full setup, API, architecture, deployment
- Frontend entry: `resources/js/backend/Views/SuperAdmin/Management/Message/`

---

## 👤 Contributors

> <br>
> <img src="https://github.com/MahfujuRahman.png" alt="alt text" width="50" height="50" > <br> 
> <a href="https://github.com/MahfujuRahman">S. M. Mahfujur Rahman</a> <br>
> Software Engineer ( Tech Park IT )
> <br>
<br>

