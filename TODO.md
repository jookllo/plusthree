# TODO / backlog

Last updated: 11 November 2026. Ordered by what to do next, not by topic. Scope detail and acceptance criteria live in [README.md](README.md) and [ROADMAP.md](ROADMAP.md).

## Before anything else

1. [x] **Deploy to Dokploy.** Live at `https://plusthree.io`: MySQL service `plusthree-db`, environment configured, domain moved from port 3000 to 80, `plusthree-uploads` volume mounted at `/var/www/html/storage/app/public`, migrations run, owner account created.
2. [ ] **Confirm images survive a redeploy.** The volume is attached, but nothing has been uploaded through it yet. Upload a featured and an in-body image, redeploy, check both still render. Depends on: 3.
3. [ ] **Replace the sample content.** Three jobs and three articles are labelled "Sample:" and must not be published as real. Write or import real listings, then delete the samples. Depends on: 1.

## Launch blockers

4. [x] **SEO minimum.** Metadata, canonicals, Open Graph, `sitemap.xml`, `robots.txt`, `JobPosting`/`Article`/`WebSite` JSON-LD and an opt-in `SEO_INDEXABLE` policy. Remaining: set `SEO_INDEXABLE=true` and submit the sitemap to Search Console after the domain is live, plus a slug redirect policy.
5. [x] **Alt text for in-body images.** A "Describe images" action on the article editor lists every image without a description and writes them back into the body. Remaining: the same check on the create screen.
6. [x] **Downscale in-body uploads.** Anything wider than 1400px is re-encoded to 1400px on upload; failures keep the original file. Required adding JPEG/WebP support to GD in the image.
7. [ ] **Database backup and a restore drill.** A nightly dump plus one real restore into a scratch database, timed and documented. A backup nobody has restored is not a backup. Depends on: 1.
8. [ ] **Account recovery.** Filament's password reset request/reset pages are wired and send through the configured mailer; set the Resend environment before relying on them. Remaining: a second admin account and login throttling. The CLI reset stays documented as the offline fallback. Depends on: 1.

## Soon after launch

9. [x] **GitHub Actions CI.** MySQL-backed test run, `composer audit`, `pnpm audit` and a Docker image build on every push and pull request.
10. [x] **Delete orphaned in-body images.** `php artisan images:prune` reports files under `articles/inline` that no article references; `--force` deletes them. Dry run by default, 24-hour grace. Remaining: schedule it once a scheduler runs.
11. [ ] **Error monitoring and uptime checks.** Depends on: 1.
12. [ ] **Scheduler and queue workers** once any background job exists. Depends on: nothing yet.

## Job lifecycle follow-ups (5 November 2026)

13. [ ] **301 long-closed roles after a grace period.** Expired and archived roles currently keep a readable page with the closed notice and no `JobPosting`. Sector pages now exist, so redirect a role to its sector (or `/jobs`) after roughly 90 days so the old URL still passes equity.
14. [ ] **Decide whether to auto-archive expired roles.** `closing_at` passing does not change `status`, so expired roles stay `Published` in the panel and invisible publicly. Either schedule `jobs:archive-expired` nightly or leave it manual. Needs an owner decision.
15. [ ] **Job sector filter.** Sectors now have landing pages and appear on each job, but the `/jobs` search form still has no Sector select. Add one (shareable, `noindex` while filtered) before asking the owner to populate them in production.
16. [ ] **Describe-images check on the create screen** for articles and jobs; it currently runs from the edit screen only.
17. [ ] **IndexNow and the Google Indexing API.** Ping IndexNow when a job is published so Bing and Yandex see it within minutes; Google recommends the Indexing API over sitemaps for job URLs. Both need a scheduler (see 12).

## Design and content polish

