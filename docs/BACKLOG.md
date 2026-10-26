# Backlog — deferred items

These were specified but deferred to keep phases shippable. Pick up anytime.

## SEO (from Phase 6)
- Auto-generated Open Graph images per page (GD/Imagick, cached). Currently og:image uses an
  existing image if a view sets one.
- Internal-link suggester in the editor (scan text for store/category/roundup terms).
- Broken-link checker on a schedule to flag dead affiliate URLs.
- Split sitemap into an index + child sitemaps once past 10,000 URLs.

## Commerce (from Phase 2)
- Amazon Product Advertising API sync (behind a config flag; requires approved Associates account).
- Coupon "worked / didn't work" public voting feeding success/fail counts.

## General
- Full-page response caching keyed by model with cache busting on save.
- Signed draft-preview links that work without logging in.
