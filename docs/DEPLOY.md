# Deployment notes — Shani CMS

- **App location (private):** `~/shani-cms`  (code, settings, uploads — not web-reachable)
- **Web root:** `~/domains/gipsypockets.store/public_html` is a symlink to `~/shani-cms/public`
- **Original placeholder** saved at `~/domains/gipsypockets.store/public_html_backup_orig`
- **Secrets:** live only in `~/shani-cms/.env` (gitignored, never public)
- **PHP:** 8.3  | **DB:** MariaDB 11.8.9 (`u921710875_shanicms`)
- **Sessions/cache:** file driver
- After any `.env` change, run: `php artisan config:clear` then `php artisan config:cache`
- Production rule: APP_ENV=production, APP_DEBUG=false (never true on the live site)