17. [ ] **Photography.** The owner has not supplied real assets; nothing on the site is a real photograph yet. Needed: licence/provenance recorded and saved in the workspace, not a temporary directory.
18. [ ] **Responsive `srcset`** for article and in-body images.
19. [ ] **Imagery for the about band and job listings** — lists show a photo only when one exists.
20. [ ] **Typography decision for blog body text** (system fonts today).
21. [ ] **Optional quieter fallback art** — halve the stripe weight and pull the motif colour closer to the base if the article-hero backdrop reads too strongly behind headlines.
22. [ ] **Visible dark-mode toggle** in the panel top bar (Filament's switcher already sits in the avatar menu).

## Deferred by the owner (31 October 2026)

23. [x] Google sign-in for `/mdosi` — Socialite, `ADMIN_EMAILS` allowlist enforced at the callback, Google-only accounts for new admins, password login kept as break-glass. Remaining: add the Google credentials in Dokploy.
24. [ ] Optional two-factor authentication.
25. [ ] User-management screen (admins are currently granted through `tinker`).
26. [ ] Per-role permissions if a second editor joins.
27. [ ] Keep the CLI password reset documented as break-glass recovery.

## Done

- [x] Sector and country landing pages (`/jobs/sector/{slug}`, `/jobs/in/{country}`) with internal links, sitemap inclusion at three open roles and `noindex` below that (11 November 2026).
- [x] `JobPosting` made Google-compliant: ISO country codes, remote eligibility, `identifier`, employer `sameAs`, email applications as `directApply` (11 November 2026).
- [x] `Organization` JSON-LD with `info@plusthree.io` and a footer contact line on every page (11 November 2026).
- [x] Job lists went back to newest-first so new roles are not buried; closing-soon work is now a chip on the row plus a notice above the list linking to `?closing=soon` (11 November 2026).
- [x] The header's first link was renamed from “Careers” to “Jobs” (11 November 2026).
- [x] Homepage paginates six roles at a time instead of linking to “See all open roles” (5 November 2026).
- [x] Header converted from a “Find a job” dropdown to five inline links (5 November 2026).
- [x] Numbered pagination on `/jobs` and `/blog`; homepage role count removed (5 November 2026).
- [x] Closing-soon urgency labels and the `?closing=soon` filter (5 November 2026).
- [x] Job listings gained article-parity editing: rich description/requirements, featured image and alt text, SEO overrides, preview and describe-images (5 November 2026).
- [x] Archived jobs stay readable with the closed notice and no `JobPosting`; Part-time added as an employment type (5 November 2026).
- [x] Dedicated `/fellowships` and `/other-opportunities` pages, listed in the sitemap.
- [x] Optional sender choice (`MAIL_SENDER_ADDRESSES`) in the email composer, stored per email.

- [x] Content schema, publication and expiry scopes, factories, tests.
- [x] Filament 5.11 panel at `/mdosi`, admin-only.
- [x] Public job pages: search, filters, pagination, detail, closed state.
- [x] Public blog: index, article, category pages, homepage teasers.
- [x] Article featured images with required alt text; generated art hero.
- [x] Rich-text editor with floating toolbar, sanitised output, in-body images.
- [x] Signed draft preview from the edit screen.
- [x] `Dockerfile`, `.dockerignore`, `DEPLOY.md`, trusted proxies, Dependabot.

## Inbox

- [x] Public employer form at `/for-employers`, stored in `messages` (name, email, organisation, role, type, link, details), honeypot and a 6/minute throttle. Nothing is emailed on submit, so a mail outage cannot lose an enquiry.
- [x] Panel **Inbox → Messages**: unread badge, filters, mark handled/unread, delete, and a Reply action that pre-fills the composer with the original quoted.
- [x] Panel **Inbox → Emails**: rich-text compose with drafts, preview, per-recipient send through Resend, and a stored `sent`/`failed` status with the provider's reason.
- [ ] Receiving replies in the panel (Resend inbound + webhook). Until then, use Cloudflare Email Routing to forward `info@plusthree.io` to a real inbox.
- [ ] Saved templates and contact lists, images in email bodies.
