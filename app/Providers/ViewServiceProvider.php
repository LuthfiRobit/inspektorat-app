<?php

namespace App\Providers;

use App\Models\Guru;
use App\Models\JabatanGuru;
use App\Models\JenisKegiatan;
use App\Models\Jurusan;
use App\Models\Kecamatan;
use App\Models\Role;
use App\Models\TahunAnggaran;
use App\Models\TahunPelajaran;
use App\Models\Tingkat;
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
    public function boot(): void
    {
        // Menambahkan View Composer
        View::composer(['administration.masters.desa.*'], function ($view) {
            $kecamatanList = Kecamatan::getActive();
            // $statusGuruList = config('static-data.status_guru');
            // $pendidikanTerakhirList = config('static-data.pendidikan_terakhir');
            $view->with('kecamatanList', $kecamatanList);
            // ->with('statusGuruList', $statusGuruList)
            // ->with('pendidikanTerakhirList', $pendidikanTerakhirList);
        });

        View::composer(['administration.masters.jenisKegiatan.*'], function ($view) {
            $tahunAnggaranList = TahunAnggaran::select('id_tahun_anggaran', 'tahun', 'status')->get();
            $view->with('tahunAnggaranList', $tahunAnggaranList);
        });

        View::composer(['administration.masters.kegiatan.*'], function ($view) {
            $tahunAnggaranList = TahunAnggaran::select('id_tahun_anggaran', 'tahun', 'status')->get();
            $view->with('tahunAnggaranList', $tahunAnggaranList);
        });

        View::composer(['administration.masters.pertanyaanKegiatan.*'], function ($view) {

            $kegiatanList = DB::table('kegiatan')
                ->join('tahun_anggaran', 'kegiatan.tahun_anggaran_id', '=', 'tahun_anggaran.id_tahun_anggaran')
                ->select(
                    'kegiatan.id_kegiatan',
                    'tahun_anggaran.tahun',
                    'kegiatan.kode_kegiatan',
                    'kegiatan.nama_kegiatan'
                )
                ->orderBy('tahun_anggaran.tahun', 'desc')
                ->orderBy('kegiatan.kode_kegiatan', 'ASC')
                ->orderBy('kegiatan.kode_kegiatan')
                ->get();

            $view->with([
                'kegiatanList' => $kegiatanList,
            ]);
        });

        View::composer(['administration.masters.persyaratan.*'], function ($view) {

            $kegiatanList = DB::table('kegiatan')
                ->join('tahun_anggaran', 'kegiatan.tahun_anggaran_id', '=', 'tahun_anggaran.id_tahun_anggaran')
                ->select(
                    'kegiatan.id_kegiatan',
                    'tahun_anggaran.tahun',
                    'kegiatan.kode_kegiatan',
                    'kegiatan.nama_kegiatan'
                )
                ->orderBy('tahun_anggaran.tahun', 'desc')
                ->orderBy('kegiatan.kode_kegiatan', 'ASC')
                ->orderBy('kegiatan.kode_kegiatan')
                ->get();

            $view->with([
                'kegiatanList' => $kegiatanList,
            ]);
        });

        View::composer(['administration.masters.petugas.*'], function ($view) {
            $view->with([
                'jabatanInspektorat' => Role::getByScope('inspektorat'),
                'jabatanKecamatan'   => Role::getByScope('kecamatan'),
                'jabatanDesa'        => Role::getByScope('desa'),
            ]);
        });

        View::composer(['administration.masters.petugas.kecamatan.*'], function ($view) {
            $kecamatanList = Kecamatan::getActive();
            $view->with('kecamatanList', $kecamatanList);
        });

        View::composer(['administration.masters.petugas.desa.*'], function ($view) {
            $kecamatanList = Kecamatan::getActive();
            $view->with('kecamatanList', $kecamatanList);
        });
    }
}
