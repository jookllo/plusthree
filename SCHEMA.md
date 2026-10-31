# Plusthree content schema — design proposal

**Status: implemented 31 October 2026** — migrations, models, enums, factories, scopes and tests are in place and green. No admin UI or public data-backed pages exist yet; the homepage still renders `config/preview.php`. Decisions marked **D1–D6** remain listed for the record.

Environment facts this design assumes, verified 31 October 2026:

- MySQL 8.4.11, `utf8mb4` / `utf8mb4_unicode_ci`, strict mode, existing container `eden-test-mysql` on `127.0.0.1:3307`.
- Application timezone is `UTC` (`app/config/app.php`); owner works in `Africa/Nairobi`.
- Laravel's existing `jobs` table is the **queue** table. Vacancies use `job_listings`. The queue migration is never modified.

## 1. Tables

| Table | Purpose |
| --- | --- |
| `companies` | Employer records referenced by listings |
| `job_listings` | Vacancies (not `jobs`) |
| `articles` | Career blog posts |
| `categories` | Shared taxonomy, scoped by `kind` (`job` / `article`) — **D1** |
| `tags` | Free-form labels shared across content |
| `categorizables` / `taggables` | Polymorphic pivots |
| `users` | Existing table; gains `is_admin` — **D4** |

## 2. Columns

### `companies`

| Column | Type | Notes |
| --- | --- | --- |
| `id` | bigint unsigned PK | |
| `name` | varchar(160) not null | |
| `slug` | varchar(180) not null | unique |
| `website` | varchar(255) null | validated `http`/`https` on input |
| `logo_path` | varchar(255) null | storage path only; no upload yet |
| `description` | text null | plain text, escaped on output |
| `created_at` / `updated_at` | timestamp null | |

### `job_listings`

| Column | Type | Notes |
| --- | --- | --- |
| `id` | bigint unsigned PK | |
| `company_id` | bigint unsigned FK | → `companies.id`, **restrict on delete** |
| `title` | varchar(180) not null | |
| `slug` | varchar(200) not null | unique |
| `description` | longtext not null | |
| `requirements` | longtext null | |
| `employment_type` | varchar(20) not null | `full_time`, `contract`, `internship` |
| `work_mode` | varchar(20) not null | `on_site`, `hybrid`, `remote` |
| `location_city` | varchar(120) null | |
| `location_country` | varchar(80) null | country name; ISO-2 code can be added later |
| `application_url` | varchar(2048) null | validated scheme; **D5** |
| `application_email` | varchar(255) null | **D5** |
| `status` | varchar(20) not null | `draft`, `published`, `archived` |
| `published_at` | timestamp null | UTC |
| `closing_at` | timestamp null | UTC; null = no deadline |
| `created_by` / `updated_by` | bigint unsigned null FK | → `users.id`, null on delete |
| `created_at` / `updated_at` | timestamp null | |

#### Location, remote and dates

- **Location is stored, not searched.** `location_city` and `location_country` describe where a role is based. The owner explicitly removed the "Where?" search control; storing and displaying location does **not** restore that field. Location becomes filterable only if a later design decision reintroduces it.
- **Remote is a work mode, not a second location.** `work_mode` (`on_site`, `hybrid`, `remote`) is the single source of truth; a derived `scopeRemote()` serves future filtering. No `is_remote` boolean, because two representations drift apart. A remote role may still be country-restricted, which is why `location_country` stays meaningful for remote rows.
- **Four dates, three purposes.** `created_at` is when the record was written (audit only, never displayed). `published_at` is when the vacancy went live and is the public "posted" date — this is what listings sort and display by. `updated_at` supports "last edited" and change detection. `closing_at` is the deadline, `null` meaning open-ended. Showing `created_at` publicly would misdate anything drafted ahead of publication, so public templates use `published_at` only.

### `articles`

| Column | Type | Notes |
| --- | --- | --- |
| `id` | bigint unsigned PK | |
| `author_id` | bigint unsigned null FK | → `users.id`, null on delete (content survives) |
| `title` | varchar(200) not null | |
| `slug` | varchar(200) not null | unique |
| `excerpt` | varchar(320) null | |
| `body` | longtext not null | rich HTML, sanitised before rendering |
| `featured_image_path` | varchar(255) null | unused until Milestone 5 |
| `featured_image_alt` | varchar(255) null | required if a path is set |
| `status` | varchar(20) not null | same three states |
| `published_at` | timestamp null | UTC |
| `seo_title` | varchar(255) null | falls back to `title` |
| `seo_description` | varchar(320) null | falls back to `excerpt` |
| `created_at` / `updated_at` | timestamp null | |

#### Article fields not stored as columns

