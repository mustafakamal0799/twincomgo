<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SyncAccurateUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:accurate-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync users from Accurate API to local database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('⏳ Mengambil semua data dari Accurate...');

        $token = env('ACCURATE_API_TOKEN');
        $session = env('ACCURATE_SESSION');
        
        $pageSize = 100;

        $totalUsers = 0;
        $newUsers = 0;
        $updatedUsers = 0;

        $page = 1;
        do {
            $this->info("🔄 Mengambil data halaman ke-$page...");
    
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
    
            if ($response->failed()) {
                $this->error('❌ Gagal menghubungi Accurate API di halaman ' . $page);
                break;
            }
    
            $users = $response->json()['d'] ?? [];
    
            if (count($users) === 0) {
                break;
            }
    
            foreach ($users as $accurateUser) {

                $user = User::firstOrNew(['email' => $accurateUser['email'] ?? null]);
                $user->name = $accurateUser['name'] ?? null;

                if (!$user->exists) {
                    $user->password = bcrypt('twincom@reseller123');
                    $newUsers++;
                } else {
                    $updatedUsers++;
                }
                $user->save();
                $totalUsers++;
                
            }
    
            $page++; // naikkan halaman untuk next loop
        } while (true);


        $page = 1;
        do {
            $this->info("👔 Mengambil data karyawan halaman ke-$page...");

            $params = [
                'sp.page' => $page,
                'sp.pageSize' => $pageSize,
                'fields' => 'id,name,email'
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'X-Session-ID' => $session
            ])->get('https://public.accurate.id/accurate/api/employee/list.do', $params);

            if ($response->failed()) {
                $this->error('❌ Gagal menghubungi Accurate API untuk karyawan di halaman ' . $page);
                break;
            }

            $employees = $response->json()['d'] ?? [];

            if (count($employees) === 0) break;

            foreach ($employees as $employee) {
                $user = User::firstOrNew(['email' => $employee['email'] ?? null]);
                $user->name = $employee['name'] ?? null;
                $user->status = 'karyawan';

                if (!$user->exists) {
                    $user->password = bcrypt('twincom@karyawan123');
                    $newUsers++;
                } else {
                    $updatedUsers++;
                }

                $user->save();
                $totalUsers++;
            }

            $page++;
        } while (true);
    
        $this->info("✅ Sinkronisasi selesai.");
        $this->info("👤 Total user diproses: $totalUsers");
        $this->info("🆕 User baru dibuat: $newUsers");
        $this->info("♻️ User yang diperbarui: $updatedUsers");
    }
}
