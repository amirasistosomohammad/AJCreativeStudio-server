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
     * Similar to how branding logo works
     */
    public function thumbnail($id)
    {
        $product = Product::find($id);
        
        if (!$product) {
            Log::warning('ProductImageController: Product not found', ['id' => $id]);
            return response()->noContent(Response::HTTP_NOT_FOUND);
        }
        
        if (!$product->thumbnail_image) {
            Log::warning('ProductImageController: No thumbnail path in database', ['id' => $id, 'product' => $product->title]);
            return response()->noContent(Response::HTTP_NOT_FOUND);
        }

        $path = $product->thumbnail_image;
        
        Log::info('ProductImageController: Attempting to serve thumbnail', [
            'product_id' => $id,
            'product_title' => $product->title,
            'path' => $path,
            'exists' => Storage::disk('public')->exists($path),
            'storage_root' => Storage::disk('public')->path(''),
        ]);
        
        if (! Storage::disk('public')->exists($path)) {
            Log::warning('ProductImageController: File not found in storage', [
                'product_id' => $id,
                'path' => $path,
                'full_path' => Storage::disk('public')->path($path),
            ]);
            return response()->noContent(Response::HTTP_NOT_FOUND);
        }

        return Storage::disk('public')->response($path, null, [
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * Serve product feature image by product ID and index
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

        $path = $featureImages[$index];
        
        if (! Storage::disk('public')->exists($path)) {
            return response()->noContent(Response::HTTP_NOT_FOUND);
        }

        return Storage::disk('public')->response($path, null, [
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}

