<script>
    $(document).ready(function () {
        $('.selectpicker').selectpicker();

        let dataId = window.location.pathname.split('/').pop();
        let url = '{{ route('administrator.master.petugas.inspektorat.show', ':id') }}'.replace(':id', dataId);

        AjaxHandler.sendGetRequest(url, function (response) {
            if (response.status === 200 && response.data) {
                let d = response.data;

                $('#editForm').attr('data-id', d.id_petugas);

                if (d.foto_petugas) {
                    let fotoPath = "{{ asset('') }}" + 'uploads/' + d.foto_petugas;

                    $('#link-container').html(
                        `<a href="${fotoPath}" class="d-flex align-items-center" id="lihat_foto" target="_blank">
                            <i class="fas fa-eye me-2"></i>
                            <span>Lihat Foto</span>
                        </a>`
                    );
                } else {
                    $('#link-container').empty();
                }

                if (d.user_status === 'active') {
                    $('#edit_akses_login_active').prop('checked', true);
                } else {
                    $('#edit_akses_login_inactive').prop('checked', true);
                }

                // Populate form fields
                $('#edit_nip').val(d.nip);
                $('#edit_nama_lengkap').val(d.nama_lengkap);
                $('#edit_jabatan').val(d.jabatan);
                $('#edit_unit_kerja').val(d.unit_kerja);
                $('#edit_alamat').val(d.alamat);
                $('#edit_no_telp').val(d.no_telp);
                $('#edit_email').val(d.email); // Gunakan email dari petugas/user relation
                $('#edit_status').val(d.status);

                // Date parsing for input type="date" (YYYY-MM-DD)
                if (d.tanggal_awal) {
                    $('#edit_tanggal_awal').val(d.tanggal_awal.split('T')[0]);
                }
                if (d.tanggal_akhir) {
                    $('#edit_tanggal_akhir').val(d.tanggal_akhir.split('T')[0]);
                }

                $('.selectpicker').selectpicker('refresh');
            } else {
                ResponseHandler.handleError("Data tidak ditemukan.");
            }
        });
    });
</script>


<script>
    $("#editForm").on("submit", function (e) {
        e.preventDefault();
        let form = $(this);
        let dataId = form.attr('data-id');
        let url = '{{ route('administrator.master.petugas.inspektorat.update', ':id') }}'.replace(':id',
            dataId);

        AjaxHandler.sendUpdateRequest(url, this, function () {
            window.location.href = "{{ route('administrator.master.petugas.inspektorat.index') }}";
        }, function (response) {
            let errors = response.responseJSON?.data || {};
            ResponseHandler.handleValidationErrors(errors, form);
        });
    });
</script>