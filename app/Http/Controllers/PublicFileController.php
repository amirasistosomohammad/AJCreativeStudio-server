<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PublicFileController extends Controller
{
    public function show(Request $request, string $path)
    {
        // Remove leading slashes and decode
        $path = ltrim(urldecode($path), '/');
        
        // Remove query string if present
        if (($pos = strpos($path, '?')) !== false) {
            $path = substr($path, 0, $pos);
        }
        
        // Prevent path traversal
        if (str_contains($path, '..') || empty($path)) {
            abort(404);
        }

        // Check if file exists
        if (! Storage::disk('public')->exists($path)) {
            abort(404);
        }

        // Return the file with proper headers
        return Storage::disk('public')->response($path, null, [
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
