<?php

namespace App\Http\Controllers;

use App\Models\ProductDownload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class DownloadController extends Controller
{
    /**
     * Get download information (for download page)
     */
    public function info(string $token)
    {
        $download = ProductDownload::where('download_token', $token)->first();

        if (!$download) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid download link',
            ], 404);
        }

        // Load relationships
        $download->load(['product', 'order', 'orderItem']);

        return response()->json([
            'success' => true,
            'download' => [
                'token' => $download->download_token,
                'product' => [
                    'id' => $download->product->id,
                    'title' => $download->product->title,
                    'description' => $download->product->description,
                    'file_name' => $download->product->file_name,
                    'file_size' => $download->product->file_size,
                ],
                'order' => [
                    'order_number' => $download->order->order_number,
                    'created_at' => $download->order->created_at,
                ],
                'download_count' => $download->download_count,
                'max_downloads' => $download->max_downloads,
                'remaining_downloads' => $download->remaining_downloads,
                'expires_at' => $download->expires_at,
                'is_expired' => $download->isExpired(),
                'can_download' => $download->canDownload(),
                'last_downloaded_at' => $download->last_downloaded_at,
            ],
        ]);
    }

    /**
     * Download the product file using secure token
     */
    public function download(string $token)
    {
        $download = ProductDownload::where('download_token', $token)->first();

        if (!$download) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid download link',
            ], 404);
        }

        // Load product
        $product = $download->product;

        if (!$product || !$product->file_path) {
            Log::error('Product file not found for download', [
                'token' => $token,
                'product_id' => $download->product_id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Product file not found',
            ], 404);
        }

        // Check if file exists
        if (!Storage::disk('public')->exists($product->file_path)) {
            Log::error('File not found on storage', [
                'token' => $token,
                'file_path' => $product->file_path,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'File not found on server',
            ], 404);
        }

        // Record download
        $download->recordDownload();

        // Log download
        Log::info('Product download successful', [
            'token' => $token,
            'product_id' => $product->id,
            'order_id' => $download->order_id,
            'download_count' => $download->download_count,
        ]);

        // Get file path and name
        $filePath = Storage::disk('public')->path($product->file_path);
        $fileName = $product->file_name ?: basename($product->file_path);

        // Determine content type based on file extension
        $contentType = $this->getContentType($fileName);

        // Return file download response
        return response()->download($filePath, $fileName, [
            'Content-Type' => $contentType,
        ]);
    }

    /**
     * Get content type based on file extension
     */
    private function getContentType(string $fileName): string
    {
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $contentTypes = [
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xls' => 'application/vnd.ms-excel',
            'pdf' => 'application/pdf',
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'txt' => 'text/plain',
            'csv' => 'text/csv',
            'json' => 'application/json',
        ];

        return $contentTypes[$extension] ?? 'application/octet-stream';
    }

    /**
     * List customer's downloads (for dashboard)
     */
    public function index(Request $request)
    {
        $customer = $request->user();

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $downloads = ProductDownload::where('customer_id', $customer->id)
            ->with(['product', 'order'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'downloads' => $downloads->map(function ($download) {
                return [
                    'token' => $download->download_token,
                    'product' => [
                        'id' => $download->product->id,
                        'title' => $download->product->title,
                        'file_name' => $download->product->file_name,
                    ],
                    'order' => [
                        'order_number' => $download->order->order_number,
                    ],
                    'download_count' => $download->download_count,
                    'max_downloads' => $download->max_downloads,
                    'remaining_downloads' => $download->remaining_downloads,
                    'expires_at' => $download->expires_at,
                    'is_expired' => $download->isExpired(),
                    'can_download' => $download->canDownload(),
                    'last_downloaded_at' => $download->last_downloaded_at,
                    'created_at' => $download->created_at,
                ];
            }),
        ]);
    }
}
