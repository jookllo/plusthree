# Plusthree — project guide and implementation handoff

Last inspected: **31 October 2026** (Africa/Nairobi).

Plusthree is a job board and career blog connecting African talent with global work. The tagline is **“WHERE AFRICA’S GEMS MEET GLOBAL WORK.”** Visitors should browse without an account. The owner should publish jobs and articles through a private admin panel.

**Current state: a local, interactive design preview with a working Laravel/MySQL foundation. It is not a finished job board or CMS and has not been deployed.**

Read [AGENTS.md](AGENTS.md) for contributor/agent instructions, [ROADMAP.md](ROADMAP.md) for the short milestone checklist and [TODO.md](TODO.md) for the running backlog. This README is the detailed handoff. All commands below state their working directory; application commands run inside `app/`, not at the repository root.

## 1. What is implemented

| Area | Implemented | Important limit |
| --- | --- | --- |
| Backend | Laravel 12 installed with Composer lockfile | Only the homepage has application-specific behaviour |
| Database | Dedicated MySQL development and testing databases configured; content-schema migrations applied | No public page or admin screen reads the new tables yet |
| Content schema | `companies`, `job_listings`, `articles`, `categories`, `tags`, pivot tables and `users.is_admin`; publication/expiry scopes, enums, factories | Only the admin panel reads these tables; no public page does yet. See [SCHEMA.md](SCHEMA.md) |
| Publishing admin | Filament 5 panel at `/mdosi`; admin-only sign-in; create/edit/delete for companies, job listings, articles, categories and tags; publication state, dates and expiry | Plain-text article body; no image uploads, record preview, scheduling UI or user management |
| Frontend | Blade, Tailwind 4, Vite 7, pnpm lockfile | No Vue, Livewire or Filament installed |
| Homepage | Responsive masthead, provisional logo, gold search area, ruled job list, editorial sidebar, about/employer sections | Local preview with fictional listings |
| Search | GET keyword search and employment-type filters | In-memory substring filtering, not database/full-text search |
| Job previews | Four sample roles with expandable descriptions | No real employers, application workflow or standalone job pages |
| Career advice | Three short readable guides inside expandable homepage sections | Hardcoded Blade content; no blog database, article URLs or editor |
| Branding | Supplied colours, Africa logo with red uppercase tagline, saved logo concepts | Logo remains provisional; raster PNG, not a finished vector identity |
| Accessibility basics | Labels, semantic headings, skip link, visible keyboard focus, native details controls | Not a complete accessibility audit |
| SEO foundation | Server-rendered HTML, page title and description | Intentionally `noindex, nofollow`; sitemap/canonical/schema work remains |
| Verification | PHP feature tests including schema visibility, expiry boundary, delete rules and database isolation | Does not verify features that do not exist |

### Current public behaviour

- `GET /` is named `home`.
- `GET /up` is Laravel's configured health route, not a full database/storage health check.
- Homepage anchors: `#main`, `#jobs`, `#career`, `#about`, `#publishing`.
- `q`: optional string, maximum 100 characters. Trimmed and matched case-insensitively against sample **title, sector, description and work mode**.
- `type`: optional exact value: `Full-time`, `Contract`, or `Internship`.
- Keyword and type conditions are combined. Type links preserve the current keyword.
- Search returns all matching sample entries; there is no pagination, relevance ranking, stemming, typo tolerance or saved search.
- The “Where?” location field was removed at the user's request. Location is still shown on listing rows. Old `location=` URL parameters are ignored by the current route.
- The “Remote work” shortcut now uses `?q=remote#jobs`.
- All examples are explicitly marked as sample roles. Applications are unavailable.
- “Career advice” opens a section on the same page, not a separate blog index.
- “For employers” points to a section explaining that submissions are not open. There is no employer portal.

## 2. Stack decisions and product boundaries

### Chosen architecture

One Laravel application serves both the public website and, eventually, the private CMS.

