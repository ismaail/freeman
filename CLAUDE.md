# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

**Setup (first time):**
```bash
composer run setup
```
This installs dependencies, copies `.env`, generates app key, runs migrations, installs npm packages, and builds assets.

**Development:**
```bash
composer run dev
```
Runs concurrently: `php artisan serve`, `queue:listen`, `pail` (log viewer), and `vite` (HMR).

**Testing:**
```bash
composer run test        # Clears config cache, then runs PHPUnit
php artisan test         # Run all tests
php artisan test --filter TestName  # Run a single test
```

**Linting:**
```bash
./vendor/bin/pint        # Fix code style (Laravel Pint)
./vendor/bin/pint --test # Check without fixing
```

**Frontend:**
```bash
npm run build   # Production build
npm run dev     # Dev server with HMR
```

## Architecture

This is a **Laravel 12** application using:
- **Vite + Tailwind CSS 4** for frontend assets
- **SQLite** (default for local dev) — configured in `.env` as `DB_CONNECTION=sqlite`
- **Database-backed** sessions, cache, and queues (all use the `database` driver by default)

### Key architectural notes

- `bootstrap/app.php` uses the fluent builder pattern (Laravel 11+), not the traditional `App\Http\Kernel` class.
- Middleware, exception handling, and routing are configured directly in `bootstrap/app.php`.
- `routes/web.php` handles HTTP routes; `routes/console.php` defines Artisan commands via closures.
- No API routes file exists by default — add `routes/api.php` and register it in `bootstrap/app.php` if needed.

### Test environment

PHPUnit uses an in-memory SQLite database (`:memory:`), synchronous queue, and array cache/session — configured in `phpunit.xml`. No `.env.testing` needed for the test DB setup.
