<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PublicFileController extends Controller
{
    public function show(Request $request, $path = null)
    {
        // Extract path from route parameter or URI
        if (empty($path)) {
            $uri = $request->getRequestUri();
            $path = preg_replace('#^/api/files/#', '', $uri);
        }
        
        $path = ltrim(urldecode($path), '/');
        
        // Remove query string
        if (($pos = strpos($path, '?')) !== false) {
            $path = substr($path, 0, $pos);
        }
        
        // Security check
        if (str_contains($path, '..') || empty($path)) {
            Log::warning('PublicFileController: Invalid path', ['path' => $path]);
            abort(404);
        }

        // Log for debugging
        $exists = Storage::disk('public')->exists($path);
        Log::info('PublicFileController', [
            'requested_path' => $path,
            'exists' => $exists,
            'storage_path' => $exists ? Storage::disk('public')->path($path) : 'N/A',
        ]);

        if (! $exists) {
            abort(404);
        }

        return Storage::disk('public')->response($path, null, [
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
