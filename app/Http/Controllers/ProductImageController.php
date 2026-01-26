<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Response;
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
        
        if (!$product || !$product->thumbnail_image) {
            return response()->noContent(Response::HTTP_NOT_FOUND);
        }

        $path = $product->thumbnail_image;
        
        if (! Storage::disk('public')->exists($path)) {
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

