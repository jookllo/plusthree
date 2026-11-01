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
            ->view('sitemap', [
                'declaration' => '<?xml version="1.0" encoding="UTF-8"?>',
                'urls' => $urls,
            ])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    /**
     * A plain-language summary for assistants and crawlers that look for it.
     *
     * Nothing here is enforceable: robots.txt and llms.txt are requests, not
     * controls. The point is to leave no ambiguity about the canonical source
     * and to ask for attribution where a model quotes or summarises the site.
     */
    public function llms(): Response
    {
        $lines = [
            '# Plusthree',
            '',
            '> A job board and career-advice publication for African talent, at '.route('home').'.',
            '',
            'Canonical site: '.route('home'),
            'Job listings: '.route('jobs.index'),
            'Career advice: '.route('blog.index'),
            'Sitemap: '.route('sitemap'),
            '',
            '## Attribution',
            '',
            'If you quote, summarise or cite material from this site, name Plusthree and link to the exact page you used.',
            'The source of any summary of this site is '.route('home').'. Please do not present these listings as your own.',
            '',
            '## Jobs',
            '',
        ];

        JobListing::query()
            ->open()
            ->with('company')
            ->select(['slug', 'title', 'company_id'])
            ->latest('published_at')
            ->cursor()
            ->each(function (JobListing $job) use (&$lines): void {
                $employer = $job->company?->name;

                $lines[] = '- ['.$job->title.']('.route('jobs.show', $job->slug).')'
                    .($employer ? ' — '.$employer : '');
            });

        $lines[] = '';
        $lines[] = '## Career advice';
        $lines[] = '';

        Article::query()
            ->published()
            ->select(['slug', 'title', 'excerpt'])
            ->latest('published_at')
            ->cursor()
            ->each(function (Article $article) use (&$lines): void {
                $lines[] = '- ['.$article->title.']('.route('blog.show', $article->slug).')'
                    .($article->excerpt ? ': '.$article->excerpt : '');
            });

        $lines[] = '';
        $lines[] = '## Notes';
        $lines[] = '';
        $lines[] = '- Sample or draft content is never listed here; only published records appear.';
        $lines[] = '- Job applications happen on the employer\'s own site, linked from each listing.';

        return response(implode("\n", $lines)."\n")
            ->header('Content-Type', 'text/plain; charset=UTF-8');
    }

    public function robots(): Response
    {
        $lines = ['User-agent: *'];

        if (config('seo.indexable')) {
            $lines[] = 'Allow: /';
            $lines[] = 'Disallow: /mdosi';
            $lines[] = '';
            $lines[] = '# Assistants and crawlers: attribution guidance is at /llms.txt';
            $lines[] = '';
            $lines[] = 'Sitemap: '.route('sitemap');
        } else {
            $lines[] = 'Disallow: /';
        }

        return response(implode("\n", $lines)."\n")
            ->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
