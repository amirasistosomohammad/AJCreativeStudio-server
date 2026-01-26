<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PublicFileController extends Controller
{
    public function show(Request $request, $path = null)
    {
        // Handle both route parameter and direct URI extraction
        if (empty($path)) {
            $uri = $request->getRequestUri();
            $path = str_replace('/api/files/', '', $uri);
            $path = ltrim($path, '/');
        }
        
        // Clean the path
        $path = ltrim($path, '/');
        
        // Remove query string
        if (($pos = strpos($path, '?')) !== false) {
            $path = substr($path, 0, $pos);
        }
        
        // URL decode
        $path = urldecode($path);
        
        // Prevent path traversal
        if (str_contains($path, '..') || empty($path)) {
            Log::warning('PublicFileController: Invalid path', ['path' => $path, 'uri' => $request->getRequestUri()]);
            return response()->noContent(Response::HTTP_NOT_FOUND);
        }

        // Log for debugging
        $exists = Storage::disk('public')->exists($path);
        Log::info('PublicFileController', [
            'uri' => $request->getRequestUri(),
            'path' => $path,
            'exists' => $exists,
            'storage_root' => Storage::disk('public')->path(''),
        ]);

        if (! $exists) {
            // List files in the directory to help debug
            $dir = dirname($path);
            $files = Storage::disk('public')->allFiles($dir);
            Log::warning('PublicFileController: File not found', [
                'requested' => $path,
                'files_in_dir' => array_slice($files, 0, 10), // First 10 files
            ]);
            return response()->noContent(Response::HTTP_NOT_FOUND);
        }

        return Storage::disk('public')->response($path, null, [
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
