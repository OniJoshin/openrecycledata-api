<?php

namespace Tests\Feature;

use App\Models\Offer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScrapeRecyclingPricesTest extends TestCase
{
    use RefreshDatabase;

    public function test_rerunning_command_does_not_create_duplicates(): void
    {
        $this->artisan('scrape:prices');

        $firstCount = Offer::count();
        $this->assertGreaterThan(0, $firstCount);

        $this->artisan('scrape:prices');

        $secondCount = Offer::count();
        $this->assertSame($firstCount, $secondCount);
    }
}
