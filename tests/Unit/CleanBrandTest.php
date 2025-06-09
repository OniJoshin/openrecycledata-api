<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Console\Commands\ScrapeRecyclingPrices;
use ReflectionMethod;

class CleanBrandTest extends TestCase
{
    /**
     * @dataProvider brandProvider
     */
    public function test_clean_brand(string $input, string $expected): void
    {
        $method = new ReflectionMethod(ScrapeRecyclingPrices::class, 'cleanBrand');
        $method->setAccessible(true);
        $this->assertSame($expected, $method->invoke(null, $input));
    }

    public static function brandProvider(): array
    {
        return [
            ['THE MOBILE SHOP', 'Mobile shop'],
            ['BUY TEK', ''],
            ['SAMSUNG MOBILES', 'Samsung'],
            ['ACME PHONES', 'Acme'],
            ['THE great phone SHOP', 'Great phone'],
        ];
    }
}