- **Reading time** — derived from `body` word count at render; storing it invites staleness.
- **Related articles** — derived from shared categories, newest first; no join table yet.
- **Author display** — `author_id` plus a display name resolved through the relation; no separate profile fields until the owner asks for bylines with bios.
- **Last reviewed / updated** — `updated_at` rendered as "Updated 3 Nov 2026" only where it differs meaningfully from `published_at`.
- **Featured lead story** — the career column needs one obvious lead. Options: newest published article, or an explicit `is_featured` boolean (indexed, one per slot at a time). Recommendation: start with newest published, add the flag only when the owner wants manual control.

Deferred deliberately: comments, likes/reactions, newsletter capture, canonical overrides for syndicated content, and per-article hero video.

### `categories`, `tags`, pivots

- `categories`: `id`, `kind` varchar(10) (`job`/`article`), `name` varchar(80), `slug` varchar(100), timestamps. Unique `(kind, slug)`.
- `tags`: `id`, `name` varchar(60), `slug` varchar(80) unique, timestamps.
- `categorizables`: `category_id`, `categorizable_type`, `categorizable_id`, unique `(category_id, categorizable_type, categorizable_id)`, index `(categorizable_type, categorizable_id)`.
- `taggables`: same shape for `tag_id`.

### `users` (alter only)

Add `is_admin` boolean, default `false`, indexed. No other change to the starter table.

## 3. Enums

PHP backed enums, stored as `varchar` with Eloquent casts — not MySQL `ENUM`, because altering a MySQL enum later is a table rebuild and Laravel portability suffers.

- `EmploymentType`: `full_time` (label "Full-time"), `contract`, `internship` — matches the existing UI filter values.
- `WorkMode`: `on_site`, `hybrid`, `remote`.
- `PublicationStatus`: `draft`, `published`, `archived`.

Labels live in one place so the Blade filter links and validation rules stay in step.

## 4. Indexes

| Index | Why |
| --- | --- |
| `companies.slug` unique | routing |
| `job_listings.slug` unique | routing |
| `job_listings (status, published_at)` | public listing query |
| `job_listings (closing_at)` | expiry filtering |
| `job_listings (company_id, status)` | company pages, N+1 avoidance |
| `job_listings (employment_type)`, `(work_mode)` | existing filters |
| `articles.slug` unique, `(status, published_at)` | blog list/detail |
| `categories (kind, slug)` unique | taxonomy pages |
| `users.is_admin` | admin checks |

Keyword search stays `LIKE '%term%'`, which cannot use a B-tree index on a leading wildcard. A MySQL `FULLTEXT` index is deferred until search requirements are proven (Milestone 3).

## 5. Visibility rules — one source of truth

Defined once as Eloquent scopes and reused by lists, details, search, sitemap and structured data:

```
Article::scopePublished()      status = published AND published_at <= now()
JobListing::scopePublished()   status = published AND published_at <= now()
JobListing::scopeOpen()        scopePublished() AND (closing_at IS NULL OR closing_at > now())
```

- Lists, search, filters and sitemaps use `open()`.
- Detail pages resolve via `published()`, so a closed job still renders with a "no longer accepting applications" state and application links disabled — **D2**.
- Expiry is therefore derived from data at query time and never depends on a scheduler having run on time.
- Boundary rule: `closing_at` is exclusive — a job is closed at the instant of `closing_at`.
- No query may read `job_listings` or `articles` publicly without going through a scope. Enforced by review and tests, since Laravel has no built-in guard.

## 6. Time handling

- All timestamps persisted in UTC; DB session timezone UTC; comparisons in UTC.
- Display and admin input converted to `Africa/Nairobi` at the edges.
- Tests use `Carbon::setTestNow()` in UTC and assert Nairobi rendering separately, so a host timezone change cannot silently shift publication dates.

## 7. Slugs and redirects

- Slug generated from the title at creation, unique per table.
- Slug is editable **before first publication only**; afterwards it is immutable until a `slug_history` + 301 redirect mechanism exists (Milestone 6). This avoids publishing broken links with no redirect story.

## 8. Delete behaviour

| Relationship | Rule | Reason |
| --- | --- | --- |
| `job_listings.company_id` | restrict | never silently delete live vacancies with an employer |
| `articles.author_id` | null on delete | editorial content must outlive a user account |
| `job_listings.created_by` / `updated_by` | null on delete | audit trail, not ownership |
| pivot rows | cascade | categories/tags are not content |
| `archived` status | preferred over deletion | **D6** |

Soft deletes are **not** used initially: `archived` covers intent, and soft deletes complicate unique slugs and public scopes for little gain at this size — **D6**.

## 9. Application method

`application_url` (validated `http`/`https` only, no `javascript:` or other schemes) and/or `application_email`. At least one is required for a listing to publish, enforced in a form request and a model-level publishing check. No CV storage, no tracking, per the release scope.

## 10. Testing plan

