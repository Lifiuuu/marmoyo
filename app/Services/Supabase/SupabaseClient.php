<?php

namespace App\Services\Supabase;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SupabaseClient
{
    protected ?string $url;
    protected ?string $anonKey;
    protected ?string $serviceRoleKey;
    protected string $defaultBucket;

    public function __construct()
    {
        $this->url = rtrim(config('supabase.url', ''), '/');
        $this->anonKey = config('supabase.anon_key', '');
        $this->serviceRoleKey = config('supabase.service_role_key', '');
        $this->defaultBucket = config('supabase.storage_bucket', 'marmoyo-assets');
    }

    /**
     * Check if Supabase API credentials are fully configured.
     */
    public function isConfigured(): bool
    {
        return !empty($this->url) && !empty($this->anonKey);
    }

    /**
     * Upload a file into Supabase Storage or fallback to local disk.
     */
    public function uploadFile(string $path, string $contents, string $mimeType = 'image/jpeg', ?string $bucket = null): string
    {
        $bucket = $bucket ?? $this->defaultBucket;

        if ($this->isConfigured()) {
            try {
                $endpoint = "{$this->url}/storage/v1/object/{$bucket}/{$path}";
                $token = !empty($this->serviceRoleKey) ? $this->serviceRoleKey : $this->anonKey;

                $response = Http::withHeaders([
                    'Authorization' => "Bearer {$token}",
                    'Content-Type' => $mimeType,
                ])->withBody($contents, $mimeType)->post($endpoint);

                if ($response->successful()) {
                    return $this->getPublicUrl($path, $bucket);
                }

                Log::warning('Supabase Storage upload failed, falling back to local disk: ' . $response->body());
            } catch (\Throwable $e) {
                Log::error('Supabase upload exception: ' . $e->getMessage());
            }
        }

        // Graceful fallback to Laravel public storage disk
        Storage::disk('public')->put($path, $contents);
        return Storage::disk('public')->url($path);
    }

    /**
     * Get the public URL for an asset in storage.
     */
    public function getPublicUrl(string $path, ?string $bucket = null): string
    {
        $bucket = $bucket ?? $this->defaultBucket;

        if ($this->isConfigured()) {
            return "{$this->url}/storage/v1/object/public/{$bucket}/{$path}";
        }

        return Storage::disk('public')->url($path);
    }

    /**
     * Delete an asset from storage.
     */
    public function deleteFile(string $path, ?string $bucket = null): bool
    {
        $bucket = $bucket ?? $this->defaultBucket;

        if ($this->isConfigured()) {
            try {
                $endpoint = "{$this->url}/storage/v1/object/{$bucket}/{$path}";
                $token = !empty($this->serviceRoleKey) ? $this->serviceRoleKey : $this->anonKey;

                $response = Http::withHeaders([
                    'Authorization' => "Bearer {$token}",
                ])->delete($endpoint);

                return $response->successful();
            } catch (\Throwable $e) {
                Log::error('Supabase delete exception: ' . $e->getMessage());
            }
        }

        return Storage::disk('public')->delete($path);
    }

    /**
     * Broadcast an event to Supabase Realtime channel or fallback to local logging.
     */
    public function broadcast(string $channel, string $event, array $payload): void
    {
        if ($this->isConfigured()) {
            try {
                $endpoint = "{$this->url}/rest/v1/rpc/broadcast";
                $token = !empty($this->serviceRoleKey) ? $this->serviceRoleKey : $this->anonKey;

                Http::withHeaders([
                    'Authorization' => "Bearer {$token}",
                    'apikey' => $this->anonKey,
                    'Content-Type' => 'application/json',
                ])->post($endpoint, [
                    'channel' => $channel,
                    'event' => $event,
                    'payload' => $payload,
                ]);
            } catch (\Throwable $e) {
                Log::debug('Supabase realtime broadcast error: ' . $e->getMessage());
            }
        }

        Log::info("Realtime Event [{$channel}:{$event}]", $payload);
    }
}
