<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Display name to ISO 3166-1 alpha-2 conversion.
 *
 * Listings store the country as an editor typed it ("Kenya", "Côte d'Ivoire").
 * Schema.org wants the two-letter code in `addressCountry`, and Google's job
 * posting guidance is explicit about it, so the display name is translated
 * here rather than in every template.
 *
 * The list is deliberately generous rather than exhaustive: African countries
 * first, then the destinations the diaspora applies from. Anything unknown is
 * returned unchanged, which is what the markup did before this class existed.
 */
final class Countries
{
    /**
     * Lower-case, diacritic-free names mapped to codes.
     *
     * @var array<string, string>
     */
    private const CODES = [
        // Africa
        'algeria' => 'DZ',
        'angola' => 'AO',
        'benin' => 'BJ',
        'botswana' => 'BW',
        'burkina faso' => 'BF',
        'burundi' => 'BI',
        'cameroon' => 'CM',
        'cape verde' => 'CV',
        'central african republic' => 'CF',
        'chad' => 'TD',
        'comoros' => 'KM',
        'congo' => 'CG',
        'cote d ivoire' => 'CI',
        'cote divoire' => 'CI',
        'democratic republic of the congo' => 'CD',
        'djibouti' => 'DJ',
        'drc' => 'CD',
        'egypt' => 'EG',
        'equatorial guinea' => 'GQ',
        'eritrea' => 'ER',
        'eswatini' => 'SZ',
        'ethiopia' => 'ET',
        'gabon' => 'GA',
        'gambia' => 'GM',
        'ghana' => 'GH',
        'guinea' => 'GN',
        'guinea bissau' => 'GW',
        'ivory coast' => 'CI',
        'kenya' => 'KE',
        'lesotho' => 'LS',
        'liberia' => 'LR',
        'libya' => 'LY',
        'madagascar' => 'MG',
        'malawi' => 'MW',
        'mali' => 'ML',
        'mauritania' => 'MR',
        'mauritius' => 'MU',
        'morocco' => 'MA',
        'mozambique' => 'MZ',
        'namibia' => 'NA',
        'niger' => 'NE',
        'nigeria' => 'NG',
        'rwanda' => 'RW',
        'sao tome and principe' => 'ST',
        'senegal' => 'SN',
        'seychelles' => 'SC',
        'sierra leone' => 'SL',
        'somalia' => 'SO',
        'south africa' => 'ZA',
        'south sudan' => 'SS',
        'sudan' => 'SD',
        'swaziland' => 'SZ',
        'tanzania' => 'TZ',
        'togo' => 'TG',
        'tunisia' => 'TN',
        'uganda' => 'UG',
        'zambia' => 'ZM',
        'zimbabwe' => 'ZW',

        // Common destinations for African talent in the diaspora
        'argentina' => 'AR',
        'australia' => 'AU',
        'austria' => 'AT',
        'belgium' => 'BE',
        'brazil' => 'BR',
        'canada' => 'CA',
        'china' => 'CN',
        'denmark' => 'DK',
        'england' => 'GB',
        'finland' => 'FI',
        'france' => 'FR',
        'germany' => 'DE',
        'india' => 'IN',
        'ireland' => 'IE',
        'italy' => 'IT',
        'japan' => 'JP',
        'mexico' => 'MX',
        'netherlands' => 'NL',
        'new zealand' => 'NZ',
        'norway' => 'NO',
        'poland' => 'PL',
        'portugal' => 'PT',
        'qatar' => 'QA',
        'saudi arabia' => 'SA',
        'spain' => 'ES',
        'sweden' => 'SE',
        'switzerland' => 'CH',
        'turkey' => 'TR',
        'united arab emirates' => 'AE',
        'united kingdom' => 'GB',
        'united states' => 'US',
        'united states of america' => 'US',
        'usa' => 'US',
        'uae' => 'AE',
        'uk' => 'GB',
    ];

    /**
     * The ISO 3166-1 alpha-2 code for a country as an editor wrote it, or the
     * value unchanged when it cannot be recognised.
     */
    public static function isoCode(?string $country): ?string
    {
        if (blank($country)) {
            return null;
        }

        $value = trim($country);

        // Already a code, or a code with stray punctuation.
        if (preg_match('/^[A-Za-z]{2}$/', $value) === 1) {
            return strtoupper($value);
        }

        $key = Str::of($value)->ascii()->lower()->replaceMatches('/[^a-z0-9]+/', ' ')->trim()->value();

        return self::CODES[$key] ?? $value;
    }
}
