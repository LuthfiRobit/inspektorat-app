<script>
    $(document).ready(function () {
        $('.selectpicker').selectpicker();

        let dataId = window.location.pathname.split('/').pop();
        let url = '{{ route('administrator.master.petugas.kecamatan.show', ':id') }}'.replace(':id', dataId);

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
        let url = '{{ route('administrator.master.petugas.kecamatan.update', ':id') }}'.replace(':id',
            dataId);

        AjaxHandler.sendUpdateRequest(url, this, function () {
            window.location.href = "{{ route('administrator.master.petugas.kecamatan.index') }}";
        }, function (response) {
            let errors = response.responseJSON?.data || {};
            ResponseHandler.handleValidationErrors(errors, form);
        });
    });
</script>