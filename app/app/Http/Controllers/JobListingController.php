<?php

namespace App\Http\Controllers;

use App\Enums\EmploymentType;
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
        $type = $landing?->value ?? ($filters['type'] ?? null);
        $closing = $filters['closing'] ?? null;

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
            ->when($type, fn ($builder) => $builder->where('employment_type', $type))
            ->when($closing === 'soon', fn ($builder) => $builder->closingSoon())
            ->latest('published_at')
            ->paginate(10)
            ->withQueryString();

        return view('jobs.index', [
            'jobs' => $jobs,
            'query' => $query,
            'type' => $type,
            'closing' => $closing,
            'landing' => $landing,
            'heading' => match ($landing) {
                EmploymentType::Fellowship => 'Fellowships',
                EmploymentType::Other => 'Other opportunities',
                default => 'Open roles',
            },
            'intro' => match ($landing) {
                EmploymentType::Fellowship => 'Fellowships, graduate programmes and leadership schemes open to African talent.',
                EmploymentType::Other => 'Scholarships, grants, volunteer roles and other opportunities that are not a standard job.',
                default => null,
            },
        ]);
    }

    public function show(string $slug)
    {
        $job = JobListing::query()
            ->publiclyReadable()
            ->with(['company', 'categories', 'tags'])
            ->where('slug', $slug)
            ->firstOrFail();

        return view('jobs.show', ['job' => $job]);
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

        return view('jobs.show', ['job' => $job, 'preview' => true]);
    }
}
