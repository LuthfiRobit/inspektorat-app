<script>
    $(document).ready(function () {

        /* -------------------------------------------------
         *  INIT DATATABLE
         * ------------------------------------------------- */
        window.table = $('#example').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('administrator.master.pertanyaan-kegiatan.list') }}',
                type: 'GET',
                data: function (d) {
                    d.filter_status   = $('#filter_status').val();
                    d.filter_kegiatan = $('#filter_kegiatan').val();
                    d.search          = d.search.value;
                }
            },
            columns: [
                { data: 'checkbox',   orderable: false, searchable: false },
                { data: 'aksi',       orderable: false, searchable: false },
                { data: 'kegiatan_info', name: 'kegiatan_info', orderable: false },
                { data: 'pertanyaan', name: 'pertanyaan',       orderable: true },
                { data: 'urutan',     name: 'urutan',           orderable: true,  className: 'text-center' },
                { data: 'status',     name: 'status',           orderable: false, className: 'text-center' }
            ],
            language: {
                searchPlaceholder: "Cari (min 4 karakter)...",
                search: ''
            }
        });


        /* -------------------------------------------------
         *  SEARCH DEBOUNCE
         * ------------------------------------------------- */
        const optimizedSearch = _.debounce(query => {
            if (query.length >= 4 || query.length === 0) {
                window.table.search(query).draw();
            }
        }, 3000);

        $('#example_filter input')
            .unbind()
            .on('input', function () {
                optimizedSearch($(this).val());
            });


        /* -------------------------------------------------
         *  FILTER CHANGE → RELOAD TABLE
         * ------------------------------------------------- */
        $('#filter_status, #filter_kegiatan')
            .change(() => window.table.ajax.reload());


        /* -------------------------------------------------
         *  RESET FILTER
         * ------------------------------------------------- */
        $('#btnResetFilter').click(function () {

            // Reset nilai dasar
            $('#filter_kegiatan').val('');
            $('#filter_status').val('');

            // Refresh semua selectpicker
            $('.selectpicker').selectpicker('refresh');

            // Reload table
            window.table.ajax.reload();
        });


        /* -------------------------------------------------
         *  SELECT ALL CHECKBOX
         * ------------------------------------------------- */
        $('#selectAll').on('click', function () {
            $('.table-checkbox:not(:disabled)').prop('checked', this.checked);
        });

    });
</script>
