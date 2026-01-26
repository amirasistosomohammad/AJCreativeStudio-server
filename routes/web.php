<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return view('welcome');
});

// Fallback route to serve storage files if symlink doesn't exist (for DigitalOcean)
Route::get('/storage/{path}', function ($path) {
    $path = urldecode($path);
    
    // Prevent path traversal
    if (str_contains($path, '..') || empty($path)) {
        abort(404);
    }
    
    // Check if file exists in storage
    if (!Storage::disk('public')->exists($path)) {
        abort(404);
    }
    
    // Return the file
    return Storage::disk('public')->response($path, null, [
        'Cache-Control' => 'public, max-age=3600',
    ]);
})->where('path', '.*');
