<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PublicFileController extends Controller
{
    public function show(Request $request, string $path)
    {
        // The route parameter should already contain the path
        // URL decode it in case it's encoded
        $path = urldecode($path);
        $path = ltrim($path, '/');
        
        // Remove query string if present
        if (($pos = strpos($path, '?')) !== false) {
            $path = substr($path, 0, $pos);
        }
        
        // Prevent path traversal
        if (str_contains($path, '..')) {
            Log::warning('PublicFileController: Path traversal attempt', ['path' => $path]);
            return response()->noContent(Response::HTTP_NOT_FOUND);
        }

        Log::info('PublicFileController: Attempting to serve file', [
            'request_uri' => $request->getRequestUri(),
            'route_path' => $path,
            'exists' => Storage::disk('public')->exists($path),
            'storage_path' => Storage::disk('public')->path($path),
        ]);

        if (! Storage::disk('public')->exists($path)) {
            Log::warning('PublicFileController: File not found', [
                'path' => $path,
                'storage_root' => Storage::disk('public')->path(''),
                'files_in_storage' => Storage::disk('public')->allFiles('products/thumbnails'),
            ]);
            return response()->noContent(Response::HTTP_NOT_FOUND);
        }

        return Storage::disk('public')->response($path, null, [
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
