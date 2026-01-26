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
            return response()->json(['error' => 'Product not found'], 404);
        }
        
        if (!$product->thumbnail_image) {
            return response()->json(['error' => 'No thumbnail path in database'], 404);
        }

        $path = $product->thumbnail_image;
        
        // Try multiple possible locations
        $possiblePaths = [
            $path,
            'public/' . $path,
            'storage/app/public/' . $path,
            ltrim($path, '/'),
        ];
        
        $foundPath = null;
        foreach ($possiblePaths as $tryPath) {
            if (Storage::disk('public')->exists($tryPath)) {
                $foundPath = $tryPath;
                break;
            }
        }
        
        // Also try direct file system check
        if (!$foundPath) {
            $storageRoot = Storage::disk('public')->path('');
            $directPath = $storageRoot . '/' . ltrim($path, '/');
            if (file_exists($directPath)) {
                $foundPath = $path;
            }
        }
        
        if (!$foundPath) {
            // Check what files actually exist
            $allFiles = Storage::disk('public')->allFiles('products/thumbnails');
            $storageRoot = Storage::disk('public')->path('');
            
            return response()->json([
                'error' => 'File not found in any location',
                'product_id' => $id,
                'product_title' => $product->title,
                'database_path' => $path,
                'storage_root' => $storageRoot,
                'files_in_thumbnails' => array_slice($allFiles, 0, 20),
                'total_files' => count($allFiles),
            ], 404);
        }

        return Storage::disk('public')->response($foundPath, null, [
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

