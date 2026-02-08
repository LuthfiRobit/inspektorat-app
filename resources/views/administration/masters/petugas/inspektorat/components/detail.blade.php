<!-- Modal Detail Petugas Start -->
<div class="modal fade" id="modalDetail" tabindex="-1" role="dialog" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content shadow">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDetailLabel">Detail Petugas Inspektorat</h5>
                <button type="button" class="btn-close" aria-label="Close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <!-- FOTO DI KIRI & TEKS DI KANAN -->
                <div class="row mb-4 justify-content-center gy-2">
                    <div class="col-auto d-flex align-items-center">
                        <img id="detail_foto_petugas" src="" alt="Foto Petugas" class="img-thumbnail shadow-sm"
                            style="max-width: 120px;">
                    </div>
                    <div class="col-auto d-flex flex-column justify-content-center">
                        <h5 id="detail_nama_lengkap" class="fw-bold mb-1"></h5>
                        <p class="mb-1"><strong>NIP:</strong> <span id="detail_nip"></span></p>
                        <p class="mb-1"><strong>Jabatan:</strong> <span id="detail_jabatan"></span></p>
                        <p class="mb-0"><strong>Unit Kerja:</strong> <span id="detail_unit_kerja"></span></p>
                    </div>
                </div>

                <!-- INFORMASI DETAIL -->
                <div class="row row-cols-1 row-cols-md-2 g-3">

                    <!-- Kolom Kiri -->
                    <div class="col">
                        <div class="border-bottom pb-2"><strong>Tanggal Awal:</strong> <span
                                id="detail_tanggal_awal"></span></div>
                        <div class="border-bottom pb-2"><strong>Tanggal Akhir:</strong> <span
                                id="detail_tanggal_akhir"></span></div>
                        <div class="border-bottom pb-2"><strong>Kecamatan:</strong> <span
                                id="detail_nama_kecamatan"></span></div>
                        <div class="border-bottom pb-2"><strong>Desa:</strong> <span id="detail_nama_desa"></span></div>
                    </div>

                    <!-- Kolom Kanan -->
                    <div class="col">
                        <div class="border-bottom pb-2"><strong>Alamat:</strong> <span id="detail_alamat"></span></div>
                        <div class="border-bottom pb-2"><strong>No Telepon:</strong> <span id="detail_no_telp"></span>
                        </div>
                        <div class="border-bottom pb-2 mb-2"><strong>Email:</strong> <br><span id="detail_email"></span>
                        </div>
                        <div class="border-bottom pb-2 mb-2">
                            <strong>Status Petugas:</strong> <br><span id="detail_status"></span>
                        </div>
                        <div class="pt-2">
                            <strong>Akses Login:</strong> <br><span id="detail_user_status"></span>
                        </div>
                    </div>

                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
<!-- Modal Detail Petugas End -->