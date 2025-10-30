<!-- Modal Detail Start -->
<div class="modal fade" id="modalDetail" tabindex="-1" role="dialog" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Kegiatan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-12 mb-2">
                        <div class="row">
                            <div class="col-4 fw-bold">Tahun Anggaran / Periode</div>
                            <div class="col-8">: <span id="detail_tahun"></span> / <span id="detail_nama_bulan"></span>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 mb-2">
                        <div class="row">
                            <div class="col-4 fw-bold">Jenis Kegiatan</div>
                            <div class="col-8">: <span id="detail_kode_jenis"></span> | <span
                                    id="detail_nama_jenis"></span></div>
                        </div>
                    </div>

                    <div class="col-12 mb-2">
                        <div class="row">
                            <div class="col-4 fw-bold">Kegiatan</div>
                            <div class="col-8">: <span id="detail_nama_kegiatan"></span></div>
                        </div>
                    </div>

                    <div class="col-12 mb-2">
                        <div class="row">
                            <div class="col-4 fw-bold">Tanggal Mulai</div>
                            <div class="col-8">: <span id="detail_tanggal_mulai"></span></div>
                        </div>
                    </div>

                    <div class="col-12 mb-2">
                        <div class="row">
                            <div class="col-4 fw-bold">Tanggal Selesai</div>
                            <div class="col-8">: <span id="detail_tanggal_selesai"></span></div>
                        </div>
                    </div>

                    <div class="col-12 mb-2">
                        <div class="row">
                            <div class="col-4 fw-bold">Batas Akhir Upload</div>
                            <div class="col-8">: <span id="detail_batas_upload"></span></div>
                        </div>
                    </div>

                    <div class="col-12 mb-2">
                        <div class="row">
                            <div class="col-4 fw-bold">Status</div>
                            <div class="col-8">: <span id="detail_status"></span></div>
                        </div>
                    </div>

                    <div class="col-12 mb-2">
                        <div class="row">
                            <div class="col-4 fw-bold">Dasar Hukum</div>
                            <div class="col-8">: <span id="detail_dasar_hukum"></span></div>
                        </div>
                    </div>

                    <div class="col-12 mb-2">
                        <div class="mb-3">
                            <div class="fw-bold mb-2">List Pertanyaan</div>
                            <div id="detail_list_pertanyaan"></div>
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
<!-- Modal Detail End -->
