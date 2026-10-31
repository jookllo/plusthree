<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function (Request $request) {
    $filters = $request->validate([
        'q' => ['nullable', 'string', 'max:100'],
        'type' => ['nullable', 'string', 'in:Full-time,Contract,Internship'],
    ]);
    $query = trim($filters['q'] ?? '');
    $type = $filters['type'] ?? '';
    $jobs = collect(config('preview.jobs'))->filter(fn (array $job) =>
        ($query === '' || str_contains(mb_strtolower($job['title'].' '.$job['sector'].' '.$job['description'].' '.$job['mode']), mb_strtolower($query))) &&
        ($type === '' || $job['type'] === $type)
    )->values();

    return view('home', compact('jobs', 'query', 'type'));
})->name('home');
