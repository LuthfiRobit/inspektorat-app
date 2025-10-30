<!-- Modal Detail Start-->
<div class="modal fade" id="modalDetail" tabindex="-1" role="dialog" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDetailLabel">Detail Kecamatan</h5>
                <button type="button" class="btn-close" aria-label="Close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-12 mb-2">
                        <div class="row">
                            <div class="col-6"><span class="fs-6 fw-bold">Kode Kecamatan</span></div>
                            <div class="col-6"><span class="fs-6 fw-bold">:</span>
                                <span id="detail_kode_kecamatan" class="mb-0"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 mb-2">
                        <div class="row">
                            <div class="col-6"><span class="fs-6 fw-bold">Nama Kecamatan</span></div>
                            <div class="col-6"><span class="fs-6 fw-bold">:</span>
                                <span id="detail_nama_kecamatan" class="mb-0"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 mb-2">
                        <div class="row">
                            <div class="col-6"><span class="fs-6 fw-bold">Status</span></div>
                            <div class="col-6"><span class="fs-6 fw-bold">:</span>
                                <span id="detail_status" class="mb-0"></span>
                            </div>
                        </div>
                    </div>
                    <!-- Tambahan jika ingin menampilkan waktu dibuat atau diupdate -->
                    <!--
                    <div class="col-12 mb-2">
                        <div class="row">
                            <div class="col-6"><span class="fs-6 fw-bold">Dibuat Pada</span></div>
                            <div class="col-6"><span class="fs-6 fw-bold">:</span>
                                <span id="detail_created_at" class="mb-0"></span>
                            </div>
                        </div>
                    </div>
                    -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
<!-- Modal Detail end -->
