<?php

namespace App\Services\Supabase;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class SupabaseStorageService
{
    public function __construct(
        protected SupabaseClient $client
    ) {}

    /**
     * Upload a menu image file to Supabase or local public disk.
     */
    public function uploadMenuImage(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $filename = 'menus/' . Str::uuid() . '.' . $extension;
        $contents = file_get_contents($file->getRealPath());
        $mimeType = $file->getMimeType() ?: 'image/jpeg';

        return $this->client->uploadFile($filename, $contents, $mimeType);
    }

    /**
     * Upload generated receipt HTML or image.
     */
    public function uploadReceipt(string $orderNumber, string $contents, string $mimeType = 'text/html'): string
    {
        $filename = 'receipts/' . $orderNumber . '.html';
        return $this->client->uploadFile($filename, $contents, $mimeType);
    }

    /**
     * Check if cloud Supabase storage is active.
     */
    public function isCloudStorageActive(): bool
    {
        return $this->client->isConfigured();
    }
}

