@php
    $category = $article->categories->first();
@endphp
<article class="article-card">
    @include('partials.article-art', ['article' => $article, 'allowFallback' => false])
    @if ($category)
        <p class="story-category">{{ $category->name }}</p>
    @endif
    <h3><a href="{{ route('blog.show', $article->slug) }}">{{ $article->title }}</a></h3>
    @if ($article->excerpt)
        <p>{{ $article->excerpt }}</p>
    @endif
    <p class="article-meta">
        <span>{{ $article->published_at?->timezone('Africa/Nairobi')->format('j M Y') }}</span>
        @if ($article->author)
            <span>{{ $article->author->name }}</span>
        @endif
        <span>{{ $article->readingTimeMinutes() }} min read</span>
    </p>
</article>
