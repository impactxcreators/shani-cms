# How to use Shani CMS (for the owner)

## Logging in
1. Go to: https://gipsypockets.store/admin8869
2. Email: impactxcreators@gmail.com
3. Password: the temporary one you were given. **Change it right after your first login**
   (top-right menu → profile).

Keep the `/admin8869` address private — it is your "side door" and is kept secret on purpose.

## What you can do today
Phase 0 is just the foundation: you can log in and see an empty admin panel with the menu
groups (Content, Stores, Products, Editorial, Tools, Settings). The screens inside them get
built in the next phases.

## If a page ever shows an error
Tell me (Claude) "read the Laravel log" — the real reason is always recorded there.

## New in Phase 1

### Site Settings  (menu: Settings → Site Settings)
Change your site name, tagline, description, and the footer disclosure. The big switch here is
**"Show coming soon to visitors"** — leave it ON while the site is unfinished; turn it OFF when
you are ready to go public. You always see the real site while logged in.

### Pages  (menu: Content → Pages)
Create normal website pages (About, Contact, Privacy, etc.).
- **Title** fills the web address automatically; you can edit it.
- **Body** is a rich text editor (bold, links, lists, images).
- Set **Status** to *Published* to make it live. *Draft* keeps it hidden.
- A published page appears at `https://gipsypockets.store/your-slug`.

### Redirects  (menu: Tools → Redirects)
If a page moves, send its old address to a new one so visitors and Google do not hit a dead end.
Enter the old path (e.g. `/old-page`) and where it should go. Use **301** for a permanent move.

## New in Phase 2  (menu: Stores)
- **All Stores** — add a brand (e.g. Nike): name, website, your affiliate link, logo URL, categories.
- **Categories** — group stores (e.g. Fashion, Electronics). Can be nested.
- **Coupons** — pick the store, add a title and (optionally) a code, set a type and expiry.
  Expired coupons disappear from the site automatically.
- **Deals** — product price drops: original price, sale price, image, link.
- Every outgoing link goes through a tracked `/go/...` link so you can see what gets clicked.
- See a store live at `https://gipsypockets.store/store/<slug>` (once "coming soon" is off).

## New in Phase 3  (menu: Products)
- **Products** — add items you recommend: title, brand, image, price, rating, highlights, affiliate link.
- **Product Categories** — group products (Laptops, Phones…).
- **Roundup Lists** — build a "Best X for 2026" page: add products, drag to reorder, give each a
  badge (Best Overall, Best Budget), write a blurb, add FAQs. Set status to Published and it appears
  at `https://gipsypockets.store/<slug>`, listed on `/best`.

## New in Phase 4  (menu: Editorial)
- **Blog Posts** — write articles with a rich editor, author, categories, tags. Publish to show at
  `/blog/<slug>`, listed on `/blog`.
- **Authors** / **Blog Categories** — organise posts.
- **Handpicks** — small curated promo cards (used on the homepage later).
- **Comments** — visitor comments arrive as *Pending* (the menu shows a count). Click the green tick
  to approve, or the ✋ to mark spam. Only approved comments appear on the site.

## New in Phase 5  (menu: Content)
- **Home Banners** — big image banners for the top of the homepage. Drag to reorder.
- **Home Blocks** — build your homepage from sections: latest coupons, featured stores, blog feed,
  categories, handpicks, or custom text. Drag to reorder; toggle Active to show/hide.
- **Adverts** — ad slots with click counting.
If you create no blocks, the homepage shows a sensible default.

## New in Phase 6  (automatic SEO)
- Your site now has `/sitemap.xml`, `/robots.txt` and `/rss.xml` automatically.
- Each page tells Google its title, description and rich "structured data" (so roundups can show
  star ratings / lists, posts show as articles).
- If you change a page's web address (slug), the old address auto-forwards to the new one — no
  broken links.
- While "coming soon" is on, robots.txt tells search engines to stay away; flip maintenance off to
  open up.

## New in Phase 7
- **Import products from a spreadsheet:** Products → **Import CSV**. Upload a CSV, match the columns,
  and it imports with validation. Any bad rows come back as a downloadable file to fix.
  (Save your spreadsheet as CSV. Tip: put multiple highlights in one cell separated by | )
- **Dashboard:** now shows click totals (7/30 days), a 14-day clicks chart, coupons expiring soon,
  a warning if expired coupons are still live, and pending comments.

## New in Phase 8  (menu: Settings)
- **Users & Roles** — add team members and give them a role: Admin (everything), Editor (publish
  anything), Author (write own drafts), Viewer (read-only). Toggle *Active* to allow/block login.
- **Activity Log** — a history of who changed what (stores, coupons, posts, etc.).
- **Automatic upkeep:** expired coupons auto-archive, old click data is trimmed after 90 days, and
  the database is backed up daily (kept in storage/app/backups). These run via the cron job your
  developer/you set up once in hPanel (see DEPLOY.md).
