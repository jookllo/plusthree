# TODO / backlog

Last updated: 31 October 2026. Scope detail and acceptance criteria live in [README.md](README.md) and [ROADMAP.md](ROADMAP.md); this file is the running list.

## Next up

- [ ] **Deploy the current preview to Dokploy** — containerise the app, connect the repository, provision production MySQL, set environment variables, attach a domain with HTTPS, confirm the `noindex` preview loads.
- [ ] **Public job pages** — `/jobs` with keyword/type filters and pagination, `/jobs/{slug}` detail pages, application links, closed-job state. Replace `config/preview.php` on the homepage.
- [ ] **Public blog** — `/blog`, `/blog/{slug}`, category pages, article teasers on the homepage.

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
- [ ] Rich-text article editor with HTML sanitisation.
- [ ] "Preview as visitor" for drafts.
- [ ] Image uploads and storage (blocked on the imagery decision).
- [ ] Per-role permissions if a second editor joins.

## Public site and content

- [ ] Imagery for the about band and article headers (owner chose to ship without images for now).
- [ ] SEO: per-page metadata, canonical URLs, sitemap, `JobPosting`/`Article` structured data, noindex policy.
- [ ] Typography decision for blog body text (system fonts today).
- [ ] Replace sample listings with real content once publishing is trusted.

## Deployment and operations

- [ ] `Dockerfile` and `.dockerignore` (Node asset build, production Composer install, PHP runtime).
- [ ] Dokploy application, MySQL service, environment variables, domain and HTTPS.
- [ ] Persistent storage for uploads plus database backup and a restore test.
- [ ] Scheduler and queue workers when background jobs are introduced.
- [ ] Error monitoring and uptime checks.
