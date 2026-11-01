<?php

namespace App\Http\Controllers;

use App\Enums\EmploymentType;
use App\Models\JobListing;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class JobListingController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'type' => ['nullable', Rule::in(EmploymentType::values())],
        ]);

        $query = trim($filters['q'] ?? '');
        $type = $filters['type'] ?? null;

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
            ->latest('published_at')
            ->paginate(10)
            ->withQueryString();

        return view('jobs.index', [
            'jobs' => $jobs,
            'query' => $query,
            'type' => $type,
        ]);
    }

    public function show(string $slug)
    {
        $job = JobListing::query()
            ->published()
            ->with(['company', 'categories', 'tags'])
            ->where('slug', $slug)
            ->firstOrFail();

        return view('jobs.show', ['job' => $job]);
    }
}
