# TODO / backlog

Last updated: 1 November 2026. Scope detail and acceptance criteria live in [README.md](README.md) and [ROADMAP.md](ROADMAP.md); this file is the running list.

## Next up

- [ ] **Deploy the current preview to Dokploy** — containerise the app, connect the repository, provision production MySQL, set environment variables, attach a domain with HTTPS, confirm the `noindex` preview loads.
- [x] **Public job pages** — `/jobs` with keyword/type filters and pagination, `/jobs/{slug}` detail pages, application links, closed-job state. Homepage reads the database; `config/preview.php` removed.
- [x] **Public blog** — `/blog`, `/blog/{slug}`, category pages, article teasers on the homepage.

## Authentication and access (deferred by owner on 31 October 2026)

- [ ] Google sign-in for `/mdosi` (Laravel Socialite + Google OAuth client; restrict entry to named admin accounts).
- [ ] Email password reset (Filament reset flow + a real mail provider) so a forgotten password does not require server access.
- [ ] Keep the CLI password reset documented as break-glass recovery.
- [ ] Login throttling / rate limiting on failed attempts.
- [ ] A second admin account so one lost mailbox cannot lock the site.
- [ ] Optional two-factor authentication.
- [ ] User-management screen (currently admins are granted through `tinker`).

## Admin panel polish

- [ ] Visible dark-mode toggle in the top bar (Filament's light/dark/system switcher already exists in the avatar menu).
- [x] **Rich-text article editor** — Filament `RichEditor` with bold, italic, underline, headings, lists, quotes and links, plus a floating toolbar on text selection. Output is sanitised through `App\Support\ArticleBody` (Symfony HtmlSanitizer allowlist) before rendering.
- [x] "Preview as visitor" for drafts — a signed URL valid for 30 minutes, generated from the article's edit screen.
- [x] Article featured images (16:9, resized, alt text required) — mounted volume still required in Dokploy.
- [x] **In-body images** — uploaded mid-article to `articles/inline` on the same public volume.
- [ ] Alt text for in-body images: the editor's image button does not prompt for it, and Filament strips the `data-id` we would need to look it up. Add a custom image dialog.
- [ ] Delete orphaned in-body images when they are removed from an article (Filament only cleans up when a file-attachment provider is registered).
- [ ] Downscale in-body uploads; only the featured image is resized today.
- [ ] Optional quieter fallback art — halve the stripe weight and pull the motif colour closer to the base if the current article-hero backdrop reads too strongly behind the headline.
- [ ] Responsive `srcset` sizes and stored licence/provenance for photography.
- [ ] Per-role permissions if a second editor joins.

## Public site and content

- [ ] Imagery for the about band and job listings (article images are implemented; lists show a photo when one exists).
- [ ] SEO: per-page metadata, canonical URLs, sitemap, `JobPosting`/`Article` structured data, noindex policy.
- [ ] Typography decision for blog body text (system fonts today).
- [ ] Replace sample listings with real content once publishing is trusted.

## Deployment and operations

- [x] `Dockerfile` and `.dockerignore` (Node asset build, production Composer install, PHP runtime).
- [ ] Dokploy application, MySQL service, environment variables, domain and HTTPS.
- [ ] Mount `/var/www/html/storage/app/public` for uploaded images; include it in backups.
- [ ] GitHub Actions CI: tests, `composer audit`, `pnpm audit`, image build (Dependabot is configured).
- [ ] Persistent storage for uploads plus database backup and a restore test.
- [ ] Scheduler and queue workers when background jobs are introduced.
- [ ] Error monitoring and uptime checks.
