<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\EmployerEnquiryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\JobListingController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/llms.txt', [SitemapController::class, 'llms'])->name('llms');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');

// Google sign-in for the panel. Registered before the panel routes are matched
// so the callback path is ours, not a panel catch-all.
Route::get('/mdosi/auth/google', [GoogleController::class, 'redirect'])->name('auth.google');
Route::get('/mdosi/auth/google/callback', [GoogleController::class, 'callback'])->name('auth.google.callback');

Route::get('/jobs', [JobListingController::class, 'index'])->name('jobs.index');
Route::get('/fellowships', [JobListingController::class, 'fellowships'])->name('jobs.fellowships');
Route::get('/other-opportunities', [JobListingController::class, 'otherOpportunities'])->name('jobs.other');

// Crawlable landing pages for sectors and countries. They sit above the
// /jobs/{slug} catch-all so a sector named like a role cannot shadow it.
Route::get('/jobs/sector/{slug}', [JobListingController::class, 'sector'])->name('jobs.sector');
Route::get('/jobs/in/{country}', [JobListingController::class, 'country'])->name('jobs.country');

Route::get('/jobs/{slug}/preview', [JobListingController::class, 'preview'])
    ->middleware('signed')
    ->name('jobs.preview');
Route::get('/jobs/{slug}', [JobListingController::class, 'show'])->name('jobs.show');

// Employer enquiries are stored for the panel inbox, never emailed on submit.
Route::get('/for-employers', [EmployerEnquiryController::class, 'show'])->name('employers.show');
Route::post('/for-employers', [EmployerEnquiryController::class, 'store'])
    ->middleware('throttle:6,1')
    ->name('employers.store');

Route::get('/blog', [ArticleController::class, 'index'])->name('blog.index');
Route::get('/blog/category/{slug}', [CategoryController::class, 'show'])->name('blog.category');
Route::get('/blog/{slug}/preview', [ArticleController::class, 'preview'])
    ->middleware('signed')
    ->name('blog.preview');
Route::get('/blog/{slug}', [ArticleController::class, 'show'])->name('blog.show');
