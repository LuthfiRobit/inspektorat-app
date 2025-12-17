<script>
    $(document).ready(function () {

        /* -------------------------------------------------
         *  INIT DATATABLE
         * ------------------------------------------------- */
        const table = $('#example').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('administrator.master.persyaratan.list') }}',
                type: 'GET',
                data: function (d) {
                    d.filter_status      = $('#filter_status').val();
                    d.filter_tipe        = $('#filter_tipe').val();
                    d.filter_pertanyaan  = $('#filter_pertanyaan').val();
                    d.search             = d.search.value;
                }
            },
            columns: [
                { data: 'checkbox',          orderable: false, searchable: false },
                { data: 'aksi',              orderable: false, searchable: false },
                { data: 'pertanyaan_info',   orderable: false },
                { data: 'nama_persyaratan',  orderable: false },
                { data: 'tipe',              className: 'text-center', orderable: false },
                { data: 'urutan',            className: 'text-center', orderable: true  },
                { data: 'status',            className: 'text-center', orderable: false }
            ],
            order: [[5, 'asc']],
            language: {
                searchPlaceholder: "Cari (min 4 karakter)...",
                search: ''
            }
        });


        /* -------------------------------------------------
         *  DEBOUNCED SEARCH
         * ------------------------------------------------- */
        const optimizedSearch = _.debounce(query => {
            if (query.length >= 4 || query.length === 0) {
                table.search(query).draw();
            }
        }, 500);

        $('#example_filter input')
            .unbind()
            .on('input', function () {
                optimizedSearch($(this).val());
            });


        /* -------------------------------------------------
         *  FILTER CHANGE → RELOAD TABLE
         * ------------------------------------------------- */
        $('#filter_status, #filter_tipe, #filter_pertanyaan')
            .on('change', () => {
                table.ajax.reload();
            });


        /* -------------------------------------------------
         *  RESET FILTER
         * ------------------------------------------------- */
        $('#btnResetFilter').click(function () {

            $('#filter_tipe').val('');
            $('#filter_status').val('');
            $('#filter_pertanyaan').val('');

            $('.selectpicker').selectpicker('refresh');

            table.ajax.reload();
        });


        /* -------------------------------------------------
         *  SELECT ALL CHECKBOX
         * ------------------------------------------------- */
        $('#selectAll').on('click', function () {
            $('.table-checkbox:not(:disabled)').prop('checked', this.checked);
        });

    });
</script>
