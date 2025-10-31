<?php

namespace App\Console\Commands;

use App\Models\AccurateItems;
use App\Models\AccuratePrice;
use App\Helpers\AccurateHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncAccuratePrice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:accurate-price';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("💰 Sinkronisasi harga per kategori...");
        $categories = ['USER', 'RESELLER'];

        foreach ($categories as $category) {
            $this->line("🔹 Sinkron kategori: {$category}");

            AccurateItems::select('id', 'accurate_id')
                ->chunk(100, function ($items) use ($category) {
                    foreach ($items as $item) {
                        $resp = AccurateHelper::getApi('/item/get-selling-price.do', [
                            'id' => $item->accurate_id,
                            'priceCategoryName' => $category,
                            'discountCategoryName' => $category,
                        ]);

                        $price = $resp['d']['unitPrice'] ?? null;
                        if (!$price) continue;

                        AccuratePrice::updateOrCreate(
                            ['item_id' => $item->id, 'category_name' => $category],
                            ['price' => $price, 'synced_at' => now()]
                        );
                    }
                });
        }

        $this->info("✅ Semua kategori selesai disinkron.");
    }
}
