# TODO / backlog

Last updated: 1 November 2026. Ordered by what to do next, not by topic. Scope detail and acceptance criteria live in [README.md](README.md) and [ROADMAP.md](ROADMAP.md).

## Before anything else

1. [ ] **Deploy to Dokploy.** Create the project, add a MySQL service, paste the environment block from [DEPLOY.md](DEPLOY.md), attach the domain with HTTPS, mount `/var/www/html/storage/app/public`, then run `php artisan migrate --force` and create the owner account. Nothing else below matters until the site exists on a real URL. Depends on: none. Blocks: everything.
2. [ ] **Confirm images survive a redeploy.** Upload a featured and an in-body image, redeploy, check both still render. The volume is the only thing standing between the owner and silently losing every image. Depends on: 1.
3. [ ] **Replace the sample content.** Three jobs and three articles are labelled "Sample:" and must not be published as real. Write or import real listings, then delete the samples. Depends on: 1.

## Launch blockers

4. [x] **SEO minimum.** Metadata, canonicals, Open Graph, `sitemap.xml`, `robots.txt`, `JobPosting`/`Article`/`WebSite` JSON-LD and an opt-in `SEO_INDEXABLE` policy. Remaining: set `SEO_INDEXABLE=true` and submit the sitemap to Search Console after the domain is live, plus a slug redirect policy.
5. [ ] **Alt text for in-body images.** The editor's image button does not ask for a description, so every inline image is invisible to screen readers. Needs a custom image dialog that captures alt text. Depends on: nothing.
6. [ ] **Downscale in-body uploads.** Only featured images are resized to 1600×900; an inline photo from a phone is served at full weight. Depends on: nothing.
7. [ ] **Database backup and a restore drill.** A nightly dump plus one real restore into a scratch database, timed and documented. A backup nobody has restored is not a backup. Depends on: 1.
8. [ ] **Account recovery.** Email password reset (Filament's flow plus a mail provider), a second admin account, and login throttling. Today a forgotten password means server access or `tinker`. Depends on: 1.

## Soon after launch

9. [ ] **GitHub Actions CI.** Tests, `composer audit`, `pnpm audit` and an image build on every push. Dependabot is already configured. Depends on: nothing.
10. [ ] **Delete orphaned in-body images** when they are removed from an article. Filament only cleans up when a file-attachment provider is registered. Depends on: nothing.
11. [ ] **Error monitoring and uptime checks.** Depends on: 1.
12. [ ] **Scheduler and queue workers** once any background job exists. Depends on: nothing yet.

## Design and content polish

13. [ ] **Photography.** The owner has not supplied real assets; nothing on the site is a real photograph yet. Needed: licence/provenance recorded and saved in the workspace, not a temporary directory.
14. [ ] **Responsive `srcset`** for article and in-body images.
15. [ ] **Imagery for the about band and job listings** — lists show a photo only when one exists.
16. [ ] **Typography decision for blog body text** (system fonts today).
17. [ ] **Optional quieter fallback art** — halve the stripe weight and pull the motif colour closer to the base if the article-hero backdrop reads too strongly behind headlines.
18. [ ] **Visible dark-mode toggle** in the panel top bar (Filament's switcher already sits in the avatar menu).

## Deferred by the owner (31 October 2026)

19. [ ] Google sign-in for `/mdosi` (Socialite + Google OAuth client, restricted to named admin accounts).
20. [ ] Optional two-factor authentication.
21. [ ] User-management screen (admins are currently granted through `tinker`).
22. [ ] Per-role permissions if a second editor joins.
23. [ ] Keep the CLI password reset documented as break-glass recovery.

## Done

- [x] Content schema, publication and expiry scopes, factories, tests.
- [x] Filament 5.11 panel at `/mdosi`, admin-only.
- [x] Public job pages: search, filters, pagination, detail, closed state.
- [x] Public blog: index, article, category pages, homepage teasers.
- [x] Article featured images with required alt text; generated art hero.
- [x] Rich-text editor with floating toolbar, sanitised output, in-body images.
- [x] Signed draft preview from the edit screen.
- [x] `Dockerfile`, `.dockerignore`, `DEPLOY.md`, trusted proxies, Dependabot.
