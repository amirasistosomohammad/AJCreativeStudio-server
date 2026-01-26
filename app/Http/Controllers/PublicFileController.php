<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PublicFileController extends Controller
{
    public function show(Request $request)
    {
        // Get the full path from the request URI
        // Request URI: /api/files/products/thumbnails/image.png
        // We need: products/thumbnails/image.png
        $uri = $request->getRequestUri();
        $path = str_replace('/api/files/', '', $uri);
        $path = ltrim($path, '/');
        
        // Remove query string if present
        if (($pos = strpos($path, '?')) !== false) {
            $path = substr($path, 0, $pos);
        }
        
        // URL decode the path
        $path = urldecode($path);
        
        // Prevent path traversal
        if (str_contains($path, '..')) {
            Log::warning('PublicFileController: Path traversal attempt', ['path' => $path]);
            return response()->noContent(Response::HTTP_NOT_FOUND);
        }

        Log::info('PublicFileController: Attempting to serve file', [
            'uri' => $uri,
            'path' => $path,
            'exists' => Storage::disk('public')->exists($path),
        ]);

        if (! Storage::disk('public')->exists($path)) {
            Log::warning('PublicFileController: File not found', [
                'path' => $path,
                'storage_root' => Storage::disk('public')->path(''),
            ]);
            return response()->noContent(Response::HTTP_NOT_FOUND);
        }

        return Storage::disk('public')->response($path, null, [
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
