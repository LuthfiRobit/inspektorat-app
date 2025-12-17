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
                d.filter_periode = $('#filter_periode').val(); // <--- ditambahkan
                d.filter_desa = $('#filter_desa').val();
                d.search = d.search.value;
            }
        },
        columns: [
            // {
            //     data: 'checkbox',
            //     orderable: false,
            //     searchable: false,
            //     className: 'text-center'
            // },
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
        ],
        drawCallback: function() {
            $('[data-bs-toggle="tooltip"]').tooltip();
        }
    });

    // Debounce search
    const optimizedSearch = _.debounce(query => {
        if (query.length >= 3 || query.length === 0) {
            table.search(query).draw();
        }
    }, 500);

    $('#example_filter input').off('input').on('input', function() {
        optimizedSearch($(this).val());
    });

    // Reload saat filter berubah
    $('#filter_status, #filter_tahun, #filter_periode, #filter_desa').on('change', function() {
        table.ajax.reload();
    });

    // // Select all checkbox
    // $('#selectAll').on('click', function() {
    //     $('.table-checkbox:not(:disabled)').prop('checked', this.checked);
    // });

    // Enable tooltip
    $(document).ready(function() {
        $('[data-bs-toggle="tooltip"]').tooltip();
    });

    // Reset filter
    $('#btnResetFilter').on('click', function() {
        $('#filter_status').val('');
        $('#filter_tahun').val('');
        $('#filter_periode').val(''); // <--- reset filter bulan
        $('#filter_desa').val('');
        $('.selectpicker').selectpicker('refresh');
        table.ajax.reload();
    });
</script>
