# Contributor and agent instructions — Plusthree

Applies to this project tree. Last reviewed 31 October 2026. Read the root README.md for detailed setup, status, acceptance criteria and troubleshooting; ROADMAP.md is the compact checklist. Newer explicit user instructions take precedence over this handoff.

## Objective and current state

Build a public African job board and career blog with owner-managed publishing. Browsing requires no login. The current application is a local design preview, not a CMS or production launch.

Implemented: Laravel 12, MySQL connection and content-schema migrations, Blade/Tailwind/Vite, database-backed homepage, `/jobs` search and detail pages, `/blog` index, article and category pages, content models with publication/expiry scopes, factories and tests, article featured-image uploads with generated fallback art, and a Filament 5.11 publishing panel at `/mdosi` restricted to `users.is_admin` accounts.

Not implemented: full-text search, alt text for in-body images, password-reset mail, 2FA, user-management screens, SEO sitemap/schema/canonicals, real photography, and the Dokploy deployment itself. Article images upload from `/mdosi`, but nothing is deployed and no volume exists yet.

Content models exist (`Company`, `JobListing`, `Article`, `Category`, `Tag`), are edited through the `/mdosi` panel, and are read by the public pages: `GET /`, `/jobs`, `/jobs/{slug}`, `/blog`, `/blog/{slug}`, `/blog/category/{slug}`. Only `User` exists as an account model; the panel requires `users.is_admin` (not mass assignable) and the starter Test User is not an admin. `config/preview.php` and the four fictional roles were removed. `/up` is the framework health route. Do not claim the development database contains real vacancies or editorial — it holds a few explicitly labelled “Sample:” records used for layout checks.

## Owner decisions to preserve

- Brand: `plusthree`; tagline “WHERE AFRICA’S GEMS MEET GLOBAL WORK.”
- Palette: navy #23305C, red #B3261E, gold #F2B632, green #1F8A70, ivory #FBF7F0. Use accessible darker variants when needed for readable text.
- Provisional logo is `app/public/images/plusthree-logo.png`; source is `output/logos/plusthree-africa-red-tagline.png`. Final logo work was deferred. Preserve all concepts.
- Laravel + MySQL + Blade/Tailwind is the selected stack. Livewire/Filament are planned. No Vue/separate frontend is needed by default.
- **Do not restore the “Where?” location field unless requested.** Location remains useful on jobs. Current `location` query parameters are ignored; remote shortcut uses keyword search.
- The owner rejected a generic marketing hero with oversized claims and rounded cards. Preserve job discovery, ruled/scannable listings, compact navigation and the editorial career column.
- The owner asked about images. Photos/illustrations have not actually been integrated. Source suitable assets when working on that task; do not require the owner to download them first, or pretend the current text panel is a photograph.
- Public users apply through external employer links/email initially. Do not add CV storage, applicant tracking, payments, accounts or employer self-service without a scope decision.
- No invented employer names, testimonials, hiring claims or metrics. Sample content must remain explicitly labelled and must not be published as real vacancies.

## Earlier design brief to retain

- Palette direction: **Kitenge Bold**, confident and energetic, inspired by printed fabric; exact colours above.
- References: https://www.idealist.org/en ; https://applynow.co.zw/category/careerhack/?tag=cvs ; https://github.com/nutlope/hallmark . Keep these references in future design handoffs.
- Logo exploration included a +3 symbol, an African continent silhouette, uppercase tagline, then a red tagline. The owner also requested Keith Haring-inspired and other playful alternatives; street-pop, cut-paper and bubbly pop-art concepts were generated. This does not establish one of those as the approved full-site style.
- The specific navy plusthree wordmark/+3 concept with green Africa and red uppercase tagline became the provisional website asset. Final logo work was postponed.
- The initial generic design was explicitly rejected as AI-generated. The current iteration has not been explicitly approved as final.
- Images were requested/discussed but remain unimplemented. Do not silently drop that outstanding design task.
- Full chronology and reference limitations are recorded in README.md under “Original design brief and later corrections.”

## Start-of-task checks

1. Read README.md, ROADMAP.md and any more-specific AGENTS.md files that exist at task time.
2. Inspect affected source and working-tree state; preserve user changes.
3. Work in `/home/okello/Documents/projects/Kajob/app` for artisan, Composer and frontend builds. The outer directory is not the Laravel root.
4. Check dependency manifests/locks before adding packages. Filament/Livewire are not installed yet; verify version compatibility before installing them.
5. Do not assume the browser, server, Docker container or temporary Hallmark checkout survived the last session.
6. Root `.git` was not a valid repository during this handoff. Never claim a commit was made or changes are backed up without verifying it. Do not replace environment-managed metadata.

