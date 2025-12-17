<?php

namespace App\Providers;

use App\Models\Desa;
use App\Models\Guru;
use App\Models\JabatanGuru;
use App\Models\JenisKegiatan;
use App\Models\Jurusan;
use App\Models\Kecamatan;
use App\Models\Role;
use App\Models\TahunAnggaran;
use App\Models\TahunPelajaran;
use App\Models\Tingkat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    // public function boot(): void
    // {
    //     // Menambahkan View Composer
    //     View::composer(['administration.masters.desa.*'], function ($view) {
    //         $kecamatanList = Kecamatan::getActive();
    //         // $statusGuruList = config('static-data.status_guru');
    //         // $pendidikanTerakhirList = config('static-data.pendidikan_terakhir');
    //         $view->with('kecamatanList', $kecamatanList);
    //         // ->with('statusGuruList', $statusGuruList)
    //         // ->with('pendidikanTerakhirList', $pendidikanTerakhirList);
    //     });

    //     View::composer(['administration.masters.jenisKegiatan.*'], function ($view) {
    //         $tahunAnggaranList = TahunAnggaran::select('id_tahun_anggaran', 'tahun', 'status')->get();
    //         $view->with('tahunAnggaranList', $tahunAnggaranList);
    //     });

    //     View::composer(['administration.masters.kegiatan.*'], function ($view) {
    //         $tahunAnggaranList = TahunAnggaran::select('id_tahun_anggaran', 'tahun', 'status')->get();
    //         $view->with('tahunAnggaranList', $tahunAnggaranList);
    //     });

    //     View::composer(['administration.masters.pertanyaanKegiatan.*'], function ($view) {

    //         $kegiatanList = DB::table('kegiatan')
    //             ->join('tahun_anggaran', 'kegiatan.tahun_anggaran_id', '=', 'tahun_anggaran.id_tahun_anggaran')
    //             ->select(
    //                 'kegiatan.id_kegiatan',
    //                 'tahun_anggaran.tahun',
    //                 'kegiatan.kode_kegiatan',
    //                 'kegiatan.nama_kegiatan'
    //             )
    //             ->where('kegiatan.status', 'active')
    //             ->orderBy('tahun_anggaran.tahun', 'desc')
    //             ->orderBy('kegiatan.kode_kegiatan', 'DESC')
    //             ->orderBy('kegiatan.kode_kegiatan')
    //             ->get();

    //         $view->with([
    //             'kegiatanList' => $kegiatanList,
    //         ]);
    //     });

    //     View::composer(['administration.masters.persyaratan.*'], function ($view) {

    //         $pertanyaanList = DB::table('pertanyaan_kegiatan as pk')
    //             ->join('kegiatan as k', 'pk.kegiatan_id', '=', 'k.id_kegiatan')
    //             ->select(
    //                 'pk.id_pertanyaan',
    //                 'pk.pertanyaan',
    //                 'k.id_kegiatan',
    //                 'k.kode_kegiatan',
    //                 'k.nama_kegiatan'
    //             )
    //             ->where('k.status', 'active')
    //             ->orderBy('k.tahun_anggaran_id', 'desc')
    //             ->orderBy('k.kode_kegiatan', 'ASC')
    //             ->orderBy('pk.pertanyaan', 'ASC')
    //             ->get();

    //         $kegiatanList = DB::table('kegiatan')
    //             ->join('tahun_anggaran', 'kegiatan.tahun_anggaran_id', '=', 'tahun_anggaran.id_tahun_anggaran')
    //             ->select(
    //                 'kegiatan.id_kegiatan',
    //                 'tahun_anggaran.tahun',
    //                 'kegiatan.kode_kegiatan',
    //                 'kegiatan.nama_kegiatan'
    //             )
    //             ->where('kegiatan.status', 'active')
    //             ->orderBy('tahun_anggaran.tahun', 'desc')
    //             ->orderBy('kegiatan.kode_kegiatan', 'ASC')
    //             ->orderBy('kegiatan.kode_kegiatan')
    //             ->get();

    //         $view->with([
    //             'kegiatanList' => $kegiatanList,
    //             'pertanyaanList' => $pertanyaanList,
    //         ]);
    //     });

    //     View::composer(['administration.masters.petugas.*'], function ($view) {
    //         $view->with([
    //             'jabatanInspektorat' => Role::getByScope('inspektorat'),
    //             'jabatanKecamatan' => Role::getByScope('kecamatan'),
    //             'jabatanDesa' => Role::getByScope('desa'),
    //         ]);
    //     });

    //     View::composer(['administration.masters.petugas.kecamatan.*'], function ($view) {
    //         $kecamatanList = Kecamatan::getActive();
    //         $view->with('kecamatanList', $kecamatanList);
    //     });

    //     View::composer(['administration.masters.petugas.desa.*'], function ($view) {
    //         $kecamatanList = Kecamatan::getActive();
    //         $view->with('kecamatanList', $kecamatanList);
    //     });

    //     View::composer(['administration.monitoring.*'], function ($view) {
    //         $tahunAnggaranList = TahunAnggaran::select('id_tahun_anggaran', 'tahun', 'status')->orderBy('tahun', 'DESC')->get();
    //         $kecamatanList = Kecamatan::getActive();
    //         $view->with('tahunAnggaranList', $tahunAnggaranList)
    //             ->with('kecamatanList', $kecamatanList);
    //     });
    // }

    public function boot(): void
    {
        /* ============================================================
         *  SHARED DATA (Reusable untuk banyak view)
         * ============================================================ */

        /** 1) TAHUN ANGGARAN — dipakai di banyak halaman */
        View::composer([
            'administration.masters.jenisKegiatan.*',
            'administration.masters.kegiatan.*',
            'administration.monitoring.*',
            'administration.monev.*',
            'administration.dashboard.*',
        ], function ($view) {

            static $tahunAnggaranList;

            if (!$tahunAnggaranList) {
                $tahunAnggaranList = TahunAnggaran::select('id_tahun_anggaran', 'tahun', 'status')
                    ->orderBy('tahun', 'DESC')
                    ->get();
            }

            $view->with('tahunAnggaranList', $tahunAnggaranList);
        });


        /** 2) KECAMATAN — dipakai di banyak modul */
        View::composer([
            'administration.masters.desa.*',
            'administration.masters.petugas.kecamatan.*',
            'administration.masters.petugas.desa.*',
            'administration.monitoring.*',
            'administration.dashboard.*',
        ], function ($view) {

            static $kecamatanList;

            if (!$kecamatanList) {
                $kecamatanList = Kecamatan::getActive();
            }

            $view->with('kecamatanList', $kecamatanList);
        });

        /** 3) DESA — hanya untuk modul monev (dioptimasi) */
        View::composer(
            ['administration.monitoring.*', 'administration.monev.*', 'administration.dashboard.*'],
            function ($view) {
                $user = Auth::user();
                $petugas = $user->petugas;

                // Ambil list desa global dari cache jika ada, atau dari DB
                $globalDesaList = Cache::remember('composer_desa_list', 300, function () {
                    return DB::table('desa as d')
                        ->join('kecamatan as k', 'd.kecamatan_id', '=', 'k.id_kecamatan')
                        ->select('d.id_desa', 'd.nama_desa', 'k.nama_kecamatan', 'd.kecamatan_id')
                        ->where('d.status', 'active')
                        ->orderBy('k.nama_kecamatan')
                        ->orderBy('d.nama_desa')
                        ->get();
                });

                // Tentukan list desa sesuai user
                if ($petugas && $petugas->kecamatan_id) {
                    // Filter dari global list tanpa query ulang
                    $desaList = $globalDesaList->where('kecamatan_id', $petugas->kecamatan_id)->values();
                } else {
                    // Admin / global → pakai cached global list
                    $desaList = $globalDesaList;
                }

                // Kirim ke view
                $view->with('desaList', $desaList);
            }
        );
        
        /** 3) ROLE PETUGAS — hanya untuk modul petugas */
        View::composer([
            'administration.masters.petugas.*'
        ], function ($view) {

            static $cachedRoles;

            if (!$cachedRoles) {
                $cachedRoles = [
                    'jabatanInspektorat' => Role::getByScope('inspektorat'),
                    'jabatanKecamatan'   => Role::getByScope('kecamatan'),
                    'jabatanDesa'        => Role::getByScope('desa'),
                ];
            }

            $view->with($cachedRoles);
        });


        /* ============================================================
         *  DATA KHUSUS PAGE TERTENTU
         * ============================================================ */


        /** 4) DATA UNTUK PERTANYAAN KEGIATAN */
        View::composer(['administration.masters.pertanyaanKegiatan.*'], function ($view) {

            // CACHE selama 1 menit agar tidak berat
            $kegiatanList = Cache::remember('composer_kegiatan_list', 60, function () {
                return DB::table('kegiatan')
                    ->join('tahun_anggaran', 'kegiatan.tahun_anggaran_id', '=', 'tahun_anggaran.id_tahun_anggaran')
                    ->select(
                        'kegiatan.id_kegiatan',
                        'tahun_anggaran.tahun',
                        'kegiatan.kode_kegiatan',
                        'kegiatan.nama_kegiatan'
                    )
                    ->where('kegiatan.status', 'active')
                    ->orderBy('tahun_anggaran.tahun', 'desc')
                    ->orderBy('kegiatan.kode_kegiatan', 'ASC')
                    ->get();
            });

            $view->with('kegiatanList', $kegiatanList);
        });


        /** 5) DATA UNTUK PERSYARATAN */
        View::composer(['administration.masters.persyaratan.*'], function ($view) {

            // Query pertanyaan & kegiatan biasanya berat → CACHE
            $pertanyaanList = Cache::remember('composer_pertanyaan_list', 60, function () {
                return DB::table('pertanyaan_kegiatan as pk')
                    ->join('kegiatan as k', 'pk.kegiatan_id', '=', 'k.id_kegiatan')
                    ->select(
                        'pk.id_pertanyaan',
                        'pk.pertanyaan',
                        'k.id_kegiatan',
                        'k.kode_kegiatan',
                        'k.nama_kegiatan'
                    )
                    ->where('k.status', 'active')
                    ->orderBy('k.tahun_anggaran_id', 'desc')
                    ->orderBy('k.kode_kegiatan')
                    ->orderBy('pk.pertanyaan')
                    ->get();
            });

            $kegiatanList = Cache::remember('composer_kegiatan_list_for_persyaratan', 60, function () {
                return DB::table('kegiatan')
                    ->join('tahun_anggaran', 'kegiatan.tahun_anggaran_id', '=', 'tahun_anggaran.id_tahun_anggaran')
                    ->select(
                        'kegiatan.id_kegiatan',
                        'tahun_anggaran.tahun',
                        'kegiatan.kode_kegiatan',
                        'kegiatan.nama_kegiatan'
                    )
                    ->where('kegiatan.status', 'active')
                    ->orderBy('tahun_anggaran.tahun', 'desc')
                    ->orderBy('kegiatan.kode_kegiatan', 'ASC')
                    ->get();
            });

            $view->with([
                'pertanyaanList' => $pertanyaanList,
                'kegiatanList'   => $kegiatanList,
            ]);
        });
    }
}
