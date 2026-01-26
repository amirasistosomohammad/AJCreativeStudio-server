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
        // Get the full path from the request URI to handle paths with slashes
        $fullPath = $request->path();
        // Remove 'api/files/' prefix
        $path = str_replace('api/files/', '', $fullPath);
        $path = ltrim($path, '/');
        
        // Prevent path traversal
        if (str_contains($path, '..')) {
            Log::warning('PublicFileController: Path traversal attempt', ['path' => $path]);
            return response()->noContent(Response::HTTP_NOT_FOUND);
        }

        Log::info('PublicFileController: Attempting to serve file', [
            'request_path' => $request->path(),
            'route_path' => $path,
            'exists' => Storage::disk('public')->exists($path),
            'storage_path' => Storage::disk('public')->path($path),
        ]);

        if (! Storage::disk('public')->exists($path)) {
            return response()->noContent(Response::HTTP_NOT_FOUND);
        }

        return Storage::disk('public')->response($path, null, [
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
