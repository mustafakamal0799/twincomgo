<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SyncCustomerFromAccurate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $token = env('ACCURATE_API_TOKEN');
        $session = env('ACCURATE_SESSION');
        $pageSize = 100;

        // === SYNC CUSTOMER ===
        $page = 1;
        do {
            $params = [
                'sp.page' => $page,
                'sp.pageSize' => $pageSize,
                'fields' => 'id,name,email',
                'filter.customerCategoryId' => 2650,
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'X-Session-ID' => $session
            ])->get('https://public.accurate.id/accurate/api/customer/list.do', $params);

            if ($response->failed()) break;

            $customers = $response->json()['d'] ?? [];
            if (count($customers) === 0) break;

            foreach ($customers as $accurateUser) {
                $user = User::firstOrNew(['email' => $accurateUser['email'] ?? null]);
                $user->name = $accurateUser['name'] ?? null;
                $user->status = 'reseller';

                if (!$user->exists) {
                    $user->password = bcrypt('twincom@reseller123');
                }

                $user->save();
            }

            $page++;
        } while (true);
    }
}
