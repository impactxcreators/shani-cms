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
