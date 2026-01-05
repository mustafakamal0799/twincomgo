<?php

namespace App\Services\Accurate;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class ItemService
{
    private string $baseUrl;
    private string $token;
    private string $session;

    public function __construct($token, $session)
    {
        $this->baseUrl = rtrim(config('services.accurate.base_api'), '/');
        $this->token   = $token;
        $this->session = $session;
    }

    private function client()
    {
        return Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'X-Session-ID'  => $this->session,
        ])->retry(3, 200);
    }

    public function listItems(array $params)
    {
        $response = $this->client()->get("{$this->baseUrl}/item/list.do", $params);
        return $response->json();
    }

    public function getPrice($itemId, $priceCategory = 'USER')
    {
        return Cache::remember("accurate:price:{$itemId}:{$priceCategory}", 600, function () use ($itemId, $priceCategory) {
            $resp = $this->client()->get("{$this->baseUrl}/item/get-selling-price.do", [
                'id' => $itemId,
                'priceCategoryName' => $priceCategory,
            ]);
            $data = $resp->json()['d'] ?? [];
            return $data['unitPrice'] ?? ($data['unitPriceRule'][0]['price'] ?? 0);
        });
    }

    public function getCategories()
    {
        return Cache::remember('accurate:categories:global', 1800, function () {
            $cats = collect();
            $page = 1;
            do {
                $resp = $this->client()->get("{$this->baseUrl}/item-category/list.do", [
                    'sp.page' => $page,
                    'sp.pageSize' => 100,
                    'fields' => 'id,name,parent',
                ]);
                $json = $resp->json();
                $cats = $cats->merge($json['d'] ?? []);
                $page++;
            } while (($json['sp']['pageCount'] ?? 1) >= $page);
            return $cats->values();
        });
    }
}
