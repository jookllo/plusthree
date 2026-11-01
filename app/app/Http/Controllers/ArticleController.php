<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;

class ArticleController extends Controller
{
    public function index()
    {
        $articles = Article::query()
            ->published()
            ->with(['author', 'categories'])
            ->latest('published_at')
            ->paginate(9)
            ->withQueryString();

        $categories = Category::query()
            ->where('kind', Category::KIND_ARTICLE)
            ->withCount(['articles' => fn ($query) => $query->published()])
            ->orderBy('name')
            ->get();

        return view('blog.index', [
            'articles' => $articles,
            'categories' => $categories,
        ]);
    }

    public function show(string $slug)
    {
        $article = Article::query()
            ->published()
            ->with(['author', 'categories', 'tags'])
            ->where('slug', $slug)
            ->firstOrFail();

        $related = Article::query()
            ->published()
            ->whereKeyNot($article->getKey())
            ->whereHas('categories', fn ($query) => $query->whereIn('categories.id', $article->categories->modelKeys()))
            ->latest('published_at')
            ->take(3)
            ->get();

        return view('blog.show', [
            'article' => $article,
            'related' => $related,
        ]);
    }
}
