<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Helpers\AccurateHelper;
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
        $this->info('⏳ Memulai sinkronisasi data dari Accurate...');

        $token = AccurateHelper::getToken();
        $session = AccurateHelper::getSession();
        $pageSize = 100;

        $totalUsers = 0;
        $newUsers = 0;
        $updatedUsers = 0;

        // ================================
        // 🔁 1. Sinkronisasi Customer Reseller
        // ================================
        $this->info("👥 Sinkronisasi data Customer Reseller...");

        $page = 1;
        do {
            $this->info("🔄 Mengambil customer halaman $page...");

            $params = [
                'sp.page' => $page,
                'sp.pageSize' => $pageSize,
                'fields' => 'id,name,email,suspended,customerBranchName,customerNo',
                'filter.customerCategoryId' => 2650,
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'X-Session-ID' => $session
            ])->get('https://public.accurate.id/accurate/api/customer/list.do', $params);

            if ($response->failed()) {
                $this->error("❌ Gagal mengambil data customer halaman $page.");
                break;
            }

            $customers = $response->json()['d'] ?? [];
            if (empty($customers)) break;

            $ids = collect($customers)->pluck('id');

            $batches = $ids->chunk(50);

            $withProvince = [];

            foreach ($batches as $batch) {
                $detailResponses = Http::pool(fn($pool) =>
                    $batch->map(fn($id) =>
                        $pool->withHeaders([
                            'Authorization' => 'Bearer ' . $token,
                            'X-Session-ID' => $session])
                            ->get("https://public.accurate.id/accurate/api/customer/detail.do?id={$id}")
                    )->all()
                );

                foreach ($detailResponses as $resp) {
                    if ($resp->successful()) {
                        $d = $resp->json()['d'] ?? [];
                        $withProvince[$d['id']] = $d['shipProvince'] ?? null;
                    }
                }
                // opsi: jeda kecil kalau perlu throttle
                usleep(200_000);
            }

            foreach ($customers as $cust) {
                $accurateId = $cust['id'];
                $email      = $cust['email'] ?? null;
                // ambil province dari mapping
                $province   = $withProvince[$accurateId] ?? null;

                if (!$accurateId || !$email) continue;

                if (!empty($cust['suspended']) && $cust['suspended'] === true) {
                    User::where('accurate_id', $accurateId)
                        ->orWhere(fn($q) => $q->whereNull('accurate_id')->where('email', $email))
                        ->delete();
                    continue;
                }

                $user = User::where('accurate_id', $accurateId)
                    ->orWhere(fn($q) => $q->whereNull('accurate_id')->where('email', $email))
                    ->first();

                if (!$user) {
                    $user = new User();
                    $user->password = bcrypt('twincom@reseller123');
                    $newUsers++;
                } else {
                    $updatedUsers++;
                }

                $user->accurate_id = $accurateId;
                $user->name        = $cust['name'];
                $user->email       = $email;
                $user->province    = $province;      
                $user->status      = 'RESELLER';
                $user->customer_branch = $cust['customerBranchName'] ?? null; // tambahkan customerBranch
                $user->save();

                $totalUsers++;
            }

            $page++;
        } while (true);


        // ================================
        // 👔 2. Sinkronisasi Karyawan
        // ================================
        $this->info("👨‍💼 Sinkronisasi data Karyawan...");

        $page = 1;
        do {
            $this->info("🔄 Mengambil karyawan halaman $page...");

            $params = [
                'sp.page' => $page,
                'sp.pageSize' => $pageSize,
                'fields' => 'id,name,email,suspended',
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'X-Session-ID' => $session
            ])->get('https://public.accurate.id/accurate/api/employee/list.do', $params);

            if ($response->failed()) {
                $this->error("❌ Gagal mengambil data karyawan halaman $page.");
                break;
            }

            $employees = $response->json()['d'] ?? [];
            if (empty($employees)) break;

            foreach ($employees as $employee) {
                $accurateId = $employee['id'] ?? null;
                $email = $employee['email'] ?? null;
                if (!$accurateId || !$email) continue;

                if (!empty($employee['suspended']) && $employee['suspended'] === true) {
                    User::where('accurate_id', $accurateId)
                        ->orWhere(function ($q) use ($email) {
                            $q->whereNull('accurate_id')->where('email', $email);
                        })->delete();
                    continue;
                }

                $user = User::where('accurate_id', $accurateId)
                    ->orWhere(function ($q) use ($email) {
                        $q->whereNull('accurate_id')->where('email', $email);
                    })->first();

                if (!$user) {
                    $user = new User();
                    $user->password = bcrypt('twincom@karyawan123');
                    $newUsers++;
                } else {
                    $updatedUsers++;
                }

                $user->accurate_id = $accurateId;
                $user->name = $employee['name'] ?? null;
                $user->email = $email;
                $user->status = 'KARYAWAN';
                $user->save();
                $totalUsers++;
            }

            $page++;
        } while (true);


        // ================================
        // 🔐 3. Tambahkan Admin Jika Belum Ada
        // ================================
        if (!User::where('email', 'admin@gmail.com')->exists()) {
            $this->info('🔧 Menambahkan akun admin default...');
            $user->accurate_id = $accurateId;
            $user->name = 'Administrator';
            $user->email = 'admin@gmail.com';
            $user->password = bcrypt('twincom@123');
            $user->status = 'admin';
            $user->save();
            $newUsers++;
            $totalUsers++;
        }

        // ================================
        // ✅ Ringkasan
        // ================================
        $this->info("✅ Sinkronisasi selesai.");
        $this->info("👤 Total user diproses: $totalUsers");
        $this->info("🆕 User baru dibuat: $newUsers");
        $this->info("♻️ User yang diperbarui: $updatedUsers");
    }
}