| Layer | Current / planned choice |
| --- | --- |
| Backend | Laravel 12; installed host PHP was 8.3.6; Composer requires PHP `^8.2` |
| Database | MySQL 8.4 in an existing Docker container |
| Public templates | Blade, server-rendered |
| Styling/build | Tailwind 4 plus custom CSS; Vite 7; pnpm |
| Interaction | Native GET forms and details today; Livewire planned where useful |
| Admin | Filament 5.11 at `/mdosi`; explicit `is_admin` check in every environment; Livewire ships with Filament |
| Testing | PHPUnit 11 through `php artisan test`; real isolated MySQL testing database |

PostgreSQL was discussed originally, but the implementation now uses MySQL because the machine already had it. Do not change database engines or introduce Vue, Nuxt, React, or a separate API frontend as an incidental task.

### Initial release scope

- Public job discovery and career articles without registration.
- Owner/admin-managed job and article publishing.
- Application links to employers or clearly presented email instructions.
- Searchable, crawlable individual content pages.

Deferred: CV collection, applicant tracking, candidate accounts, employer self-service, payments, job alerts/newsletter subscriptions, social login and application messaging. Revisit deliberately; do not build these by assumption.

## 3. File map

| Path from project root | Role |
| --- | --- |
| `app/` | Laravel application root; contains artisan and dependency manifests |
| `app/routes/web.php` | Homepage route, validation and sample filtering |
| `app/config/preview.php` | Four fictional job examples; temporary data source |
| `app/resources/views/home.blade.php` | Entire current public page and hardcoded career guides |
| `app/resources/css/app.css` | Tailwind entry, source discovery and original colour/font tokens; imports site.css |
| `app/resources/css/site.css` | Custom responsive design, focus styles and local design tokens |
| `app/resources/js/app.js` | Laravel starter JavaScript entry |
| `app/public/images/plusthree-logo.png` | Provisional Africa/red-tagline logo used by the page |
| `app/public/build/` | Generated, ignored production asset bundles and Vite manifest |
| `app/app/Filament/Resources/` | Filament resources: companies, job listings, articles, categories, tags (forms, tables, pages) |
| `app/app/Providers/Filament/AdminPanelProvider.php` | Panel configuration: `/mdosi` path, brand name, colour palette, login |
| `app/public/css/filament/`, `app/public/js/filament/`, `app/public/fonts/filament/` | Filament assets published locally by `filament:install` |
| `app/app/Models/User.php` | Starter User model now carrying `is_admin` and author/listing relations |
| `app/app/Models/` | `Company`, `JobListing`, `Article`, `Category`, `Tag` — content models (not yet rendered publicly) |
| `app/app/Enums/` | `EmploymentType`, `WorkMode`, `PublicationStatus` backed enums |
| `app/app/Support/` | `HasUniqueSlug`, `Publishable` (visibility scopes), `NairobiTime` (UTC boundary conversion) |
| `app/database/migrations/` | Starter users/cache/queue tables plus the `2026_10_31_*` content-schema migrations |
| `app/database/factories/` | Factories for every content model, with draft/published/open/closed/remote states |
| `app/tests/TestCase.php` | Fail-fast guard refusing any database other than `plusthree_testing` |
| `SCHEMA.md` | Content schema design, decisions, and implementation record |
| `app/database/seeders/DatabaseSeeder.php` | Starter Test User seeder; not a secure admin provisioning workflow |
| `app/bootstrap/app.php` | Laravel bootstrap/routing and `/up` configuration |
| `app/tests/Feature/FoundationTest.php` | Homepage/preview expectations and isolated MySQL checks |
| `app/tests/Feature/DiscoveryPreviewTest.php` | Sample keyword/type filtering, empty state and escaping |
| `app/tests/Feature/ExampleTest.php`, `app/tests/Unit/ExampleTest.php` | Starter smoke/trivial tests, not evidence of business functionality |
| `app/phpunit.xml` | Test overrides including MySQL and `plusthree_testing` |
| `app/.env.example` | Safe configuration template, no working password |
| `app/.env`, `app/.env.testing` | Ignored local secrets; never include contents in documentation or logs |
| `app/composer.lock`, `app/pnpm-lock.yaml` | Dependency locks; preserve and use them |
| `app/pnpm-workspace.yaml` | Explicit permission for esbuild's installation script |
| `scripts/setup-local-db.py` | Machine-specific local database provisioning helper |
| `output/logos/` | Saved logo explorations and image-generation prompt records |
| `.hallmark/redesign-notes.md` | Reference review, design changes and prior check limitations |

