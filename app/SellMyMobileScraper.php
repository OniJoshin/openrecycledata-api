<?php

namespace App;

use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

class SellMyMobileScraper
{
    public function scrape()
    {
        $url = 'https://www.sellmymobile.com/phones/apple/iphone-13/';
        $response = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
        ])->get($url);

        if ($response->failed()) {
            throw new \Exception('Failed to fetch the page.');
        }

        $html = $response->body();
        $crawler = new Crawler($html);

        $offers = [];

        // Adjust the selector based on the actual HTML structure
        $crawler->filter('.offer-row')->each(function ($node) use (&$offers) {
            $merchant = $node->filter('.merchant-name')->text();
            $priceText = $node->filter('.offer-price')->text();
            $price = (float) preg_replace('/[^0-9.]/', '', $priceText);
            $condition = $node->filter('.condition')->text();
            $network = $node->filter('.network')->text();
            $storage = $node->filter('.storage')->text();

            $offers[] = [
                'brand' => 'Apple',
                'model' => 'iPhone 13',
                'storage' => $storage,
                'normalized_name' => 'Apple iPhone 13 ' . $storage,
                'merchant' => $merchant,
                'price' => $price,
                'condition' => $condition,
                'network' => $network,
            ];
        });

        return $offers;
    }
}

