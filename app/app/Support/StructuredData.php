<?php

namespace App\Support;

use App\Enums\EmploymentType;
use App\Enums\WorkMode;
use App\Models\Article;
use App\Models\JobListing;
use Illuminate\Support\Facades\Storage;

/**
 * schema.org JSON-LD. Kept in one place so the page markup, the sitemap and
 * any future feed cannot disagree about dates or URLs.
 */
final class StructuredData
{
    /**
     * @return array<string, mixed>
     */
    public static function website(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => config('seo.site_name'),
            'url' => route('home'),
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => route('jobs.index').'?q={search_term_string}',
                ],
                'query-input' => 'required name=search_term_string',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function article(Article $article): array
    {
        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $article->title,
            'description' => $article->metaDescription(),
            'datePublished' => $article->published_at?->toIso8601String(),
            'dateModified' => $article->updated_at?->toIso8601String(),
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => route('blog.show', $article->slug),
            ],
            'publisher' => self::publisher(),
        ];

        if ($article->author) {
            $data['author'] = [
                '@type' => 'Person',
                'name' => $article->author->name,
            ];
        }

        if ($article->featured_image_path) {
            $data['image'] = url(Storage::disk('public')->url($article->featured_image_path));
        }

        return array_filter($data, fn (mixed $value): bool => $value !== null);
    }

    /**
     * @return array<string, mixed>
     */
    public static function jobPosting(JobListing $job): array
    {
        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'JobPosting',
            'title' => $job->title,
            'description' => (string) ArticleBody::render($job->description),
            'datePosted' => $job->published_at?->toIso8601String(),
            'employmentType' => self::employmentType($job->employment_type),
            'hiringOrganization' => [
                '@type' => 'Organization',
                'name' => $job->company?->name,
            ],
            'url' => route('jobs.show', $job->slug),
            'directApply' => filled($job->application_url),
        ];

        if ($job->featured_image_path) {
            $data['image'] = url(Storage::disk('public')->url($job->featured_image_path));
        }

        if ($job->closing_at) {
            $data['validThrough'] = $job->closing_at->toIso8601String();
        }

        $address = array_filter([
            'addressLocality' => $job->location_city,
            'addressCountry' => $job->location_country,
        ]);

        if ($job->work_mode === WorkMode::Remote) {
            $data['jobLocationType'] = 'TELECOMMUTE';

            if ($address !== []) {
                $data['applicantLocationRequirements'] = [
                    '@type' => 'Country',
                    'name' => $job->location_country ?? $job->location_city,
                ];
            }
        } elseif ($address !== []) {
            $data['jobLocation'] = [
                '@type' => 'Place',
                'address' => ['@type' => 'PostalAddress'] + $address,
            ];
        }

        $data['hiringOrganization'] = array_filter($data['hiringOrganization']);

        return array_filter($data, fn (mixed $value): bool => $value !== null);
    }

    /**
     * @return array<string, mixed>
     */
    private static function publisher(): array
    {
        return [
            '@type' => 'Organization',
            'name' => config('seo.site_name'),
            'url' => route('home'),
            'logo' => [
                '@type' => 'ImageObject',
                'url' => url(config('seo.logo')),
            ],
        ];
    }

    private static function employmentType(EmploymentType $type): string
    {
        return match ($type) {
            EmploymentType::FullTime => 'FULL_TIME',
            EmploymentType::PartTime => 'PART_TIME',
            EmploymentType::Contract => 'CONTRACTOR',
            EmploymentType::Internship => 'INTERN',
            // schema.org has no dedicated value for these; the listing's own
            // copy explains what kind of opportunity it is.
            EmploymentType::Fellowship, EmploymentType::Other => 'OTHER',
        };
    }
}
