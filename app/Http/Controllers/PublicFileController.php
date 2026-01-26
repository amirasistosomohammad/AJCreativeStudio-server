<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class PublicFileController extends Controller
{
    public function show(string $path)
    {
        // Prevent path traversal
        $path = ltrim($path, '/');
        if (str_contains($path, '..')) {
            return response()->noContent(Response::HTTP_NOT_FOUND);
        }

        if (! Storage::disk('public')->exists($path)) {
            return response()->noContent(Response::HTTP_NOT_FOUND);
        }

        return Storage::disk('public')->response($path, null, [
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
