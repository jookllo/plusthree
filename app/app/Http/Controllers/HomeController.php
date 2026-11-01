<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\JobListing;

class HomeController extends Controller
{
    public function __invoke()
    {
        $jobs = JobListing::query()
            ->open()
            ->with('company')
            ->latest('published_at')
            ->take(6)
            ->get();

        $articles = Article::query()
            ->published()
            ->with(['author', 'categories'])
            ->latest('published_at')
            ->take(3)
            ->get();

        return view('home', [
            'jobs' => $jobs,
            'articles' => $articles,
            'leadArticle' => $articles->first(),
            'moreArticles' => $articles->skip(1),
        ]);
    }
}