## Source locations

- `app/routes/web.php`: public routes for home, jobs, job detail, blog, article and category pages.
- `app/app/Http/Controllers/`: `HomeController`, `JobListingController`, `ArticleController`, `CategoryController` for the public pages.
- `app/resources/views/jobs/`, `app/resources/views/blog/`, `app/resources/views/partials/`: public listing, detail and card templates.
- `app/resources/views/home.blade.php`: all homepage content and guides.
- `app/resources/css/app.css`: Tailwind entry/imports; `site.css`: custom design.
- `app/app/Models`, `app/app/Enums`, `app/app/Support`: content models, enums and slugs/visibility/timezone traits.
- `app/database/migrations`, `app/database/factories`: content-schema migrations and factories.
- `app/app/Filament/Resources`, `app/app/Providers/Filament/AdminPanelProvider.php`: `/mdosi` panel and CRUD resources.
- `app/tests/Feature/AdminPanelAccessTest.php`: panel access rules.
- `app/tests/Feature`: relevant behaviour tests.
- `app/tests/TestCase.php`: fail-fast test-database guard.
- `SCHEMA.md`: schema design, decisions and implementation record.
- `app/phpunit.xml`: test environment defaults.
- `scripts/setup-local-db.py`: local provisioning only; side effects below.
- `.hallmark/redesign-notes.md`: design history and limitations.

## Database and secret handling

- Configured local Docker container: `eden-test-mysql`, MySQL 8.4, host 127.0.0.1:3307.
- Development database: `plusthree`; testing: `plusthree_testing`; dedicated user: `plusthree`.
- Credentials live in ignored `app/.env` and `app/.env.testing`. Never print, commit or copy their contents into documentation, fixtures or responses.
- This Docker container belongs to shared local infrastructure. Never delete its volume, reset unrelated schemas or run broad Docker cleanup.
- **Laravel's existing `jobs` table is the queue table.** Create vacancies as `job_listings` / `JobListing`; do not repurpose the queue migration.
- Never run `migrate:fresh` or test database refreshes against development, production or another project's schema.
- `FoundationTest` uses RefreshDatabase. A fail-fast guard now lives in `app/tests/TestCase.php` and runs inside `setUpTraits()`, before any database trait touches a schema; it refuses any connection that is not MySQL `plusthree_testing`. Do not weaken or bypass it, and keep the positive control (running with `DB_DATABASE=plusthree`) in mind when changing test bootstrap.
- The provisioning helper generates/rotates the dedicated DB password on every invocation, updates `.env` and overwrites `.env.testing`. It is not routine startup. Avoid rerunning it unless reprovisioning is intended; explain its credential/config side effects.
- Do not regenerate an existing APP_KEY without a specific reason.
- Follow normal tool approval requirements for Docker/network access. Do not bypass sandbox restrictions or weaken service security.

## Implementation sequence

1. Content schema and publication rules — **implemented 31 October 2026** (models, migrations, factories, scopes and tests; see SCHEMA.md). Admin capability column exists but no panel uses it.
2. Publishing admin — **core implemented 31 October 2026; rich text and preview 1 November 2026**: Filament 5.11 at `/mdosi`, admin-only access, CRUD resources, article featured and in-body images, a rich-text editor and a signed draft preview. Remaining: alt text for in-body images, image cleanup, password reset, 2FA, user management.
3. Database-backed public jobs/search/pagination/detail/application/expiry states — **implemented 1 November 2026**.
4. Blog index/article pages/categories and homepage integration — **implemented 1 November 2026**; article search and tag pages remain.
5. Imagery and responsive design finish — **article featured images, in-body images and 20 fallback art panels implemented**; real photography and responsive `srcset` remain.
6. SEO metadata, sitemap, canonical/noindex policy, structured data, slug redirects and expiration handling.
7. End-to-end/accessibility/performance checks, then production deployment and operations.

See README milestone sections for detailed acceptance criteria. A different user-prioritised task may change the order. Do not mark a milestone complete when only its visual mockup exists.

## Architecture and implementation rules

