<script>
    const table = $('#example').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('administrator.master.kecamatan.list') }}', // Ganti ke route kecamatan yang benar
            type: 'GET',
            data: function(d) {
                d.filter_status = $('#filter_status').val();
                d.search = d.search.value; // pastikan search query ikut terkirim
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
                data: 'kode_kecamatan',
                orderable: false,
                searchable: true
            },
            {
                data: 'nama_kecamatan',
                orderable: false,
                searchable: true
            },
            {
                data: 'status',
                orderable: false,
                searchable: false
            }
        ],
        language: {
            searchPlaceholder: "Cari (min 4)...",
            search: ''
        }
    });

    // Debounce search biar gak tiap ketik langsung ajax request
    const performOptimizedSearch = _.debounce(query => {
        if (query.length >= 4 || query.length === 0) table.search(query).draw();
    }, 3000);

    $('#example_filter input').unbind().on('input', function() {
        performOptimizedSearch($(this).val());
    });

    // Filter status onchange reload table
    $('#filter_status').change(() => table.ajax.reload());

    // Select all checkbox
    $('#selectAll').click(function() {
        $('.table-checkbox:not(:disabled)').prop('checked', this.checked);
    });
</script>
