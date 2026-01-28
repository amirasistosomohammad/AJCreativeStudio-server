<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProductImageController extends Controller
{
    /**
     * Serve product thumbnail image by product ID
     * Uses configured storage disk (s3 for Spaces, public for local)
     */
    public function thumbnail($id)
    {
        $product = Product::find($id);
        
        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }
        
        if (!$product->thumbnail_image) {
            return response()->json(['error' => 'No thumbnail path in database'], 404);
        }

        $disk = config('products.storage_disk', 'public');
        $path = $product->thumbnail_image;
        
        // Clean up path (remove leading slashes, storage/ prefix if present)
        $path = ltrim($path, '/');
        $path = preg_replace('#^storage/#', '', $path);
        $path = preg_replace('#^public/#', '', $path);
        
        $storage = Storage::disk($disk);
        
        // Check if file exists
        if (!$storage->exists($path)) {
            return response()->json([
                'error' => 'File not found',
                'product_id' => $id,
                'product_title' => $product->title,
                'database_path' => $product->thumbnail_image,
                'checked_path' => $path,
                'storage_disk' => $disk,
            ], 404);
        }

        // For S3/Spaces, redirect to the CDN URL (more efficient)
        if ($disk === 's3') {
            $url = $storage->url($path);
            return redirect($url, 302, [
                'Cache-Control' => 'public, max-age=3600',
            ]);
        }

        // For local storage, stream the file
        return $storage->response($path, null, [
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * Serve product feature image by product ID and index
     * Uses configured storage disk (s3 for Spaces, public for local)
     */
    public function feature($id, $index = 0)
    {
        $product = Product::find($id);
        
        if (!$product || !$product->feature_images) {
            return response()->noContent(Response::HTTP_NOT_FOUND);
        }

        $featureImages = is_array($product->feature_images) 
            ? $product->feature_images 
            : json_decode($product->feature_images, true);

        if (!is_array($featureImages) || !isset($featureImages[$index])) {
            return response()->noContent(Response::HTTP_NOT_FOUND);
        }

        $disk = config('products.storage_disk', 'public');
        $path = $featureImages[$index];
        
        // Clean up path (remove leading slashes, storage/ prefix if present)
        if (is_string($path)) {
            $path = ltrim($path, '/');
            $path = preg_replace('#^storage/#', '', $path);
            $path = preg_replace('#^public/#', '', $path);
        }
        
        $storage = Storage::disk($disk);
        
        if (!$storage->exists($path)) {
            return response()->noContent(Response::HTTP_NOT_FOUND);
        }

        // For S3/Spaces, redirect to the CDN URL (more efficient)
        if ($disk === 's3') {
            $url = $storage->url($path);
            return redirect($url, 302, [
                'Cache-Control' => 'public, max-age=3600',
            ]);
        }

        // For local storage, stream the file
        return $storage->response($path, null, [
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}

