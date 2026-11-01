<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\JobListingController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/jobs', [JobListingController::class, 'index'])->name('jobs.index');
Route::get('/jobs/{slug}', [JobListingController::class, 'show'])->name('jobs.show');

Route::get('/blog', [ArticleController::class, 'index'])->name('blog.index');
Route::get('/blog/category/{slug}', [CategoryController::class, 'show'])->name('blog.category');
Route::get('/blog/{slug}/preview', [ArticleController::class, 'preview'])
    ->middleware('signed')
    ->name('blog.preview');
Route::get('/blog/{slug}', [ArticleController::class, 'show'])->name('blog.show');