**Naming trap:** the existing migration `0001_01_01_000002_create_jobs_table.php` creates Laravel queue tables. Its `jobs` table is not a vacancy table. Use a model such as `JobListing` and table `job_listings` for vacancies.

## 4. Running the existing project on this machine

Workspace: `/home/okello/Documents/projects/Kajob`.

### Database

Start Docker Desktop first. Check the container before starting it:

```sh
docker ps -a --format '{{.Names}}\t{{.Image}}\t{{.Status}}\t{{.Ports}}'
docker start eden-test-mysql
```

Established connection:

| Setting | Value |
| --- | --- |
| Container | `eden-test-mysql` |
| Image | `mysql:8.4` |
| Host / published port | `127.0.0.1:3307` (container port 3306) |
| Development database | `plusthree` |
| Test database | `plusthree_testing` |
| Dedicated database user | `plusthree` |
| Password | Stored only in the local ignored environment files |

The container is shared infrastructure from another project. Do not delete its volumes, reset unrelated databases or run a blanket Docker cleanup. Another container, `kamovie-mysql`, was also present; it is not this application's configured database.

### Application and assets

From the Laravel application directory:

```sh
cd /home/okello/Documents/projects/Kajob/app
composer install
pnpm install --frozen-lockfile
pnpm build
php artisan migrate
php artisan serve --host=127.0.0.1 --port=8000
```

