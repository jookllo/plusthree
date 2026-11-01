<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Models\JobListing;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $urls = [
            ['loc' => route('home'), 'lastmod' => null],
            ['loc' => route('jobs.index'), 'lastmod' => null],
            ['loc' => route('blog.index'), 'lastmod' => null],
        ];

        JobListing::query()
            ->open()
            ->select(['slug', 'updated_at'])
            ->latest('updated_at')
            ->cursor()
            ->each(function (JobListing $job) use (&$urls): void {
                $urls[] = ['loc' => route('jobs.show', $job->slug), 'lastmod' => $job->updated_at];
            });

        Article::query()
            ->published()
            ->select(['slug', 'updated_at'])
            ->latest('published_at')
            ->cursor()
            ->each(function (Article $article) use (&$urls): void {
                $urls[] = ['loc' => route('blog.show', $article->slug), 'lastmod' => $article->updated_at];
            });

        Category::query()
            ->where('kind', Category::KIND_ARTICLE)
            ->whereHas('articles', fn ($query) => $query->published())
            ->orderBy('slug')
            ->cursor()
            ->each(function (Category $category) use (&$urls): void {
                $urls[] = ['loc' => route('blog.category', $category->slug), 'lastmod' => $category->updated_at];
            });

        return response()
            ->view('sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function robots(): Response
    {
        $lines = ['User-agent: *'];

        if (config('seo.indexable')) {
            $lines[] = 'Allow: /';
            $lines[] = 'Disallow: /mdosi';
            $lines[] = '';
            $lines[] = 'Sitemap: '.route('sitemap');
        } else {
            $lines[] = 'Disallow: /';
        }

        return response(implode("\n", $lines)."\n")
            ->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
