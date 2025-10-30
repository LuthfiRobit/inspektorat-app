<script>
    $(document).ready(function() {
        $('.selectpicker').selectpicker();

        let dataId = window.location.pathname.split('/').pop();
        let url = '{{ route('administrator.master.petugas.inspektorat.show', ':id') }}'.replace(':id', dataId);

        AjaxHandler.sendGetRequest(url, function(response) {
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

                $('.selectpicker').selectpicker('refresh');
            } else {
                ResponseHandler.handleError("Data tidak ditemukan.");
            }
        });
    });
</script>


<script>
    $("#editForm").on("submit", function(e) {
        e.preventDefault();
        let form = $(this);
        let dataId = form.attr('data-id');
        let url = '{{ route('administrator.master.petugas.inspektorat.update', ':id') }}'.replace(':id',
            dataId);

        AjaxHandler.sendUpdateRequest(url, this, function() {
            window.location.href = "{{ route('administrator.master.petugas.inspektorat.index') }}";
        }, function(response) {
            let errors = response.responseJSON?.data || {};
            ResponseHandler.handleValidationErrors(errors, form);
        });
    });
</script>
