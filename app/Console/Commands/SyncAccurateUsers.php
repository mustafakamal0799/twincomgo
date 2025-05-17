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
        $this->info('⏳ Memulai sinkronisasi data dari Accurate...');

        $token = env('ACCURATE_API_TOKEN');
        $session = env('ACCURATE_SESSION');
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
                'fields' => 'id,name,email,suspended',
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

            foreach ($customers as $cust) {
                $accurateId = $cust['id'] ?? null;
                $email = $cust['email'] ?? null;
                if (!$accurateId || !$email) continue;

                if (!empty($cust['suspended']) && $cust['suspended'] === true) {
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
                    $user->password = bcrypt('twincom@reseller123');
                    $newUsers++;
                } else {
                    $updatedUsers++;
                }

                $user->accurate_id = $accurateId;
                $user->name = $cust['name'] ?? null;
                $user->email = $email;
                $user->status = 'reseller';
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
                $user->status = 'karyawan';
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
            User::create([
                'name' => 'Administator',
                'email' => 'admin@gmail.com',
                'password' => bcrypt('twincom@admin123'),
                'status' => 'admin',
            ]);
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
