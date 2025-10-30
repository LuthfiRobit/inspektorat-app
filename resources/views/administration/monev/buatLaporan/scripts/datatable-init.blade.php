<script>
    const table = $('#example').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('administrator.monev.laporan.list') }}',
            type: 'GET',
            data: function(d) {
                d.filter_status = $('#filter_status').val();
                d.filter_tahun = $('#filter_tahun').val();
                d.filter_desa = $('#filter_desa').val();
                d.search = d.search.value;
            }
        },
        columns: [{
                data: 'checkbox',
                orderable: false,
                searchable: false,
                className: 'text-center'
            },
            {
                data: 'aksi',
                orderable: false,
                searchable: false,
                className: 'text-center'
            },
            {
                data: 'tahun',
                name: 'tahun',
                orderable: true,
                searchable: true,
                className: 'text-start'
            },
            {
                data: 'bulan',
                name: 'bulan',
                orderable: true,
                searchable: false,
                className: 'text-center'
            },
            {
                data: 'nama_desa',
                name: 'nama_desa',
                orderable: true,
                searchable: true,
                className: 'text-start'
            },
            {
                data: 'nama_kegiatan',
                name: 'nama_kegiatan',
                orderable: true,
                searchable: true,
                className: 'text-start'
            },
            {
                data: 'timeline',
                name: 'timeline',
                orderable: false,
                searchable: false,
                className: 'text-center',
                width: '200px'
            },
            {
                data: 'status_display',
                name: 'status_display',
                orderable: true,
                searchable: false,
                className: 'text-center'
            },
        ],
        language: {
            searchPlaceholder: "Cari desa, kegiatan...",
            search: '',
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ hingga _END_ dari _TOTAL_ laporan",
            infoEmpty: "Menampilkan 0 hingga 0 dari 0 laporan",
            infoFiltered: "(disaring dari _MAX_ total laporan)",
            zeroRecords: "Tidak ada data laporan yang sesuai",
            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Selanjutnya",
                previous: "Sebelumnya"
            }
        },
        pageLength: 10,
        lengthMenu: [
            [10, 25, 50, 100],
            [10, 25, 50, 100]
        ],
        order: [
            [2, 'desc'],
            [3, 'desc']
        ], // Default order by tahun desc, bulan desc
        drawCallback: function(settings) {
            // Enable tooltips after table redraw
            $('[data-bs-toggle="tooltip"]').tooltip();
        }
    });

    // Debounce search
    const optimizedSearch = _.debounce(query => {
        if (query.length >= 3 || query.length === 0) {
            table.search(query).draw();
        }
    }, 500);

    $('#example_filter input').unbind().on('input', function() {
        optimizedSearch($(this).val());
    });

    // Reload saat filter berubah
    $('#filter_status, #filter_tahun, #filter_desa').change(() => {
        table.ajax.reload();
    });

    // Select all checkbox (hanya yang tidak disabled)
    $('#selectAll').on('click', function() {
        const isChecked = this.checked;
        $('.table-checkbox:not(:disabled)').prop('checked', isChecked);
    });

    // Enable tooltips on page load
    $(document).ready(function() {
        $('[data-bs-toggle="tooltip"]').tooltip();
    });

    // Auto-refresh data every 60 seconds untuk update status real-time
    // setInterval(() => {
    //     table.ajax.reload(null, false);
    // }, 60000);
</script>
