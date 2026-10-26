# Deployment notes — Shani CMS

- **App location (private):** `~/shani-cms`  (code, settings, uploads — not web-reachable)
- **Web root:** `~/domains/gipsypockets.store/public_html` is a symlink to `~/shani-cms/public`
- **Original placeholder** saved at `~/domains/gipsypockets.store/public_html_backup_orig`
- **Secrets:** live only in `~/shani-cms/.env` (gitignored, never public)
- **PHP:** 8.3  | **DB:** MariaDB 11.8.9 (`u921710875_shanicms`)
- **Sessions/cache:** file driver
- After any `.env` change, run: `php artisan config:clear` then `php artisan config:cache`
- Production rule: APP_ENV=production, APP_DEBUG=false (never true on the live site)

## Scheduler (Phase 8) — one-time cron setup
In hPanel → Advanced → Cron Jobs, add a job running **every minute**:

    cd /home/u921710875/shani-cms && /usr/bin/php artisan schedule:run >> /dev/null 2>&1

This single entry runs: daily coupon auto-archive, daily 90-day click pruning, and the daily
pure-PHP database backup (storage/app/backups, newest 14 kept).

## Roles
Owner account is role **Admin**. Manage people under Settings → Users & Roles. Deactivating a user
blocks their login immediately.