Database tests run only against `plusthree_testing` (via `.env.testing`), with a fail-fast guard added before any destructive test operation:

1. Visibility matrix per model: draft, future-dated, published, archived, expired — none leaked by `open()`/`published()`.
2. Boundary: `closing_at` exactly equal to now → closed.
3. Relationships: company → listings; category/tag attach, detach and per-kind uniqueness.
4. Slug uniqueness per table; category slug unique per `kind`, repeatable across kinds.
5. Delete rules: restricted company delete, author null-on-delete.
6. Timezone: UTC storage round-trip with Nairobi display.
7. Factories produce valid, publishable records.

## 11. Migration plan

1. Create migrations in dependency order: `companies`, `categories`, `tags`, `articles`, `job_listings`, pivots, `users.is_admin`.
2. Apply to `plusthree_testing` through the test suite first; review the SQL, then migrate the development database deliberately.
3. All migrations additive. `down()` drops only new tables/columns; existing starter tables (`users`, `cache`, `jobs`, `sessions`, `password_reset_tokens`) are untouched.
4. Development seeders stay separate from production data and remain clearly fictional.

## 12. Non-goals for this step

No Filament, no Livewire, no admin UI, no public route changes, no replacement of `config/preview.php`, no blog pages, no uploads, no search rewrite. The homepage keeps rendering the four fictional preview roles until the Milestone 3 slice.

## 13. Decisions needed

| ID | Decision | Recommendation |
| --- | --- | --- |
| D1 | Shared taxonomy with `kind`, or separate job/article category tables | Shared `categories` with `kind` |
| D2 | Closed job pages: stay readable with a closed state, or 404 | Stay readable, applications disabled |
| D3 | Salary fields now or later | Later; nothing invented |
| D4 | Admin access: `is_admin` boolean, or Filament roles/permissions now | `is_admin` now, upgrades cleanly |
| D5 | Application method: URL only, or URL + email with one required | Both, one required |
| D6 | `archived` status only, or soft deletes as well | `archived` only |

### Decisions recorded

- **Location** — stored and displayed on listing rows only; not searchable or filterable for now. Does not reinstate the removed "Where?" control.
- **Blog lead story** — newest published article wins automatically; no `is_featured` column until manual curation is requested.

Effort estimate: one working session for migrations, models, factories and the visibility/relationship tests, with the test database staying isolated throughout.

## 14. Implementation record — 31 October 2026

Applied with the recommended defaults in §13; D1–D6 can still be changed before the admin milestone, but changing them now means editing migrations rather than intent.

Files added:

- `app/app/Enums/` — `EmploymentType`, `WorkMode`, `PublicationStatus` (backed enums with `label()` and `values()`).
- `app/app/Support/` — `HasUniqueSlug`, `Publishable`, `NairobiTime`.
- `app/app/Models/` — `Company`, `JobListing`, `Article`, `Category`, `Tag`; `User` gained `is_admin` casting, `isAdmin()`, `articles()` and `jobListings()`.
- `app/database/migrations/2026_10_31_000001…000008` — companies, categories, tags, `users.is_admin`, articles, job_listings, categorizables, taggables.
- `app/database/factories/` — `CompanyFactory`, `JobListingFactory`, `ArticleFactory`, `CategoryFactory`, `TagFactory`, with `published()`, `open()`, `closed()`, `remote()`, `archived()` and `scheduled()` states.
- `app/tests/Feature/ContentSchemaTest.php`, `app/tests/Feature/VisibilityScopeTest.php`.

Deviations and implementation notes:

- Pivots are polymorphic (`categorizables`, `taggables`) with explicitly named unique indexes, because the generated index names would exceed MySQL's 64-character identifier limit.
- `is_admin` is deliberately **not** mass assignable; privilege changes must be explicit.
- `NairobiTime::toUtc()` performs the boundary conversion; models store UTC and never guess a timezone from a naive string.
- `application_url` is `varchar(2048)` with an http/https allow-list in `JobListing::safeApplicationUrl()`.
- The panel hides `published_at`: setting status to Published stamps the current time on save. `closing_at` is chosen as a date and stored as the last moment of that day in Africa/Nairobi, converted to UTC.

Verification actually run:

- `php artisan test` → **24 tests, 69 assertions, all passing** (14.5s) against `plusthree_testing`.
- `php artisan migrate` → 8 migrations applied to the development database as batch 2; starter tables (`users`, `cache`, `jobs`, `sessions`, `password_reset_tokens`) untouched.
- Guard positive control: running a non-database test with `DB_DATABASE=plusthree` aborts with a `RuntimeException` before any table is touched.

Not done in this step: Filament, Livewire, admin UI, `/jobs` or `/blog` routes, replacing `config/preview.php`, uploads, search rewrite, SEO metadata beyond the article fallback accessors.

