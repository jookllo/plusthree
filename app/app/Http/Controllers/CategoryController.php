<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;

class CategoryController extends Controller
{
    public function show(string $slug)
    {
        $category = Category::query()
            ->where('kind', Category::KIND_ARTICLE)
            ->where('slug', $slug)
            ->firstOrFail();

        $articles = Article::query()
            ->published()
            ->whereHas('categories', fn ($query) => $query->where('categories.id', $category->getKey()))
            ->with(['author', 'categories'])
            ->latest('published_at')
            ->paginate(9)
            ->withQueryString();

        return view('blog.category', [
            'category' => $category,
            'articles' => $articles,
        ]);
    }
}
