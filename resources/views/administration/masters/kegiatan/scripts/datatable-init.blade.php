<script>
    $(document).ready(function () {

        /* -------------------------------------------------
         *  INIT DATATABLE
         * ------------------------------------------------- */
        const table = $('#example').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('administrator.master.kegiatan.list') }}',
                type: 'GET',
                data: function (d) {
                    d.filter_status = $('#filter_status').val();
                    d.filter_tahun = $('#filter_tahun').val();
                    d.filter_jenis = $('#filter_jenis').val();
                    d.filter_bulan = $('#filter_bulan').val();
                    d.search = d.search.value;
                }
            },
            columns: [
                { data: 'checkbox', orderable: false, searchable: false },
                { data: 'aksi', orderable: false, searchable: false },
                { data: 'tahun', name: 'tahun', searchable: true },
                { data: 'nama_bulan', searchable: false },
                { data: 'kode_kegiatan', searchable: true },
                { data: 'nama_kegiatan', searchable: true },
                { data: 'jumlah_pertanyaan', searchable: false },
                { data: 'status', searchable: false }
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
                table.search(query).draw();
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
        $('#filter_status, #filter_tahun, #filter_jenis, #filter_bulan')
            .change(() => table.ajax.reload());


        /* -------------------------------------------------
         *  RESET FILTER
         * ------------------------------------------------- */
        $('#btnResetFilter').click(function () {

            // Reset nilai dasar
            $('#filter_tahun').val('');
            $('#filter_bulan').val('');
            $('#filter_status').val('');

            // Reset jenis + hapus semua opsi
            $('#filter_jenis')
                .html('<option value="">Semua Jenis</option>')
                .val('');

            // Refresh semua selectpicker
            $('.selectpicker').selectpicker('refresh');

            // Reload table
            table.ajax.reload();
        });


        /* -------------------------------------------------
         *  SELECT ALL CHECKBOX
         * ------------------------------------------------- */
        $('#selectAll').on('click', function () {
            $('.table-checkbox:not(:disabled)').prop('checked', this.checked);
        });


        /* -------------------------------------------------
         *  DEPENDENT DROPDOWN (Tahun → Jenis)
         * ------------------------------------------------- */
        DropdownHelper.bindDependentDropdown(
            '#filter_tahun',
            '#filter_jenis',
            '{{ route('administrator.master.jenis-kegiatan.list-by-tahun', ':id') }}',
            (item, selectedValue) => {
                const selected = selectedValue == item.id_jenis_kegiatan ? 'selected' : '';
                return `<option value="${item.id_jenis_kegiatan}" ${selected}>
                            ${item.kode_jenis} - ${item.nama_jenis}
                        </option>`;
            }
        );

    });
</script>
<!-- End of embedded-html://html/embeded.embeddedhtml -->