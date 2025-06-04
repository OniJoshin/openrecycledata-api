<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Device;
use App\Models\Offer;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class ScrapeRecyclingPrices extends Command
{
    protected $signature = 'scrape:prices';
    protected $description = 'Imports grouped JSON offers from scrapers into the database';

    public function handle()
    {
        $this->info('📦 Starting import of grouped JSON offers...');
        $scraperDir = base_path('scrapers/data');
        $sources = File::directories($scraperDir);

        foreach ($sources as $sourcePath) {
            $sourceName = basename($sourcePath);

            foreach (File::allFiles($sourcePath) as $file) {
                if ($file->getExtension() !== 'json') continue;

                $this->info('🔄 Importing: ' . $file->getRelativePathname());

                $groups = json_decode($file->getContents(), true);
                $type = str_contains($file->getPath(), 'tablet') ? 'tablet' : 'phone';

                foreach ($groups as $key => $group) {
                    $brand = self::cleanBrand($group['brand'] ?? '');
                    $storage = strtoupper(trim($group['storage'] ?? ''));
                    $model = trim(preg_replace('/\s+/', ' ', $group['model'] ?? ''));
                    $normalized = "$brand $model $storage";
                    $slug = $group['slug'] ?? Str::slug($normalized);

                    $device = Device::firstOrCreate(
                        [
                            'normalized_name' => $normalized,
                            'slug' => $slug,
                        ],
                        [
                            'brand' => $brand,
                            'model' => $model,
                            'storage' => $storage,
                            'type' => $type,
                        ]
                    );

                    if ($device->type !== $type) {
                        $device->type = $type;
                        $device->save();
                    }

                    foreach ($group['offers'] ?? [] as $offer) {
                        Offer::create([
                            'device_id' => $device->id,
                            'merchant' => $offer['merchant'],
                            'price' => $offer['price'],
                            'condition' => $offer['condition'],
                            'network' => $offer['network'],
                            'source' => $sourceName,
                            'timestamp' => now(),
                        ]);
                    }
                }
            }
        }

        $this->info('✅ Import complete!');
    }

    private static function cleanBrand(string $brand): string
    {
        $brand = preg_replace('/\b(MOBILE|BUY TEK|MOBILES|PHONES|SHOP|THE)\b/i', '', $brand);
        return ucfirst(strtolower(trim(preg_replace('/\s+/', ' ', $brand))));
    }
}
