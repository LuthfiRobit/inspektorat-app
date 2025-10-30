<?php

namespace Database\Seeders;

use App\Models\Desa;
use App\Models\Kecamatan;
use App\Models\Petugas;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PetugasSeeder extends Seeder
{
    public function run(): void
    {
        // --- Pastikan role tersedia ---
        $roles = [
            'inspektorat' => Role::firstOrCreate(['role_name' => 'inspektorat'], ['role_description' => 'Petugas Inspektorat']),
            'kecamatan'   => Role::firstOrCreate(['role_name' => 'kecamatan'], ['role_description' => 'Petugas Kecamatan']),
            'desa'        => Role::firstOrCreate(['role_name' => 'desa'], ['role_description' => 'Petugas Desa']),
        ];

        $kecamatanList = Kecamatan::where('status', 'active')->get()->take(2);

        foreach ($kecamatanList as $kecamatan) {
            // -- 1 user Inspektorat untuk setiap kecamatan --
            $this->createPetugasUser([
                'role' => $roles['inspektorat'],
                'name' => 'Inspektorat ' . $kecamatan->nama_kecamatan,
                'kecamatan_id' => null,
                'desa_id' => null,
                'unit_kerja' => 'Inspektorat Kabupaten',
            ]);

            // -- 2 user Kecamatan --
            for ($i = 1; $i <= 2; $i++) {
                $this->createPetugasUser([
                    'role' => $roles['kecamatan'],
                    'name' => "Petugas Kecamatan {$kecamatan->nama_kecamatan} {$i}",
                    'kecamatan_id' => $kecamatan->id_kecamatan,
                    'desa_id' => null,
                    'unit_kerja' => "Kantor Kecamatan {$kecamatan->nama_kecamatan}",
                ]);
            }

            // -- 2 user Desa per desa aktif di kecamatan --
            $desaList = Desa::where('kecamatan_id', $kecamatan->id_kecamatan)
                ->where('status', 'active')
                ->get()->take(2);

            foreach ($desaList as $desa) {
                for ($j = 1; $j <= 2; $j++) {
                    $this->createPetugasUser([
                        'role' => $roles['desa'],
                        'name' => "Petugas Desa {$desa->nama_desa} {$j}",
                        'kecamatan_id' => $kecamatan->id_kecamatan,
                        'desa_id' => $desa->id_desa,
                        'unit_kerja' => "Kantor Desa {$desa->nama_desa}",
                    ]);
                }
            }
        }

        $this->command->info('✅ Semua petugas dan user berhasil dibuat & di-assign role.');
    }

    protected function createPetugasUser(array $data): void
    {
        // Buat username & email unik
        $baseUsername = strtolower(str_replace(' ', '_', $data['name']));
        $username = preg_replace('/[^a-z0-9_]/', '', $baseUsername);
        $email = $username . '@example.com';
        $counter = 1;

        while (User::where('username', $username)->exists()) {
            $username = $baseUsername . $counter;
            $email = $username . '@example.com';
            $counter++;
        }

        // Buat user
        $user = User::create([
            'name' => $data['name'],
            'username' => $username,
            'email' => $email,
            'password' => Hash::make('password123'),
            'status' => 'active',
        ]);

        $user->roles()->attach($data['role']->id_role);

        // Buat petugas
        Petugas::create([
            'user_id' => $user->id_user,
            'kecamatan_id' => $data['kecamatan_id'],
            'desa_id' => $data['desa_id'],
            'nama_lengkap' => $data['name'],
            'nip' => fake()->numerify('19###########'),
            'jabatan' => 'Petugas ' . ucfirst($data['role']->role_name),
            'unit_kerja' => $data['unit_kerja'],
            'no_telp' => fake()->phoneNumber(),
            'alamat' => fake()->address(),
            'status' => 'active',
        ]);
    }
}
