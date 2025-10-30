<script>
    const table = $('#example').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('administrator.master.pertanyaan-kegiatan.list') }}',
            type: 'GET',
            data: function(d) {
                d.filter_status = $('#filter_status').val();
                d.filter_kegiatan = $('#filter_kegiatan').val(); // tambahan
                d.search = d.search.value;
            }
        },
        columns: [{
                data: 'checkbox',
                orderable: false,
                searchable: false
            },
            {
                data: 'aksi',
                orderable: false,
                searchable: false
            },
            {
                data: 'kegiatan_info',
                name: 'kegiatan_info',
                orderable: false
            },
            {
                data: 'pertanyaan',
                name: 'pertanyaan',
                orderable: true
            },
            {
                data: 'urutan',
                name: 'urutan',
                orderable: true,
                className: 'text-center'
            },
            {
                data: 'status',
                name: 'status',
                orderable: false,
                className: 'text-center'
            }
        ],
        language: {
            searchPlaceholder: "Cari (min 4 karakter)...",
            search: ''
        }
    });

    // Debounce search
    const optimizedSearch = _.debounce(query => {
        if (query.length >= 4 || query.length === 0) {
            table.search(query).draw();
        }
    }, 3000);

    $('#example_filter input').unbind().on('input', function() {
        optimizedSearch($(this).val());
    });

    // Reload saat filter berubah
    $('#filter_status').change(() => table.ajax.reload());
    $('#filter_kegiatan').change(() => table.ajax.reload()); // tambahan

    // Select all checkbox
    $('#selectAll').on('click', function() {
        $('.table-checkbox:not(:disabled)').prop('checked', this.checked);
    });
</script>
