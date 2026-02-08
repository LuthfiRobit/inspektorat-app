# Standard Logic: Keterlambatan & Scoring (v3)

Dokumen ini menjelaskan **Standar Logika Baru** yang disepakati untuk perhitungan Keterlambatan dan Scoring Desa, menyelaraskan logika dengan:
1.  **Controller Master Kegiatan** (`KegiatanController@store` logic).
2.  **User Requirements** (Perbedaan konstruksi tanggal Rutin vs Insidentil).

---

## 1. Konteks Data & Populate Logic

Berdasarkan `KegiatanController`, kolom database diisi sebagai berikut:

| Kolom DB | **Insidentil** (Input) | **Rutin** (Input) |
| :--- | :--- | :--- |
| `bulan` | Input User (`bulan`) | **Auto-filled** dari `bulan_mulai` |
| `bulan_mulai` | NULL | Input User (`bulan_mulai`) |
| `bulan_selesai` | NULL | Input User (`bulan_selesai`) |
| `tanggal_mulai` | Input User | Input User |
| `tanggal_selesai` | Input User | Input User |

---

## 2. Standardisasi Logic: Konstruksi Tanggal

Karena perbedaan referensi bulan, konstruksi tanggal **HARUS** dibedakan berdasarkan tipe kegiatan.

### A. Kegiatan Insidentil
*Referensi Bulan: Kolom `bulan`*

| Tanggal | Rumus Konstruksi |
| :--- | :--- |
| **Tanggal Mulai** | Tgl `tanggal_mulai` + Bulan `bulan` + Tahun `laporan_tahun` |
| **Tanggal Selesai** | Tgl `tanggal_selesai` + Bulan `bulan` + Tahun `laporan_tahun` |
| **Target / Deadline** | **Tanggal Selesai** + `batas_akhir_upload` (Hari) |

### B. Kegiatan Rutin
*Referensi Bulan: Split (`bulan_mulai` & `bulan_selesai`)*

> [!NOTE]
> Untuk laporan rutin, kita menggunakan bulan dari Laporan (`$laporan->bulan`) sebagai anchor untuk menentukan "Periode Ke-berapa" ini, tapi untuk **Deadline Dasar** (Static), kita melihat definisi Master.

| Tanggal | Rumus Konstruksi |
| :--- | :--- |
| **Tanggal Mulai** | Tgl `tanggal_mulai` + Bulan `bulan_mulai`* + Tahun `laporan_tahun` |
| **Tanggal Selesai** | Tgl `tanggal_selesai` + Bulan `bulan_selesai`* + Tahun `laporan_tahun` |
| **Target / Deadline** | **Tanggal Selesai** + `batas_akhir_upload` (Hari) |

*) **Catatan Penting untuk Laporan Rutin Per-Periode**:
Rumus di atas adalah untuk *Siklus/Periode Normal*. Dalam konteks **Laporan Bulanan/Triwulanan**, variabel bulan akan mengikuti **Bulan Laporan (`$laporan->bulan`)** sebagai bulan selesainya periode tersebut.

**Revisi Rumus Target Dinamis (Untuk Service Laporan):**
```php
if ($kegiatan->is_rutin) {
    // Rutin: Target basisnya adalah Akhir Periode Laporan
    // Jika laporan bulan Februari, maka deadline basisnya bulan Februari
    $bulanBasis = $laporan->bulan; 
} else {
    // Insidentil: Target basisnya adalah Bulan Kegiatan itu sendiri
    $bulanBasis = $kegiatan->bulan; // Atau $laporan->bulan (sama saja untuk insidentil)
}

$targetDate = Date($laporan->tahun, $bulanBasis, $kegiatan->tanggal_selesai) 
              + $kegiatan->batas_akhir_upload;
```

---

## 3. Standardisasi Logic: Scoring (Ketepatan Waktu)

Sistem mengadopsi logika **Monitoring Dashboard** (*Gradual Penalty*).

### Rumus
```php
HariTerlambat = (TanggalSubmit > TanggalTarget) ? DiffDays : 0;
Penalty = HariTerlambat * 10;
Skor = Max(0, 100 - Penalty);
```

### Tabel Simulasi
| Kondisi | Hari Terlambat | Perhitungan | Skor Akhir |
| :--- | :--- | :--- | :--- |
| Tepat Waktu | 0 | 100 - 0 | **100** |
| Telat 1 Hari | 1 | 100 - 10 | **90** |
| Telat 3 Hari | 3 | 100 - 30 | **70** |
| Telat 5 Hari | 5 | 100 - 50 | **50** |
| Telat 10 Hari+ | 10+ | 100 - 100 | **0** |

---

## 4. Rencana Implementasi

1.  **Refactor `LaporanKegiatanService::calculateTargetDate`**:
    - Implementasi percabangan logic `is_rutin` vs `insidentil` seperti poin 2.
    - Clamping tanggal (e.g., 30 Feb -> 29 Feb).

2.  **Refactor `ScoringDesaService`**:
    - Ubah rumus skor jadi Gradual Penalty.
    - Gunakan centralized date logic.

3.  **Refactor `KeterlambatanService`**:
    - Gunakan centralized date logic.
