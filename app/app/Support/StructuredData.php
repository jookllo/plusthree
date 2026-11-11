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
        $company = $job->company;

        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'JobPosting',
            'title' => $job->title,
            'description' => (string) ArticleBody::render($job->description),
            'datePosted' => $job->published_at?->toIso8601String(),
            'employmentType' => self::employmentType($job->employment_type),
            'identifier' => [
                '@type' => 'PropertyValue',
                'name' => config('seo.site_name'),
                'value' => (string) $job->getKey(),
            ],
            'hiringOrganization' => array_filter([
                '@type' => 'Organization',
                'name' => $company?->name,
                'sameAs' => filled($company?->website) ? $company->website : null,
                'logo' => $company?->logo_path
                    ? url(Storage::disk('public')->url($company->logo_path))
                    : null,
            ]),
            'url' => route('jobs.show', $job->slug),
            // An email application is still a direct route to the employer,
            // which is how Google defines this property.
            'directApply' => filled($job->application_url) || filled($job->application_email),
        ];

        if ($job->featured_image_path) {
            $data['image'] = url(Storage::disk('public')->url($job->featured_image_path));
        }

        if ($job->closing_at) {
            $data['validThrough'] = $job->closing_at->toIso8601String();
        }

        $address = array_filter([
            'addressLocality' => $job->location_city,
            // Google requires the ISO 3166-1 alpha-2 code here, not the name.
            'addressCountry' => Countries::isoCode($job->location_country),
        ]);

        if ($job->work_mode === WorkMode::Remote) {
            $data['jobLocationType'] = 'TELECOMMUTE';

            // A 100% remote posting must name at least one country applicants
            // can work from, otherwise Google cannot place it.
            if (filled($job->location_country)) {
                $data['applicantLocationRequirements'] = [
                    '@type' => 'Country',
                    'name' => $job->location_country,
                ];
            }
        } elseif ($address !== []) {
            $data['jobLocation'] = [
                '@type' => 'Place',
                'address' => ['@type' => 'PostalAddress'] + $address,
            ];
        }

        return array_filter($data, fn (mixed $value): bool => $value !== null);
    }

    /**
     * The publisher as an entity in its own right, so the address in the footer
     * is machine-readable too.
     *
     * @return array<string, mixed>
     */
    public static function organization(): array
    {
        $email = config('seo.contact_email');

        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            '@id' => route('home').'#organization',
            'name' => config('seo.site_name'),
            'url' => route('home'),
            'logo' => [
                '@type' => 'ImageObject',
                'url' => url(config('seo.logo')),
            ],
            'email' => $email,
            'contactPoint' => filled($email) ? [[
                '@type' => 'ContactPoint',
                'contactType' => 'customer support',
                'email' => $email,
                'availableLanguage' => 'English',
            ]] : null,
            'sameAs' => config('seo.same_as') ?: null,
        ]);
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
