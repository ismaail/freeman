# Freeman

**Freeman** is an open-source, self-hosted, web-based REST API client — a lightweight [Postman](https://www.postman.com/) alternative you can run on your own server.

No desktop install required. No data leaves your network. Your team accesses it from any browser.

![Freeman screenshot](docs/screenshot.png)

---

## Features

- **Organised collections** — group requests into folders, just like Postman
- **Environment variables** — define `{{BASE_URL}}` once, reuse everywhere
- **Request history** — every executed request is logged automatically
- **Import / Export** — full Postman Collection v2.1 compatibility
- **Auth helpers** — Bearer token, Basic auth, API Key support built-in
- **Team accounts** — the super admin creates user accounts; no self-registration
- **CORS-free** — all requests are proxied server-side via Guzzle

---

## Requirements

| Requirement | Minimum version |
|---|---|
| PHP | 8.2 |
| Composer | 2.x |
| SQLite extension | bundled with PHP |

> No Node.js, no npm, no build step needed. Tailwind and Alpine.js are loaded from CDN.

---

## Installation

### 1. Clone the repository

```bash
git clone https://github.com/your-org/freeman.git
cd freeman
```

### 2. Install PHP dependencies

```bash
composer install --no-dev --optimize-autoloader
```

### 3. Run the install wizard

```bash
php artisan freeman:install
```

The wizard will:
- Copy `.env.example` to `.env` (if `.env` doesn't already exist)
- Generate a secure `APP_KEY`
- Create the SQLite database file
- Run all database migrations
- Prompt you to create the super admin username and password

### 4. Set your app URL

Open `.env` and update:

```ini
APP_URL=https://your-domain.com
```

### 5. Start the server

**Development (built-in PHP server):**

```bash
php artisan serve
```

**Production (recommended: nginx + php-fpm)**

Point your web server's document root at the `public/` directory. Ensure `APP_ENV=production` and `APP_DEBUG=false` in `.env`.

---

## Updating

```bash
git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:clear
php artisan cache:clear
```

---

## Configuration

All configuration lives in `.env`. Key settings:

| Key | Description | Default |
|---|---|---|
| `APP_NAME` | Displayed in the browser tab | `Freeman` |
| `APP_URL` | Full URL where Freeman is hosted | `http://localhost:8000` |
| `APP_ENV` | `production` for live deployments | `production` |
| `APP_DEBUG` | Set to `false` in production | `false` |
| `SESSION_LIFETIME` | Session timeout in minutes | `120` |

---

## Creating users

Only the super admin can create user accounts. Log in with your super admin credentials and navigate to **Admin → Users**.

New users are prompted to change their password on first login.

---

## License

MIT — free to use, modify, and self-host.
