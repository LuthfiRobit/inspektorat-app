<script>
    $(document).ready(function () {
        $('.selectpicker').selectpicker();

        let dataId = window.location.pathname.split('/').pop();
        let url = '{{ route('administrator.master.petugas.desa.show', ':id') }}'.replace(':id', dataId);

        AjaxHandler.sendGetRequest(url, function (response) {
            if (response.status === 200 && response.data) {
                let d = response.data;

                $('#editForm').attr('data-id', d.id_petugas);

                $('#edit_nip').val(d.nip);
                $('#edit_nama_lengkap').val(d.nama_lengkap);
                $('#edit_usia').val(d.usia);
                $('#edit_jabatan').val(d.jabatan).selectpicker('refresh');
                $('#edit_unit_kerja').val(d.unit_kerja);
                $('#edit_alamat').val(d.alamat);
                $('#edit_no_telp').val(d.no_telp);
                $('#edit_email').val(d.email);
                $('#edit_status').val(d.status).selectpicker('refresh');

                // Parsing tanggal agar sesuai dengan format input date (YYYY-MM-DD)
                const formatDate = (dateString) => {
                    if (!dateString) return '';
                    return dateString.split('T')[0]; // Ambil bagian tanggal saja
                };

                $('#edit_tanggal_awal').val(formatDate(d.tanggal_awal));
                $('#edit_tanggal_akhir').val(formatDate(d.tanggal_akhir));

                $('#edit_kecamatan_id').val(d.kecamatan_id).selectpicker('refresh');

                // Bind Jenis Kegiatan berdasarkan Tahun Anggaran, dan auto-select jenis kegiatan yang sesuai
                DropdownHelper.bindDependentDropdown(
                    '#edit_kecamatan_id',
                    '#edit_desa_id',
                    '{{ route('administrator.master.desa.list-by-kecamatan', ':id') }}',
                    function (item, selectedId) {
                        const selected = selectedId == item.id_desa ? 'selected' : '';
                        return `<option value="${item.id_desa}" ${selected}>${item.nama_desa}</option>`;
                    },
                    d.desa_id // <= ini harus angka/id yang cocok
                );

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
        let url = '{{ route('administrator.master.petugas.desa.update', ':id') }}'.replace(':id',
            dataId);

        AjaxHandler.sendUpdateRequest(url, this, function () {
            window.location.href = "{{ route('administrator.master.petugas.desa.index') }}";
        }, function (response) {
            let errors = response.responseJSON?.data || {};
            ResponseHandler.handleValidationErrors(errors, form);
        });
    });
</script>