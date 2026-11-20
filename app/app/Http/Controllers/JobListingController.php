<?php

namespace App\Http\Controllers;

use App\Enums\EmploymentType;
use App\Models\Category;
use App\Models\JobListing;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class JobListingController extends Controller
{
    public function index(Request $request): View
    {
        return $this->listing($request);
    }

    public function fellowships(Request $request): View
    {
        return $this->listing($request, EmploymentType::Fellowship);
    }

    public function otherOpportunities(Request $request): View
    {
        return $this->listing($request, EmploymentType::Other);
    }

    /**
     * The board, and the two type-specific landing pages, share one query and
     * one view. A landing page fixes the type and replaces the filter select
     * with its own heading and explanation.
     */
    private function listing(Request $request, ?EmploymentType $landing = null): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'type' => ['nullable', Rule::in(EmploymentType::values())],
            'closing' => ['nullable', Rule::in(['soon'])],
        ]);

        $query = trim($filters['q'] ?? '');
        $requested = isset($filters['type']) ? EmploymentType::from($filters['type']) : null;
        $closing = $filters['closing'] ?? null;

        // A landing page covers one or more types. ?type= may narrow the
        // Other opportunities page to one of its kinds, but it can never
        // pull a role from outside that page into it.
        $landingTypes = $landing?->landingTypes() ?? [];
        $narrowed = $requested && in_array($requested, $landingTypes, true) ? $requested : null;
        $types = $landing
            ? ($narrowed ? [$narrowed] : $landingTypes)
            : ($requested ? [$requested] : []);

        // The type the view marks as selected in its filter and chips. A
        // single-type landing page (Fellowships) keeps its own type so the
        // urgency link stays inside it; the Other page only selects a chip
        // when one was asked for.
        $type = $landing
            ? ($narrowed ?? (count($landingTypes) === 1 ? $landingTypes[0] : null))
            : $requested;

        $jobs = JobListing::query()
            ->open()
            ->with('company')
            ->when($query !== '', function ($builder) use ($query): void {
                $like = '%'.$query.'%';

                $builder->where(function ($builder) use ($like): void {
                    $builder->where('title', 'like', $like)
                        ->orWhere('description', 'like', $like)
                        ->orWhereHas('company', fn ($company) => $company->where('name', 'like', $like));
                });
            })
            ->when($types !== [], fn ($builder) => $builder->whereIn(
                'employment_type',
                array_map(fn (EmploymentType $case): string => $case->value, $types),
            ))
            // Newest first, so a role published this morning is visible without
            // scrolling past everything with a nearer deadline. The deadline
            // view is one click away and orders by what closes soonest.
            ->when(
                $closing === 'soon',
                fn ($builder) => $builder->closingSoon()->orderBy('closing_at'),
                fn ($builder) => $builder->latest('published_at'),
            )
            ->paginate(10)
            ->withQueryString();

        return view('jobs.index', [
            'jobs' => $jobs,
            'query' => $query,
            'type' => $type?->value,
            'closing' => $closing,
            'landing' => $landing,
            'typeNav' => count($landingTypes) > 1 ? $landingTypes : [],
            'heading' => match ($landing) {
                EmploymentType::Fellowship => 'Fellowships',
                EmploymentType::Other => 'Other opportunities',
                default => 'Open roles',
            },
            'intro' => match ($landing) {
                EmploymentType::Fellowship => 'Fellowships, graduate programmes and leadership schemes open to African talent.',
                EmploymentType::Other => 'Consultancy calls, funding rounds, board seats, scholarships and volunteer roles — opportunities that are not a standard job.',
                default => null,
            },
            'closingSoonCount' => JobListing::closingSoonTotal($types),
            'sectorPages' => JobListing::sectorPages(),
            'countryPages' => JobListing::countryPages(),
        ]);
    }

    /**
     * One sector, such as social impact or monitoring and evaluation. These are
     * the pages that can rank for "social impact jobs Africa"; the search form
     * has no sector filter, and filtered URLs stay noindex.
     */
    public function sector(string $slug): View
    {
        $category = Category::query()
            ->where('kind', Category::KIND_JOB)
            ->where('slug', $slug)
            ->firstOrFail();

        $jobs = JobListing::query()
            ->open()
            ->with('company')
            ->whereHas('categories', fn ($query) => $query->whereKey($category->getKey()))
            ->latest('published_at')
            ->paginate(10)
            ->withQueryString();

        return view('jobs.landing', [
            'heading' => $category->name.' jobs',
            'intro' => 'Open '.mb_strtolower($category->name).' roles for African talent and the diaspora.',
            'jobs' => $jobs,
            'indexable' => $jobs->total() >= JobListing::LANDING_PAGE_MIN_ROLES,
            'related' => JobListing::sectorPages(),
            'relatedLabel' => 'Browse another sector',
            'relatedRoute' => 'jobs.sector',
        ]);
    }

    /**
     * One country. Slugs are resolved against the countries that actually have
     * open roles, so a stale or invented slug 404s instead of rendering an
     * empty page for search engines to index.
     */
    public function country(string $country): View
    {
        $match = collect(JobListing::countryPages())->firstWhere('slug', $country);

        abort_if($match === null, 404);

        $jobs = JobListing::query()
            ->open()
            ->with('company')
            ->where('location_country', $match['name'])
            ->latest('published_at')
            ->paginate(10)
            ->withQueryString();

        return view('jobs.landing', [
            'heading' => 'Jobs in '.$match['name'],
            'intro' => 'Open roles based in '.$match['name'].', plus fellowships and other opportunities.',
            'jobs' => $jobs,
            'indexable' => $jobs->total() >= JobListing::LANDING_PAGE_MIN_ROLES,
            'related' => JobListing::countryPages(),
            'relatedLabel' => 'Browse another country',
            'relatedRoute' => 'jobs.country',
        ]);
    }

    public function show(string $slug)
    {
        $job = JobListing::query()
            ->publiclyReadable()
            ->with(['company', 'categories', 'tags'])
            ->where('slug', $slug)
            ->firstOrFail();

        $similar = $job->similarRoles();

        return view('jobs.show', [
            'job' => $job,
            'similar' => $similar,
            'similarAreRelated' => $job->similarRolesAreRelated($similar),
        ]);
    }

    /**
     * Drafts, future-dated and archived listings, reachable only through a
     * short-lived signed URL generated in the admin panel.
     */
    public function preview(string $slug)
    {
        $job = JobListing::query()
            ->with(['company', 'categories', 'tags'])
            ->where('slug', $slug)
            ->firstOrFail();

        $similar = $job->similarRoles();

        return view('jobs.show', [
            'job' => $job,
            'preview' => true,
            'similar' => $similar,
            'similarAreRelated' => $job->similarRolesAreRelated($similar),
        ]);
    }
}
