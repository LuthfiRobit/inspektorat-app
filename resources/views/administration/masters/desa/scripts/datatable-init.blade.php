<script>
    const table = $('#example').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('administrator.master.desa.list') }}', // Sesuaikan dengan route desa
            type: 'GET',
            data: function(d) {
                d.filter_status = $('#filter_status').val();
                d.filter_kecamatan = $('#filter_kecamatan').val();
                d.search = d.search.value; // kirim query pencarian
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
                data: 'kode_desa',
                name: 'kode_desa',
                orderable: false,
                searchable: true
            },
            {
                data: 'nama_desa',
                name: 'nama_desa',
                orderable: false,
                searchable: true
            },
            {
                data: 'nama_kecamatan',
                name: 'kecamatan.nama_kecamatan',
                orderable: false,
                searchable: true
            },
            {
                data: 'status',
                name: 'status',
                orderable: false,
                searchable: false
            }
        ],
        language: {
            searchPlaceholder: "Cari (min 4 karakter)...",
            search: ''
        }
    });

    // Debounce pencarian agar tidak terlalu sering request
    const optimizedSearch = _.debounce(query => {
        if (query.length >= 4 || query.length === 0) table.search(query).draw();
    }, 3000);

    $('#example_filter input').unbind().on('input', function() {
        optimizedSearch($(this).val());
    });

    // Reload saat filter status diubah
    $('#filter_status, #filter_kecamatan').change(() => table.ajax.reload());

    // Select all checkbox
    $('#selectAll').on('click', function() {
        $('.table-checkbox:not(:disabled)').prop('checked', this.checked);
    });
</script>
