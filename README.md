
# Moduler based admin panel boilerplate 

This project is built with Laravel, Vue.js, and Inertia.js, providing a streamlined single-page application experience with support for image manipulation, Excel imports, job queue processing, and authentication.

---

## Table of Contents
- [Moduler based admin panel boilerplate](#moduler-based-admin-panel-boilerplate)
  - [Table of Contents](#table-of-contents)
  - [Requirements](#requirements)
  - [Installation](#installation)
  - [Frontend Setup (Vue.js)](#frontend-setup-vuejs)
  - [Inertia.js Setup](#inertiajs-setup)
  - [Packages and Dependencies](#packages-and-dependencies)
  - [Queue Job Setup](#queue-job-setup)
  - [Usage](#usage)

---

## Requirements
- PHP >= 7.3
- Composer
- Node.js & npm
- MySQL (or other supported database)
- Redis (optional, for enhanced queue management)

---

## Installation

1. **Clone the Repository**:
   ```bash
   git clone <repository-url>
   cd <project-directory>
   ```

2. **Install PHP Dependencies**:
   ```bash
   composer install
   ```

3. **Environment Setup**:
   Copy the example environment file and configure database and other environment variables:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database Migration**:
   Run migrations to set up the database tables:
   ```bash
   php artisan migrate
   ```

---

## Frontend Setup (Vue.js)

1. **Install Laravel UI**:
   ```bash
   composer require laravel/ui
   ```

2. **Generate Vue Scaffolding**:
   ```bash
   php artisan ui vue
   ```

3. **Install JavaScript Dependencies**:
   ```bash
   npm install
   npm run dev
   ```

---

## Inertia.js Setup

1. **Install Inertia Laravel Adapter**:
   # Modular Admin Panel (Chat application)

   This repository is a Laravel-based modular admin panel with a Vue.js + Inertia frontend. It includes features commonly needed for admin dashboards and chat apps: modular `app/Modules` organization, real-time messaging (Pusher/websockets), email (OTP), queues, and frontend SPA pages built with Inertia and Vue.

   ---

   ## Quick Start / Installation

   Follow these steps to run the project locally.

   1. Clone the repository and cd into it:

   ```powershell
   git clone <repository-url>
   cd "d:\Practice work\RazinSofrt"
   ```

   2. Install PHP dependencies:

   ```powershell
   composer install
   ```

   3. Copy env and generate app key (PowerShell):

   ```powershell
   copy .env.example .env
   php artisan key:generate
   ```

   4. Edit `.env` and configure at minimum:

   - `DB_*` (database connection)
   - `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_ENCRYPTION`
   - `BROADCAST_DRIVER` / Pusher credentials (if using real-time features)
   - `QUEUE_CONNECTION=database` (recommended)

   5. Run migrations and optionally seeders:

   ```powershell
   php artisan migrate
   php artisan db:seed
   ```

   6. Install frontend dependencies and build assets:

   ```powershell
   npm install
   npm run dev   # or npm run build for production
   ```

   7. Start the application:

   ```powershell
   php artisan serve
   ```

   Open http://127.0.0.1:8000 in your browser.

   ---

   ## Architecture & Folder Structure (brief)

   Key folders and their purpose:

   - `app/` - Laravel application code.
      - `app/Modules/` - Modular organization: features grouped into modules (controllers, models, views, routes specific to a module).
   - `resources/js/` - Frontend source (Vue components, Inertia pages, stores).
      - `resources/js/frontend` - SPA pages used by Inertia/Vue.
   - `routes/` - Route definitions (`web.php`, `api.php`, etc.).
   - `resources/views/` - Blade templates (Inertia root template, email views under `emails/`).
   - `database/` - Migrations, seeders, and factories.
   - `app/Modules/Mail` - Mailable classes (e.g., `OTPSendMail`).
   - `public/` - Webserver public assets.
   - `config/` - Laravel configuration (mail, queue, broadcasting, etc.).
   - `storage/` - Logs, compiled views, file uploads.

   This modular layout lets new features live in their own module and keeps the core app tidy.

   ---

   ## Technologies & Decisions

   - Backend: Laravel (PHP) — chosen for mature ecosystem, queues, mail, and modular extensibility.
   - Frontend: Vue.js + Inertia.js — provides an SPA-like UX while keeping Laravel routing and server-side rendering for pages.
   - Build tool: Vite — for fast frontend dev builds.
   - Real-time: Pusher / Websockets (project contains scaffolding for broadcasting events used by chat features).
   - Authentication: Laravel Passport (API tokens) and session-based auth for web.
   - Mailing: Laravel Mail (SMTP). Current implementation uses SMTP credentials defined in `.env`. For production reliability, use a transactional provider (SendGrid/Mailgun/Postmark/SES) and queue emails.
   - Queues: Laravel queues (database/redis). OTP and other mail should be queued to avoid blocking requests and to enable retry/failover.

   Design decisions:

   - Modular `app/Modules` structure to keep features isolated and easier to maintain or extract.
   - Prefer queued email sending and background workers for any network I/O heavy tasks.
   - Use Inertia to keep frontend code simple and avoid a full API-first approach while retaining SPA UX.

   ---

   ## Common troubleshooting

   - Mail errors (e.g., Gmail 550 quota): check `storage/logs/laravel.log` for details; consider switching to a transactional provider.
   - Queue jobs not running: ensure `QUEUE_CONNECTION` is set and run `php artisan queue:work`.
   - Assets not loading: run `npm run dev` and ensure `@vite` is included in your app layout.

   ---

   ## Next steps / Recommendations

   - Configure and run worker processes (supervisor/systemd) for production.
   - Switch mail to a transactional provider and enable mail queueing.
   - Add rate-limiting for OTP sending and other high-cost operations.

   ---

   If you want, I can also:

   - Add a short CONTRIBUTING.md explaining module conventions.
   - Add example `.env` snippets for mail, queue, and pusher settings.
   - Search/patch code to ensure all email sends are queued and logged.
