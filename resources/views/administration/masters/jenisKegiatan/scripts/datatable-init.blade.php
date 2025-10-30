<script>
    const table = $('#example').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('administrator.master.jenis-kegiatan.list') }}', // Sesuaikan dengan route desa
            type: 'GET',
            data: function(d) {
                d.filter_status = $('#filter_status').val();
                d.filter_tahun = $('#filter_tahun').val();
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
                data: 'tahun_anggaran',
                name: 'tahun_anggaran',
                orderable: false,
                searchable: true
            },
            {
                data: 'kode_jenis',
                name: 'kode_jenis',
                orderable: false,
                searchable: true
            },
            {
                data: 'nama_jenis',
                name: 'nama_jenis',
                orderable: false,
                searchable: true
            },
            {
                data: 'jumlah_kegiatan',
                name: 'jumlah_kegiatan',
                orderable: true,
                searchable: false
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
    $('#filter_status, #filter_tahun').change(() => table.ajax.reload());

    // Select all checkbox
    $('#selectAll').on('click', function() {
        $('.table-checkbox:not(:disabled)').prop('checked', this.checked);
    });
</script>
