@php
    $allowFallback = $allowFallback ?? true;
@endphp
@if ($article->featured_image_path)
    <div class="article-media">
        <img class="article-image"
             src="{{ Storage::disk('public')->url($article->featured_image_path) }}"
             alt="{{ $article->featured_image_alt }}"
             width="1600"
             height="900"
             loading="lazy">
    </div>
@elseif ($allowFallback)
    @php
        $variant = $article->artVariant();
        $palette = intdiv($variant - 1, 4) + 1;
        $motif = (($variant - 1) % 4) + 1;
    @endphp
    <div class="article-media">
        <div class="article-art art-p{{ $palette }} art-m{{ $motif }}" aria-hidden="true"></div>
    </div>
@endif
