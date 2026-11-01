# Plusthree build roadmap

Updated 31 October 2026. See [README.md](README.md) for the detailed handoff and acceptance criteria, and [AGENTS.md](AGENTS.md) for contributor instructions.

Stack: Laravel 12, MySQL, Blade and Tailwind/Vite. Filament and Livewire are planned, not installed.

1. **Foundation — complete for local development.** Laravel, isolated development/test MySQL databases, build tooling and baseline checks.
2. **Content schema — implemented 31 October 2026.** Companies, `job_listings` (not the queue `jobs` table), articles, shared taxonomies, `users.is_admin`, publication/deadline scopes, factories and tests; details in [SCHEMA.md](SCHEMA.md). The public site and `/mdosi` now read these tables.
3. **Publishing admin — core implemented 31 October 2026.** Filament 5.11 at `/mdosi` (not the default `/admin`), admin-only access, and CRUD for companies, jobs, articles, categories and tags. Outstanding: image uploads, rich-text editor, record preview, password reset mail, 2FA and user management.
4. **Public design — preview implemented; finish remains.** Brand colours, provisional Africa logo, search-led layout, ruled job rows, editorial sidebar and responsive checks. Real imagery and final logo are outstanding.
5. **Job discovery — implemented 1 November 2026.** `/jobs` searches published, open listings by keyword and contract type with shareable URLs and pagination; `/jobs/{slug}` shows details and a safe apply destination, with closed roles readable but not applicable. Expired, draft, future and archived roles are excluded by query. The location selector stays removed; location is display-only.
6. **Blog — implemented 1 November 2026.** `/blog` (newest article as lead), `/blog/{slug}` with related reading, `/blog/category/{slug}`, and homepage teasers all read published articles. Article featured images upload through `/mdosi` with required alt text; articles without one use a generated hero panel. Rich-text bodies, tag pages and article search remain.
7. **SEO — basic title/description only.** Preview is deliberately noindex. Metadata management, canonicals, sitemaps, structured data, redirects and indexing policy remain.
8. **Verification — baseline only.** Existing homepage/search/database tests and prior responsive checks; future features require meaningful new coverage and production readiness checks.
9. **Deployment — files ready, not deployed.** A production `Dockerfile`, trusted-proxy configuration and [DEPLOY.md](DEPLOY.md) exist and the image builds locally. Still required: Dokploy project and MySQL service, production secrets, domain/HTTPS, the image-uploads volume, backups, and Search Console.

Initial release: public browsing without accounts; admin-managed jobs/articles; applications through employer links/email. Applicant tracking, CV uploads, payments and employer self-service are deferred.

Do not run migrations or destructive tests on another application's schema. Preserve the existing shared MySQL container and unrelated data. No deployment or complete CMS is implied by the local preview.
