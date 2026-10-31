# Homepage redesign

Reference reviewed: Hallmark SKILL.md, redesign, direction, patterns, gates, craft and imagery guidance from https://github.com/Nutlope/hallmark; Idealist homepage visually reviewed. ApplyNow timed out and could not be visually reviewed.

Preserved: Laravel homepage route/name, noindex during development, brand palette, uppercase tagline, local-only workflow, existing foundation tests.
Changed: promotional headline/cards replaced with compact job-search banner, ruled listings, editorial sidebar, existing Africa logo and supporting about/employer information. New sample search endpoint remains the homepage GET route; all examples explicitly labelled fictional. No live vacancies or employer claims.

Files: resources/views/home.blade.php, resources/css/app.css, resources/css/site.css, routes/web.php, config/preview.php, public/images/plusthree-logo.png, tests/Feature/DiscoveryPreviewTest.php (under app/).

Verification: Hallmark static sloplint --scope app returned 0 FAIL, 0 REFLEX, 0 WARN for site.css. The tool does not parse Blade as HTML, so this is a CSS static result, not full Hallmark certification. Browser rendering checked via the supported browser tool instead of the Hallmark browser automation. Desktop 1280x800 and mobile 390x844 reviewed. Search submitted by keyboard and confirmed one matching sample role. Application tests cover filtering, empty state, escaped input and the existing foundation.

Brand takes precedence over Hallmark defaults: supplied colours and uppercase tagline retained. This is a compact job-discovery screen, not a full-screen marketing hero. Draft guides are editable starter copy, not published database articles.
