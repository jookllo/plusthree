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
- [ ] **Rich-text article editor** — swap the plain `Textarea` for Filament's `RichEditor` so editors get bold, italic, underline, headings, lists, quotes and links. Requires server-side sanitisation before the body is rendered (allowlist of tags/attributes) and prose styling for the new elements; bodies are currently escaped plain text.
- [ ] "Preview as visitor" for drafts.
- [x] Article featured images (16:9, resized, alt text required) — mounted volume still required in Dokploy.
- [ ] **In-body images** — the same editor supports file attachments (`fileAttachmentsDisk('public')`), so images can be uploaded mid-article into the same volume. Blocked on the rich-text editor, and needs a way to supply alt text: the editor's image button does not prompt for it.
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
