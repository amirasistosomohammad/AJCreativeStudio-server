<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return view('welcome');
});

// Fallback route to serve storage files if symlink doesn't exist (for DigitalOcean)
// Handles both local storage and S3/Spaces
Route::get('/storage/{path}', function ($path) {
    $path = urldecode($path);
    $disk = config('products.storage_disk', 'public');
    
    // Prevent path traversal
    if (str_contains($path, '..') || empty($path)) {
        abort(404);
    }
    
    $storage = Storage::disk($disk);
    
    // Check if file exists in storage
    if (!$storage->exists($path)) {
        abort(404);
    }
    
    // For S3/Spaces, redirect to the CDN URL (more efficient than streaming through Laravel)
    if ($disk === 's3') {
        // Use AWS_URL (CDN endpoint) if set, otherwise fall back to Storage::url()
        $cdnUrl = config('filesystems.disks.s3.url');
        if ($cdnUrl) {
            // Ensure CDN URL doesn't end with slash
            $cdnUrl = rtrim($cdnUrl, '/');
            // Ensure path doesn't start with slash
            $cleanPath = ltrim($path, '/');
            $url = $cdnUrl . '/' . $cleanPath;
        } else {
            $url = $storage->url($path);
        }
        return redirect($url, 302, [
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
    
    // For local storage, stream the file
    return $storage->response($path, null, [
        'Cache-Control' => 'public, max-age=3600',
    ]);
})->where('path', '.*');