Open [http://127.0.0.1:8000/](http://127.0.0.1:8000/). The server is a local development process; a previously running preview may have stopped between sessions. Check before starting a second server.

For active CSS/JS development, run `pnpm dev` in a separate terminal while Laravel serves the application. Without Vite's development server, rerun `pnpm build` after asset edits.

Do not regenerate `APP_KEY` on every start. Existing local environment files are already provisioned.

### Node/pnpm on this machine

Node and npm were not initially on the standard shell PATH. A bundled Node 24 runtime and pnpm wrapper were found:

```sh
export PATH=/home/okello/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/bin:/home/okello/.cache/codex-runtimes/codex-primary-runtime/dependencies/bin/fallback:$PATH
node --version
pnpm --version
```

The pnpm wrapper is a shell executable. Run it directly, **not** through `node`. These are machine-specific paths and may change after runtime updates. A normal supported Node installation with pnpm is fine on another machine.

If dependencies are already installed, the direct build equivalent, from `app/`, is:

```sh
/home/okello/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/bin/node node_modules/vite/bin/vite.js build
```

Run it from `app/`: Vite resolves `resources/css/app.css` relative to the working directory.

### Starter script caveat

`composer setup` and `composer dev` still contain Laravel's original npm/npx commands. They have not been aligned with this pnpm workflow. `composer setup` also generates an application key and migrates automatically. Use the explicit commands above; updating those convenience scripts is an outstanding housekeeping task.

## 5. Fresh environment setup

1. Install compatible PHP with the required Laravel extensions, including `pdo_mysql`, plus Composer, Node/pnpm, and MySQL 8.4 or a compatible MySQL server.
2. From `app/`, run `composer install` and `pnpm install --frozen-lockfile`.
3. Only if `.env` is absent, copy `.env.example` to `.env`.
4. Create dedicated development and test databases, and a database user scoped to those databases. Set host, port, user and password in local environment files. Never reuse an unrelated application's database.
5. Run `php artisan key:generate` for the new development environment.
6. Create `.env.testing` locally using the development file as a starting point. Set `APP_ENV=testing` and `DB_DATABASE=plusthree_testing`; confirm the host/user/password point to the isolated test database server.
7. Run `php artisan key:generate --env=testing` for that new environment.
8. Run `php artisan config:clear`, `php artisan migrate`, `pnpm build`, and `php artisan test`.
9. Start Laravel and open the preview.

Current local defaults: file sessions, file cache, synchronous queues, and log mail. These allow the basic preview to run without background workers or an email provider. They are not a production operations setup.

### Provisioning helper: understand before running

From the project root, `python3 scripts/setup-local-db.py` targets the named `eden-test-mysql` container. It expects `app/.env` to exist and the container to expose its root password internally through `MYSQL_ROOT_PASSWORD`.

The helper:

- Creates `plusthree` and `plusthree_testing` if absent.
- Creates or updates the dedicated `plusthree` MySQL user and grants access to those two databases.
- **Generates a new password on every run**, including for an existing user.
- Updates `.env`, overwrites `.env.testing` from it with testing replacements, and sets both files to mode 0600.
- Does not print credentials and does not migrate application tables.

It is not a routine start command, a general Docker installer or a production provisioning tool. Rerunning invalidates existing connections using the old password, overwrites test-specific customisation and may require clearing config caches/restarting processes. Do not run it just because the container needs starting.

## 6. Design direction, references and assets

### Brand colours

| Colour | Hex | Intended use |
| --- | --- | --- |
| Navy | `#23305C` | Text, primary action, structural emphasis |
| Red | `#B3261E` | Tagline and editorial accents |
| Gold | `#F2B632` | Search area and selected brand accents |
| Green | `#1F8A70` | Brand accents |
| Ivory | `#FBF7F0` | Page background |

The custom stylesheet also uses a darker green for readable text and editorial surfaces. Maintain contrast; the base brand green is not suitable for every small-text combination. Consolidating the overlapping original Tailwind and custom CSS tokens is future cleanup, not an excuse to change the palette.

References supplied by the owner:

- [Idealist](https://www.idealist.org/en): visually reviewed; practical search placement and clear content navigation inform the design.
- [ApplyNow career/CV category](https://applynow.co.zw/category/careerhack/?tag=cvs): fetch and browser attempts failed/timed out. **Not visually reviewed.** Do not claim otherwise; inspect later if available.
- [Hallmark](https://github.com/nutlope/hallmark): design guidance, not a job-board application template. The actual skill and supporting references were read during the redesign.

The owner rejected the first generic oversized-heading/rounded-card landing page. Preserve the newer search-led layout, compact navigation, ruled listing rows, editorial sidebar and brand character. Avoid vague promotional filler, fabricated metrics, fake employers, decorative cards everywhere and stock UI patterns without reference to this product.

### Original design brief and later corrections

Preserve the full brief, not just the current implementation:

- The supplied palette image was labelled **“Kitenge bold”**, described as **confident and energetic, inspired by printed fabric**. Its exact five colours are listed above. The original reference image was `/home/okello/Downloads/IMG-20261030-WA0010.jpg`; this external Downloads path is historical reference, not a portable project asset.
- The named inspirations were **Idealist**, **ApplyNow’s CareerHack/CVs category**, and **Nutlope’s Hallmark repository**, with the exact links above. These are references for an original plusthree design, not permission to copy another site's branding or content.
- The brand name is **plusthree**, with the tagline **“where africa's gems meet global work.”** The owner subsequently requested the whole tagline uppercase and then red.
- The owner requested three logo choices, then explored a **+3 mark**, a **Keith Haring–inspired vibe**, and other **more playful art styles**. Three playful directions were explored: energetic street-pop figures, modernist cut-paper forms, and bubbly retro pop art. These were logo explorations, not approval to apply one of those styles to the entire website.
- The owner asked to include the **African continent**, then specifically selected the earlier navy wordmark/+3 concept as the reference for a green Africa silhouette and red tagline. The resulting logo is the provisional asset currently used. Final logo decisions were explicitly postponed.
- The owner objected that the first website looked **AI-generated** and asked for the supplied design references to be actually reviewed and used. Do not revert to that generic landing-page approach or claim a reference review that did not happen.
- The owner subsequently asked whether images could be used. Imagery is desired and remains to be implemented; the user does not need to source/download every image themselves.
- The owner requested removal of the **“Where?”** location field. Keep that correction even if a reference website uses a location search.

The current layout is a working design iteration, not a recorded final design approval. Keep reference-led visual review part of future design changes.

### Logo and imagery

Current provisional logo: `output/logos/plusthree-africa-red-tagline.png`, copied to `app/public/images/plusthree-logo.png`. It has the +3 emblem, Africa silhouette, plusthree wordmark and uppercase red tagline. Earlier concepts and prompts remain in `output/logos/`.

The owner deferred final logo work. Do not treat the current raster as an approved final vector identity.

**Photography/illustrations have not been added to the website.** The sidebar's “More than a job title. Your story.” panel is typography, not a photograph. The owner specifically asked about images; an explanation was given, but sourcing/integration remains outstanding. They do not need to download assets themselves. Source appropriately licensed imagery or generate assets when requested, record provenance/credits, optimise sizes and provide meaningful alt text. Do not represent stock/generated people as actual customers or team members.

### Previous visual checks

Desktop 1280×800 and mobile 390×844 were inspected during the redesign. Hallmark's static lint reported zero FAIL/REFLEX/WARN for the custom CSS at that point. Its static tool did not parse Blade as HTML; this was **not full Hallmark certification**. Browser checks used the supported browser tool instead of Hallmark's rendering automation. A temporary clone was at `/tmp/plusthree-hallmark`; it is not a guaranteed dependency and may no longer exist.

## 7. Implementation plan with acceptance criteria

The following is proposed work, **not existing functionality**. Keep implementation in small, working milestones and update this document after each one.

### Milestone 1 — Content schema and publication rules (implemented 31 October 2026)

Implemented: migrations, models, enums, factories, publication/expiry scopes and tests, matching [SCHEMA.md](SCHEMA.md), which records the exact columns, indexes and decisions. The design below remains the reference; nothing here is exposed through the public site yet.

Suggested starting model/table design:

| Model/table | Initial fields / relationships |
| --- | --- |
| `Company` / `companies` | Name, unique slug, website, logo path, description; has many job listings |
| `JobListing` / `job_listings` | Company FK, title, unique slug, description, requirements, employment type, work mode, location fields, application URL or email, publication status/date, closing date, creator/editor |
| `Article` / `articles` | Author FK, title, unique slug, excerpt, body, featured-image path/alt text, publication status/date, SEO title/description |
| Categories / tags | Names, unique slugs and relationships; decide separate job/article categories or a clearly scoped shared taxonomy |
| `User` | Explicit admin permission or role; author/editor relationships as needed |

Add salary/currency/period only if publishing needs them; do not invent salaries. A remote role may still have country eligibility restrictions. Keeping location data does not mean restoring the removed “Where?” control.

Tasks:

- Define statuses, e.g. draft/published/archived, with future `published_at` supporting scheduling.
- Define public eligibility once: published status, non-future publication time and, for active jobs, no elapsed deadline. Reuse scopes/policies across lists, details, sitemap and schema.
- Choose UTC storage and explicit display/input time-zone rules. The owner is in Africa/Nairobi; scheduling must not depend on the host's timezone.
- Add migrations, foreign keys, unique slugs and indexes for publication/date/filter queries.
- Decide safe delete behaviour for referenced companies/authors; avoid accidental content cascades.
- Add factories and development-only example seeders, separate from real content.
- Decide how slug changes redirect old URLs and preserve discoverability.

Acceptance: migrations work on dedicated MySQL; relationships and publication/expiry scopes have tests; draft/future/archived content never leaks through public reads. Existing queue `jobs` table remains untouched.

### Milestone 2 — Private publishing admin (core implemented 31 October 2026)

Implemented: Filament 5.11 at `/mdosi`, brand-neutral admin theme, login, an explicit `canAccessPanel()` check requiring `users.is_admin`, and resources for companies, job listings, articles, categories and tags — including enum selects with brand labels, a date-only closing date stored as 23:59:59 Africa/Nairobi converted to UTC, publish-on-status (no manual publication picker; the timestamp is stamped on save), slug generation/validation, automatic `created_by`/`updated_by`/author defaults, status badges and filters. Non-admins receive 403; guests are redirected to `/mdosi/login`; `/admin` and the resource paths outside the panel prefix return 404.

Not implemented yet: image uploads (imagery is deferred), a rich-text editor (article bodies are plain textareas), a record preview page, mail-dependent password reset, Google sign-in, two-factor authentication, and a user-management screen — grant further admins deliberately via `php artisan tinker` until that exists. Deferred auth work is listed in [TODO.md](TODO.md).

- Install a compatible Filament version and its required Livewire dependencies. Check actual package requirements first; do not guess compatibility or blindly upgrade Laravel/PHP.
- Configure a private panel at `/mdosi` (owner's chosen path, set through the Filament panel provider, not the default `/admin`). The path is cosmetic: it must not be treated as a security control, and explicit admin authorisation is still required in every environment.
- Provision the owner account deliberately without a hardcoded/default production password. The starter Test User seeder is not an admin setup procedure.
- Build company, job, article and taxonomy resources with required-field validation.
- Job editor: employer, content, work type/location, application method, publication time, deadline, preview and status controls.
- Article editor: rich body, image/alt text, excerpt, author, category/tags, slug, SEO fields, draft/preview/publish controls.
- Sanitize rich HTML at the appropriate boundary. Escaped plain-text Blade output today does not make future rich-text rendering safe automatically.
- Validate uploads by type/size, use safe storage paths and reject executable content.
- Restrict drafts and previews to authorised users or scoped signed preview links.
- Add login throttling and test authorisation on the panel and individual actions.

Acceptance: a non-admin cannot publish or access drafts; the owner can create, preview, edit, publish/unpublish and archive content; future publication and job deadlines behave consistently.

### Milestone 3 — Real job discovery and detail pages

- Replace `config('preview.jobs')` with published Eloquent queries; move growing route logic into controllers/query services.
- Add `/jobs` and `/jobs/{slug}` (proposed routes; not present today).
- Start with parameterised keyword matching across title, description and company. Define multi-word semantics and stable ordering; add full-text search when requirements/data justify it.
- Keep keyword/type state in shareable GET URLs; validate input, paginate results and avoid N+1 company queries.
- Preserve the user's removal of the location selector. Add category/remote controls only with a clear design need; location still belongs in job content.
- Build readable detail pages with employer, requirements, closing date and a valid application destination.
- Allow only appropriate application URL schemes; no `javascript:` or arbitrary unsafe links. Present email instructions without storing applicant CVs.
- Show useful no-results states and clear-filter behaviour.
- Stop presenting examples as production content. An empty real database should show an honest empty state.
- Mark expired jobs closed and disable application actions. Decide whether useful closed pages remain accessible or are removed, and apply that policy consistently to search/sitemaps/schema.

Acceptance: an admin-published job appears publicly; drafts/future/expired jobs do not appear in active results; keyword/type combinations paginate correctly; detail pages and application links work without login.

### Milestone 4 — Real blog

- Add `/blog`, `/blog/{slug}` and category/tag views as appropriate.
- Connect homepage article teasers to published articles instead of hardcoded details.
- Add article search, pagination, author/publication information and related reading.
- Render sanitized article bodies with accessible headings, links and images.
- Keep drafts/future articles out of public URLs, search, feeds and sitemaps.
- Preserve existing useful guide copy as draft content if wanted; do not silently publish it as reviewed editorial content.

Acceptance: the owner can publish an article entirely through the panel; it has a stable public URL, appears in appropriate lists/search and is safely rendered.

### Milestone 5 — Imagery and visual finish

- Add relevant editorial imagery to selected article/brand areas; the owner has asked for this.
- Confirm image rights/provenance; store project assets locally or in the configured asset storage.
- Use responsive sizes, dimensions and suitable compression; lazy-load below-fold images.
- Keep the job search and listings readable instead of letting decorative images dominate.
- Finish empty/loading/error states, mobile navigation, long titles and keyboard interaction.
- Revisit the provisional logo with the owner later; keep all current source concepts.

Acceptance: images are real assets with documented origin, layout does not jump on load, text remains readable and mobile views have no horizontal overflow.

### Milestone 6 — SEO and indexing

- Add reusable per-page title, description, canonical and social-sharing metadata with useful defaults and admin overrides.
- Generate XML sitemaps from eligible published records; include accurate modification times.
- Add valid `JobPosting` data to individual open-job pages, not result/list pages. Reflect visible content and Google’s current requirements; do not fabricate salary, employer or remote eligibility fields.
- Add appropriate article/breadcrumb structured data to real content pages.
- Define canonical/noindex behaviour for internal searches and filter combinations; avoid indexing unlimited near-duplicate pages.
- Exclude admin, drafts and private previews through access controls and appropriate indexing directives. robots.txt is not access control.
- Implement slug redirects and expired-job metadata removal/updates.
- Keep local/staging noindex. Replace the current hardcoded directive with environment/publication-aware handling before launch and deliberately enable production indexing when ready.
- Connect Search Console and submit the sitemap after the real domain is live. Validate structured data against current official documentation.

Acceptance: HTML contains the actual content without JavaScript, published URLs have correct metadata, private/expired states are handled correctly, sitemap/schema validators pass. SEO eligibility does not guarantee rankings or rich results.

### Milestone 7 — Verification and deployment

- Test public visibility, deadlines/scheduling, admin permissions, uploads, content sanitization, slugs, search/pagination and SEO output.
- Test narrow mobile layouts, long content, keyboard focus, contrast, empty results, missing images and error pages.
- Choose Laravel-capable hosting and a domain. The Sites plugin's Worker hosting is not the chosen PHP/MySQL deployment target.
- Configure PHP/extensions, web root `app/public`, HTTPS, production MySQL, persistent file/object storage and secrets outside source control.
- Set production environment, debug off, secure cookies, correct APP_URL and production mail settings.
- Deploy built assets/dependencies from lockfiles. Run migrations deliberately with backups and a rollback plan; do not reset production data.
- Configure scheduler and workers when required. Ensure future publication/expiry remains correct even if a scheduler is delayed, through public query rules.
- Configure database AND upload backups, perform a restore test, set logging/monitoring and verify health checks.
- Remove sample content and development-only UI; audit indexing configuration and complete Search Console setup.

Acceptance: owner can publish end-to-end on the real domain, anonymous visitors can search/read/apply, private content is protected, backups restore, and deployment can be repeated safely.

## 8. Tests, checks and their limits

From `app/`:

```sh
php artisan config:clear
php artisan test
php artisan test --filter=DiscoveryPreviewTest
pnpm build
php artisan route:list
php artisan view:cache
```

Use `php artisan view:clear` when troubleshooting stale compiled views. Do not use cache-clearing commands indiscriminately in production.

Historical verification during implementation:

- **31 October 2026 (current):** full suite **30 tests, 80 assertions passed** against `plusthree_testing`; panel access tests prove guests redirect, non-admins receive 403, admins load the dashboard, and `/admin` 404s. Earlier the same day: 24 tests, 69 assertions after the content schema; 7 tests, 21 assertions after the redesign.
- 8 content-schema migrations applied to the development database; the test-database guard was positively verified to abort before touching a non-test database.
- After removing the location control: focused discovery suite **3 tests, 12 assertions passed** and assets built successfully.
- Homepage previously returned HTTP 200 and desktop/mobile screenshots were inspected.

`FoundationTest` uses `RefreshDatabase`: it rebuilds test tables. A fail-fast guard now lives in `app/tests/TestCase.php` and runs inside `setUpTraits()`, before any database trait can touch a schema, refusing any connection that is not MySQL `plusthree_testing`. It was positively verified by running a non-database test with `DB_DATABASE=plusthree`, which aborted with a `RuntimeException` instead of migrating. The guard is a safety net, not a substitute for reviewing which connection is resolved; separate development and testing database users remain a sensible extra layer.

Missing test coverage includes admin permissions, all real content CRUD/publication, uploads, expiry, scheduling, blog search, SEO and deployment. The two starter tests are weak smoke checks. Do not interpret their passing as completion of the product.

## 9. Troubleshooting and known technical debt

| Symptom / concern | What to check |
| --- | --- |
| Docker socket missing | Start Docker Desktop; inspect `docker context ls`; selected context was desktop-linux |
| MySQL connection refused | Container state, published port 3307 and local env; do not assume MySQL uses 3306 on the host |
| DB access denied after helper run | Helper rotated credentials; clear cached config/restart local processes using old credentials |
| Sandbox blocks local DB/network | Use the environment's approval mechanism; don't weaken MySQL or Docker security to bypass it |
| `node` or `pnpm` not found | Add the bundled runtime paths or use a normal Node/pnpm installation |
| pnpm esbuild script not run | `pnpm-workspace.yaml` permits esbuild; ensure Node is on PATH before `pnpm rebuild esbuild` |
| pnpm cache permission error | Installer may need approved access outside the workspace; don't overwrite system directories |
| Vite cannot find entry module | Run build from `app/` |
| Vite manifest missing | Build assets or start Vite; check `app/public/build/manifest.json` |
| CSS changes do not appear | Rebuild, inspect stale `public/hot` from a stopped Vite process, refresh browser |
| Old `location=` query visible | Obsolete URL state; current route ignores it; use the base URL |
| Blog/job public URL returns 404 | Those public routes are not implemented yet |
| `/admin` returns 404 | Expected — the panel lives at `/mdosi`; `/admin` is deliberately unused |
| Cannot sign in to `/mdosi` | Confirm the user exists and `is_admin` is true (`php artisan tinker`), and that the password hash matches; panel access requires the explicit admin flag, not just an account |
| Panel assets 404 or unstyled | Run `php artisan filament:assets`; assets are published to `public/css|js|fonts/filament` |
| Search returns only four example roles | Expected current preview source; connect real content in milestone 3 |
| `git status` fails | At this inspection, `.git` was an empty/read-only environment-managed directory, not a usable Git repo. Do not claim commits/backups exist; establish version control deliberately when permitted |

Other cleanup: split the monolithic homepage into Blade layouts/components as real pages arrive; remove unused CSS left from the typographic-art iteration; consolidate colour tokens; align Composer convenience scripts with pnpm; consider making the local DB helper less destructive to test environment customisation. `app/README.md` was Laravel's stock README before this handoff; use the root documentation.

## 10. Decisions still open

Hosting/domain, final logo, imagery choices, launch countries/audience emphasis, detailed taxonomy, salary fields, editorial voice/ownership, contact channel, and optional employer submissions need decisions as their milestones approach. None should block the next schema/admin work unnecessarily. Choose reversible internal details, record assumptions and avoid expanding initial scope.

Already decided: the publishing panel is served at `/mdosi` rather than the Filament default `/admin`.

## 11. Suggested next working session

1. Read this guide, AGENTS.md and SCHEMA.md; inspect current files for newer changes.
2. Confirm MySQL is running and the test guard still aborts on a non-test database.
3. Review the applied schema decisions in SCHEMA.md §13 — they are implemented, so changes now mean new migrations.
4. Implement Milestone 2: compatible Filament installation, `/mdosi` panel, explicit admin authorisation, and company/job/article resources.
5. Only after that, replace `config('preview.jobs')` with published database queries (Milestone 3).
6. Update docs with actual results after each milestone.

Keep future handoffs honest: distinguish implemented, tested, preview-only, planned and blocked. Never report a planned feature as shipped.
