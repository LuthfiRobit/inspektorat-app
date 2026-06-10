<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use App\Models\Permission;

class SyncPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-permissions {--prune : Hapus permission yang tidak ada di route}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronisasi permission dari route middleware ke database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Mulai pemindaian route untuk mencari permission middleware...');

        $routes = Route::getRoutes();
        $permissionsFound = [];

        foreach ($routes as $route) {
            $middlewares = $route->gatherMiddleware();

            foreach ($middlewares as $middleware) {
                // Cari middleware dengan format 'permission:nama-permission'
                if (is_string($middleware) && str_contains($middleware, 'permission:')) {
                    $segments = explode(':', $middleware);
                    if (count($segments) > 1) {
                        // Ambil nama permission (bisa multiple dipisahkan dengan |)
                        $permissionPart = $segments[1];
                        $names = explode('|', $permissionPart);
                        
                        foreach ($names as $name) {
                            $cleanName = trim($name);
                            if ($cleanName) {
                                $permissionsFound[] = $cleanName;
                            }
                        }
                    }
                }
            }
        }

        // Hapus duplikasi
        $uniquePermissions = array_unique($permissionsFound);

        if (empty($uniquePermissions)) {
            $this->warn('Tidak ditemukan permission pada middleware route manapun.');
            
            // Jika prune aktif dan route kosong, tanya konfirmasi sebelum hapus semua
            if ($this->option('prune') && Permission::exists()) {
                if ($this->confirm('Tidak ditemukan permission di route. Hapus SEMUA permission di database?')) {
                    Permission::query()->delete();
                    $this->info('Semua permission telah dihapus.');
                }
            }
            return;
        }

        $this->info('Ditemukan ' . count($uniquePermissions) . ' permission unik.');

        // 1. Tambahkan permission baru
        $this->info('Sinkronisasi (Create/Update)...');
        foreach ($uniquePermissions as $permissionName) {
            Permission::firstOrCreate(['permission_name' => $permissionName]);
            $this->line("  [✔] {$permissionName}");
        }

        // 2. Prune (Hapus yang tidak ada di route)
        if ($this->option('prune')) {
            $this->info('Membersihkan permission yang tidak terpakai...');
            $toDelete = Permission::whereNotIn('permission_name', $uniquePermissions)->get();
            
            if ($toDelete->count() > 0) {
                foreach ($toDelete as $p) {
                    $this->warn("  [✘] Menghapus: {$p->permission_name}");
                    // Hapus juga dari pivot jika perlu, tapi Eloquent bisa melakukannya via event atau constraint FK
                    $p->delete();
                }
                $this->info("Berhasil menghapus {$toDelete->count()} permission.");
            } else {
                $this->info('Tidak ada permission yang perlu dihapus.');
            }
        }

        $this->info('Sinkronisasi permission selesai!');
    }
}
