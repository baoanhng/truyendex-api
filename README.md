# Truyendex API (Laravel)

A Laravel-powered API for Truyendex, providing authentication, user profile, series follow/read lists, comments, discussions, and scheduled jobs syncing data from Mangadex.

> Source code for UI: [TruyenDex UI](https://github.com/zennomi/truyendex).

## Requirements

-   PHP 8.3+
-   Composer 2+
-   MySQL/MariaDB (or a DB supported by Laravel)
-   Redis (optional, for queues/cache/rate limiting)
-   Node.js 18+ (only if you need to build front-end assets for Telescope)

## Quick start

```bash
cp .env.example .env
php artisan key:generate
# Update .env (DB_*, APP_URL, SANCTUM, MAIL, GOOGLE, etc.)
composer install
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

If you use Docker, an example `docker-compose.yml` is included. Adjust env vars and run your preferred workflow.

## Environment variables

Minimum useful settings:

-   APP_NAME, APP_ENV, APP_KEY, APP_URL
-   APP_TIMEZONE, APP_LOCALE (defaults in `config/app.php`)
-   DB_CONNECTION, DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD
-   CACHE_DRIVER, QUEUE_CONNECTION, SESSION_DRIVER
-   MAIL_MAILER and related provider credentials
-   TURNSTILE_SITE_KEY, TURNSTILE_SECRET_KEY (if using Cloudflare Turnstile)
-   GOOGLE_CLIENT_ID, GOOGLE_CLIENT_SECRET, and set `APP_URL` to form the Socialite redirect: `/sso/google/callback`
-   ANALYTICS_PROPERTY_ID and a service account file at `storage/app/analytics/service-account-credentials.json` (for GA4 analytics in scheduled command)
-   FRONTEND URL: set via `APP_URL` and `config('app.frontend_url')` if present in your environment

See `config/services.php`, `config/analytics.php`, and `config/permission.php` for details.

## Authentication

-   Session-based auth with Laravel Sanctum for API routes requiring `auth:sanctum`.
-   Email verification endpoints are included.
-   Social login via Google using Socialite.

Auth routes (`routes/auth.php`):

-   POST `/register`
-   POST `/login`
-   POST `/forgot-password`
-   POST `/reset-password`
-   GET `/verify-email/{id}/{hash}` (auth, signed)
-   POST `/email/verification-notification` (auth)
-   POST `/logout` (auth)
-   GET `/sso/{provider}/redirect` (guest)
-   GET `/sso/{provider}/callback` (guest)

## Public API endpoints

Defined in `routes/api.php`.

Unregistered:

-   GET `/api/comment/list`
-   GET `/api/comment/recent`
-   POST `/api/comment/fetch-reply`
-   GET `/api/series/homepage`
-   GET `/api/discussion/list`
-   GET `/api/discussion/show/{id}-{slug}`

Authenticated (`auth:sanctum`):

-   GET `/api/user` → current user profile

Authenticated + Verified (`verified`):

-   GET `/api/user/read-list` → paginated followed series
-   POST `/api/user/read-list/sync` body: `{ source: string, ids: string[] }`
-   POST `/api/user/change-password` body: `{ current_password, password, password_confirmation }`
-   POST `/api/user/change-name` body: `{ name }` (60-day cooldown)
-   POST `/api/user/change-avatar` form-data: `avatar` (image ≤ 1MB)

Series actions:

-   POST `/api/series/check-info` body: `{ series_uuid }` → `{ followed, comment_count }`
-   POST `/api/series/follow` body: `{ series_uuid }` → toggle follow
-   POST `/api/series/follows` body: `{ series_uuids: uuid[] }` → bulk follow

Comments:

-   POST `/api/comment/store`
-   POST `/api/comment/update`
-   POST `/api/comment/delete`

Discussions:

-   POST `/api/discussion/store`

Note: Some controller methods apply rate limiting or policy checks (Spatie Permission and Laravel Policies). Ensure the authenticated user has required abilities.

## Responses and validation (high-level)

-   Most endpoints return JSON with top-level keys like `user`, `comments`, `discussion`, `followed`, `avatar_url`, etc.
-   Validation rules are enforced via Form Requests or inline `$request->validate()`; errors return 422 JSON by default.
-   Some endpoints may return 400/403/429/500 when applicable.

## Scheduled jobs and console commands

Scheduling is configured in `routes/console.php`.

Scheduled:

-   `cron:mangadex-latest` — every minute, background
    -   Fetch latest Mangadex chapters (vi) and upsert `chapters` and `series`
-   `cron:mangadex-series` — every two minutes, background
    -   Fetch Mangadex series updated since last sync and upsert `series`
-   `telescope:prune` — daily

Artisan console commands (see `app/Console/Commands`):

-   `cron:mangadex-latest`
-   `cron:mangadex-series`
-   `app:rebuild-comment-reply-count`
-   `app:set-user-role {user?} {role?}`
-   `app:update-user-view-count`

Example manual runs:

```bash
php artisan cron:mangadex-latest
php artisan cron:mangadex-series
php artisan app:rebuild-comment-reply-count
php artisan app:set-user-role 1 admin
php artisan app:update-user-view-count
```

Enable scheduler (production):

-   Add to crontab: `* * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1`

## Policies and permissions

-   Uses Spatie Permission. Roles/permissions are seeded via `database/seeders/RoleAndPermissionSeeder.php`.
-   Application Policies exist for comments, discussions, and pages.

## Development

Useful commands:

```bash
php artisan test
php artisan migrate:fresh --seed
php artisan tinker
php artisan telescope:install && php artisan telescope:publish
```

## Notes

-   Image processing via `intervention/image` when changing avatar.
-   HTML sanitization via `mews/purifier`.
-   Some endpoints rely on external services: Mangadex API and a Directus mapping service.

## License

MIT or project-specific; see repository terms.
