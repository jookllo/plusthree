<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Event;
use App\Models\JobListing;

class HomeController extends Controller
{
    public function __invoke()
    {
        $jobs = JobListing::query()
            ->open()
            ->with('company')
            ->latest('published_at')
            ->paginate(6)
            ->withQueryString();

        // The sidebar is split: events on top, then the career edit cut to its
        // lead story and one more, so neither block crowds the other.
        $articles = Article::query()
            ->published()
            ->with(['author', 'categories'])
            ->latest('published_at')
            ->take(2)
            ->get();

        return view('home', [
            'jobs' => $jobs,
            'events' => Event::query()->upcoming()->take(3)->get(),
            'articles' => $articles,
            'leadArticle' => $articles->first(),
            'moreArticles' => $articles->skip(1),
            'closingSoonCount' => JobListing::closingSoonTotal(),
        ]);
    }
}