- Use Laravel conventions: migrations, Eloquent relationships/scopes, controllers and form requests as complexity grows. Keep the homepage route name and existing links working through deliberate transitions.
- Centralise public visibility rules. Draft, future and archived records must not leak through lists, details, search, sitemap or JSON-LD. Open job queries also enforce deadlines independently of scheduler timing.
- Use UTC persistence with explicit display/input timezone handling; owner context is Africa/Nairobi.
- Choose stable unique slugs and an intentional redirect policy.
- Validate and parameterise search; escape displayed input; use pagination and eager loading for real data. Full-text search is future work, not current functionality.
- Public job search is a case-insensitive `LIKE` across title, description and company name, plus an exact contract-type filter; it is not full-text search. The homepage form submits to `/jobs`.
- Keep filters in shareable GET URLs and preserve selected values. If introducing Livewire, retain crawlable server-rendered content and keyboard operation.
- Escape plain text. Rich article HTML is rendered only through `App\Support\ArticleBody`; that sanitiser is the trust boundary, so do not echo `$article->body` directly. Do not assume Filament input alone makes arbitrary HTML safe.
- Validate uploads, storage access and application URL schemes. Never allow executable uploaded content or unsafe external URL protocols.
- Restrict the panel by explicit admin permission, not simply “any logged-in user.” The starter Test User is not a production admin.
- Keep schema changes non-destructive and review foreign-key deletion behaviour. No fabricated production seed data.

## Design and reference use

- Idealist homepage was visually reviewed. ApplyNow repeatedly failed to load and remains unreviewed.
- Hallmark is design guidance, not application code. Its actual guidance was consulted during the redesign. A temporary clone at `/tmp/plusthree-hallmark` may be gone.
- If using Hallmark for further design, read the relevant current guidance and report the checks actually run. User decisions override generic theme preferences. Do not claim the Blade page passed HTML checks when only CSS was parsed.
- Maintain contrast, visible focus, labelled controls, semantic headings, no horizontal overflow, meaningful empty states and reduced-motion support where motion exists.
- For images, record provenance/licence or generation prompts, add alt text and dimensions, optimise and use responsive loading. Never claim stock/generated people are actual employees or clients.
- Preserve source assets; save project-used assets in the workspace instead of pointing only to a temporary/generated-image directory.

## Commands and verification

From `app/`, with Node/pnpm on PATH:

```sh
composer install
pnpm install --frozen-lockfile
pnpm build
php artisan config:clear
php artisan test
php artisan test --filter=PublicPagesTest
php artisan route:list
php artisan serve --host=127.0.0.1 --port=8000
```

Do not run every command for every task. Choose checks appropriate to the change. A documentation-only edit needs consistency/link checks, not database resets.

On this host, PATH can include:

```sh
export PATH=/home/okello/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/bin:/home/okello/.cache/codex-runtimes/codex-primary-runtime/dependencies/bin/fallback:$PATH
```

The pnpm wrapper is a shell script, not a JavaScript module. The existing Composer setup/dev scripts still invoke npm/npx and are not the preferred pnpm workflow. Use explicit commands until intentionally updated.

- Asset changes: build; inspect the actual page at desktop/mobile widths and relevant states.
- Search changes: focused search tests including combined filters, no results and escaping; browser interaction when useful.
- Schema/admin changes: test on isolated MySQL, including denied access and publication visibility.
- SEO changes: inspect rendered HTML and public/private sitemap/schema outcomes.
- Do not rewrite tests merely to hide a regression. Update intentional behaviour expectations with an explanation.
- Historical results: full suite 30 tests/80 assertions on 31 October 2026 after the panel; 24 tests/69 assertions earlier the same day after the content schema; 7 tests/21 assertions after redesign; focused search suite 3 tests/12 assertions after location removal.
- Current noindex expectation is deliberate. At launch, replace it with environment-aware indexing and revise tests for local/staging versus production.

## Documentation and completion

After meaningful work:

1. Update README status, setup and known limitations with implemented behaviour.
2. Update ROADMAP milestones without marking partially built areas complete.
3. Update this file when decisions, safe commands or architecture change.
4. Report what changed, checks actually run, results and unfinished work.
5. Keep credentials, private data and unsupported claims out of all handoffs.

This task's deliverable is documentation, not permission to start all remaining milestones, deploy the application or contact anyone. Future implementation should follow the owner's requested scope.
