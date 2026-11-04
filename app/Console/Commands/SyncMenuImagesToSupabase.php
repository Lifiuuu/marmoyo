<?php

namespace App\Console\Commands;

use App\Models\Menu;
use App\Services\Supabase\SupabaseClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SyncMenuImagesToSupabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'marmoyo:sync-storage';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync all menu photos into the Supabase Storage bucket (marmoyo-assets)';

    /**
     * Execute the console command.
     */
    public function handle(SupabaseClient $client): int
    {
        $this->info("Checking Supabase connection...");

        if (!$client->isConfigured()) {
            $this->error("Supabase credentials are not configured in .env!");
            return 1;
        }

        $bucket = config('supabase.storage_bucket', 'marmoyo-assets');
        $this->info("Target Supabase Bucket: {$bucket}");

        $menus = Menu::all();
        $this->info("Found {$menus->count()} menus to process.");

        foreach ($menus as $menu) {
            $this->line("Processing: <comment>{$menu->name}</comment> ({$menu->slug})...");

            $imageBytes = null;
            $currentUrl = $menu->image_url;

            if (!empty($currentUrl) && filter_var($currentUrl, FILTER_VALIDATE_URL)) {
                try {
                    $response = Http::withoutVerifying()->timeout(15)->get($currentUrl);
                    if ($response->successful()) {
                        $imageBytes = $response->body();
                    } else {
                        $this->warn("Failed downloading current image, using default placeholder.");
                    }
                } catch (\Throwable $e) {
                    $this->warn("Download exception: " . $e->getMessage());
                }
            }

            if (!$imageBytes) {
                // 1x1 transparent/neutral fallback image bytes if download failed
                $imageBytes = file_get_contents('https://images.unsplash.com/photo-1569718212165-3a8278d5f624?w=500');
            }

            $storagePath = "menus/{$menu->slug}.jpg";
            $publicUrl = $client->uploadFile($storagePath, $imageBytes, 'image/jpeg', $bucket);

            $menu->update(['image_url' => $publicUrl]);
            $this->info("✓ Uploaded to Supabase Storage: {$publicUrl}");
        }

        $this->newLine();
        $this->info("🎉 All menu images have been uploaded to Supabase Storage bucket '{$bucket}' and linked to the database!");

        return 0;
    }
}

