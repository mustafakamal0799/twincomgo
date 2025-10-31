<?php

namespace App\Console\Commands;

use App\Models\AccurateItems;
use App\Helpers\AccurateHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class SyncAccurateItems extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:accurate-items';

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
        $this->info("⏳ Memulai sinkronisasi item dari Accurate...");
        $page = 1;
        $totalInserted = 0;
        $token = AccurateHelper::getToken();
        $session = AccurateHelper::getSession();

        do {
            // Ambil data dari Accurate
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'X-Session-ID' => $session
            ])->get('https://public.accurate.id/accurate/api/item/list.do', [
                'sp.page' => $page,
                'sp.pageSize' => 100,
                'fields' => 'id,name,no',
                'filter.suspended' => false,
            ]);

            $items = collect($response['d'] ?? [])->map(fn ($i) => [
                'accurate_id' => $i['id'],
                'name' => $i['name'] ?? '',
                'no' => $i['no'] ?? '',
                'updated_at' => now(),
                'created_at' => now(),
            ]);

            if ($items->isEmpty()) {
                break;
            }

            // Simpan ke DB dalam transaksi dan batch (cepat & aman)
            DB::transaction(function () use ($items) {
                AccurateItems::upsert(
                    $items->toArray(),
                    ['accurate_id'], // unique key
                    ['name', 'no', 'updated_at']
                );
            });

            $totalInserted += $items->count();
            $this->info("✅ Page {$page} disimpan ({$items->count()} item).");

            $page++;
            $hasMore = isset($response['sp']['pageCount']) && $page <= $response['sp']['pageCount'];
        } while ($hasMore);

        $this->info("🎉 Sinkronisasi selesai! Total item disimpan: {$totalInserted}");
    }
}
