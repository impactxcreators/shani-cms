# Build Log — Shani CMS

A running record of what has been built and verified, phase by phase.

## Phase 0 — Foundation  ✅  (2026-10-26)

**Built**
- Laravel 13.38 installed in a private folder outside the web root: `~/shani-cms`
- PHP 8.3.36, Composer 2.9.8, MariaDB 11.8.9 (all confirmed on the server)
- Database connected: `u921710875_shanicms`
- Filament v5.10 admin panel installed
- Admin panel branded "Shani CMS", secret path `/admin8869` (set via FILAMENT_PATH in .env)
- Navigation groups created: Content, Stores, Products, Editorial, Tools, Settings
- One admin user: impactxcreators@gmail.com (temporary password — change on first login)
- Domain `gipsypockets.store` pointed at the app's `public` folder (old placeholder saved at
  `~/domains/gipsypockets.store/public_html_backup_orig`)
- Production caches built (config/route/view); file-based sessions and cache

**Verified**
- Homepage https://gipsypockets.store/ returns HTTP 200
- Admin login https://gipsypockets.store/admin8869 loads (redirects to login when logged out)
- storage/logs/laravel.log clean after live visits

**Checkpoint for the owner:** open the admin URL, log in, see an empty panel.

**Left for next time:** Phase 1 — Settings, Media, Pages, Redirects, Maintenance mode.

## Phase 1 — Settings, Media, Pages, Redirects, Maintenance  ✅  (2026-10-26)

**Built**
- `settings` table + `Setting` model (cached key/value) and a **Site Settings** admin page
  (Settings group): site identity, coming-soon/maintenance toggle + message, affiliate disclosure
- Media library installed (spatie/laravel-medialibrary 11) — table + storage link ready for uploads
- **Pages** admin resource (Content group): title, auto slug, rich-text body, status
  (draft/published/archived), scheduled publish date, header/footer menu flags, SEO section.
  Soft deletes with restore.
- Public rendering: published pages show at `/{slug}`; drafts return 404
- **Redirects** admin resource (Tools group) + middleware: 301/302/410, hit counter, last-used
- **Coming soon / maintenance mode**: when on, guests get a styled holding page (HTTP 503);
  logged-in admins always see the real site. Currently ON while we build.
- Public layout + styled coming-soon page (self-contained CSS, light/dark, mobile-friendly)
- Spatie slug helper for clean URLs

**Verified**
- 5 automated smoke tests pass (coming-soon for guests, all admin screens load, published page
  renders, draft hidden 404, redirect works) — run with `php ./vendor/phpunit/phpunit/phpunit`
- Live: home 503 coming-soon, admin login 200, log clean
- Sample data seeded: About Us, Contact, Privacy Policy, Terms pages + a /home→/ redirect

**Safety fix:** added a guard in tests/TestCase.php so tests refuse to run unless the DB is
sqlite :memory: — they can never touch the live MySQL. Always `php artisan config:clear` before
testing and `php artisan optimize` after.

**Left for next time:** Phase 2 — Stores, Categories, Coupons, Deals + outbound /go/ click tracking.

## Phase 2 — Stores, Categories, Coupons, Deals  ✅  (2026-10-26)

**Built**
- Tables: categories (nested via parent_id), stores (+ category_store pivot), coupons, deals, click_events
- Models with relationships + `active()` scopes (hide expired/not-yet-started automatically)
- Admin resources (all in the **Stores** menu group): All Stores, Categories, Coupons, Deals — with
  searchable store pickers, discount-type logic, verified/exclusive flags, soft deletes, status filters
- Public pages: `/store/{slug}` (coupons + deals), `/coupons`, `/deals` with styled cards
- **Coupon reveal**: "Show code" reveals the code and opens the retailer via the tracked link
- **Affiliate click tracking**: `/go/{coupon|deal|store}/{id}` logs a click_event (IP hashed),
  increments coupon used_count, falls back to the store's affiliate/website URL, then 302s with
  rel="sponsored nofollow"

**Verified**
- 11 automated tests pass (admin screens, store page hides expired coupons, /go logs + redirects +
  increments, affiliate fallback, listings render)
- End-to-end: /go 302 → Nike affiliate URL, click logged
- Sample data: Nike store, 2 coupons (incl. SPRING20), 1 deal, Fashion category

**Note:** while "coming soon" is ON, the public commerce pages and /go are hidden from guests
(admins bypass). They go live the moment you turn maintenance off.

**Left:** Phase 3 — Products & roundup "Best X" list builder.

## Phase 3 — Products & Roundup ("Best X") lists  ✅  (2026-10-26)

**Built**
- Tables/models: product_categories (nested), products (highlights JSON, rating, price, affiliate),
  product_lists (roundup), product_list_items (rank, badge, custom title/blurb, editor note)
- Admin (Products menu): Products, Product Categories, and a **Roundup Lists** builder with a
  drag-to-reorder product repeater, per-item badges/blurbs, an FAQ builder, related-roundup picker,
  intro/outro rich text, SEO
- Public: `/best` index; roundup pages resolve at `/{slug}` (e.g. /best-laptops) with hero, H1,
  "Updated" label, jump-to index, ranked cards (rank #, badge, rating, highlights behind "Show more"),
  product "Check price" tracked via `/go/product/{id}`, FAQ accordion, related roundups, disclosure
- Added `product` type to the `/go` click tracker

**Verified**
- 16 automated tests pass (admin screens, roundup renders ranked items, draft hidden 404,
  /best index, product click tracking)
- All public pages return 200; log clean
- Fixed a stale route-cache bug and a catch-all regex that wrongly blocked slugs starting with a
  reserved word (e.g. "best-laptops")
- Sample data: Laptops category, 2 products, "Best Laptops for 2026" roundup

**Left:** Phase 4 — Blog (posts, categories, authors, comments moderation) + handpicks.
