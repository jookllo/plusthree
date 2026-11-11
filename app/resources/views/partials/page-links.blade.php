{{--
    Crawlable landing-page links: one list of sectors or countries with counts.
    Rendered on the board and on every landing page so the pages are reachable
    without relying on the sitemap.
--}}
@if (count($pages) > 0)
    <nav class="page-links" aria-label="{{ $label }}">
        <h2>{{ $label }}</h2>
        <ul>
            @foreach ($pages as $page)
                <li>
                    <a href="{{ route($routeName, $page['slug']) }}">
                        {{ $page['name'] }}
                        <span>{{ $page['total'] }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>
@endif
