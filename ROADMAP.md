# Plusthree build roadmap

Updated 31 October 2026. See [README.md](README.md) for the detailed handoff and acceptance criteria, and [AGENTS.md](AGENTS.md) for contributor instructions.

Stack: Laravel 12, MySQL, Blade and Tailwind/Vite. Filament and Livewire are planned, not installed.

1. **Foundation — complete for local development.** Laravel, isolated development/test MySQL databases, build tooling and baseline checks.
2. **Content schema — implemented 31 October 2026.** Companies, `job_listings` (not the queue `jobs` table), articles, shared taxonomies, `users.is_admin`, publication/deadline scopes, factories and tests; details in [SCHEMA.md](SCHEMA.md). Nothing public or admin-facing reads these tables yet.
3. **Publishing admin — core implemented 31 October 2026.** Filament 5.11 at `/mdosi` (not the default `/admin`), admin-only access, and CRUD for companies, jobs, articles, categories and tags. Outstanding: image uploads, rich-text editor, record preview, password reset mail, 2FA and user management.
4. **Public design — preview implemented; finish remains.** Brand colours, provisional Africa logo, search-led layout, ruled job rows, editorial sidebar and responsive checks. Real imagery and final logo are outstanding.
5. **Job discovery — sample-only.** Keyword and type filters work over four fictional configuration records. Database search, pagination, detail pages, application links and expiry handling remain. The user removed the location selector; do not restore by default.
6. **Blog — sample copy only.** Three hardcoded expandable guides exist. Article database, full pages, taxonomy, search and related content remain.
7. **SEO — basic title/description only.** Preview is deliberately noindex. Metadata management, canonicals, sitemaps, structured data, redirects and indexing policy remain.
8. **Verification — baseline only.** Existing homepage/search/database tests and prior responsive checks; future features require meaningful new coverage and production readiness checks.
9. **Deployment — not started.** Domain/hosting, secrets, persistent storage, HTTPS, backups, scheduler/workers, monitoring and Search Console.

Initial release: public browsing without accounts; admin-managed jobs/articles; applications through employer links/email. Applicant tracking, CV uploads, payments and employer self-service are deferred.

Do not run migrations or destructive tests on another application's schema. Preserve the existing shared MySQL container and unrelated data. No deployment or complete CMS is implied by the local preview.
