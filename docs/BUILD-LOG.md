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

## Phase 4 — Blog & Handpicks  ✅  (2026-10-26)

**Built**
- Tables/models: authors, blog_categories, posts (+ blog_category_post pivot), comments
  (polymorphic, threaded, moderation statuses), handpicks
- Admin (Editorial menu): Blog Posts (rich text, author, categories, tags, SEO, scheduling),
  Blog Categories, Authors, Handpicks, and a **Comments moderation** screen with a pending-count
  badge, one-click Approve / Mark spam, bulk approve, and a default "pending" filter
- Public: /blog, /blog/{slug} (post + approved comments + comment form), /blog/category/{slug},
  /blog/author/{slug}
- Comment submission: stored as **pending** (never shown until approved), spam **honeypot**,
  **rate limited** (6/min), hashed IP

**Verified**
- 22 automated tests pass (editorial screens, blog renders, draft hidden, only approved comments
  show, visitor comment saved as pending, honeypot blocks spam)
- Live home 503 / admin 200, log clean
- Sample: author "Shani", "Guides" category, a published post, a handpick

**Fixes:** a test helper named post() clashed with Laravel's HTTP helper (fatal) — renamed; and a
one-line `@if@section@endif` Blade pattern miscompiled — switched to the safe multi-line form
(also hardened the roundup view).

**Left:** Phase 5 — Homepage builder (banners, drag-reorder home blocks, adverts).

## Phase 5 — Homepage builder  ✅  (2026-10-26)

**Built**
- Tables/models: home_banners, home_blocks (typed + JSON config), adverts (impressions/clicks)
- Admin (Content menu): Home Banners, Home Blocks, Adverts — all drag-to-reorder
- Homepage assembled from active home_blocks in order; block types: hero (banners), coupon_grid,
  featured_logos, blog_feed, trending (categories), handpicked, rich_text
- Graceful default homepage when no blocks are configured
- Advert click tracking at /ad/{id} (increments clicks, 302 redirect)

**Verified**
- 27 automated tests pass (home admin screens, homepage renders blocks, inactive block hidden,
  default homepage fallback, advert click tracking)
- Live home 503 / admin 200, log clean
- Sample: a hero banner, 3 home blocks, a top advert

**Left:** Phase 6 — SEO layer (sitemap.xml, robots.txt, rss.xml, JSON-LD, meta tags, canonical).

## Phase 6 — SEO layer  ✅  (2026-10-26)

**Built**
- `/sitemap.xml` (generated from published pages, roundups, stores, posts with lastmod)
- `/sitemap/html` human-readable sitemap, `/robots.txt` (Disallow all during coming-soon;
  Allow + Sitemap line when live), `/rss.xml` (latest posts)
- Site-wide JSON-LD: Organization + WebSite (with SearchAction)
- Per-page JSON-LD: ItemList + FAQPage on roundups, Article on posts
- Canonical tag, OpenGraph + Twitter card meta, per-page noindex support
- **Auto-301**: changing a Page/Store/Post/Roundup slug auto-creates a 301 from the old URL
- SEO endpoints respond even during coming-soon
- Deferred extras recorded in docs/BACKLOG.md (OG image generation, internal-link suggester,
  broken-link checker, sitemap index split)

**Verified**
- 35 automated tests pass (sitemap lists content, robots both states, rss, Organization & ItemList
  JSON-LD, slug-change 301 for page and prefixed store)
- Live sitemap.xml / robots.txt / rss.xml return 200; log clean

**Left:** Phase 7 — CSV/Excel imports + click analytics dashboard widgets.
