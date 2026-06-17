<script>
    let tableInspektorat = null;
    if ($('#table-inspektorat').length > 0) {
        // Initialize Datatable Binaan Inspektorat
        tableInspektorat = $('#table-inspektorat').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('administrator.master.wilayah-binaan.list-inspektorat') }}",
                type: 'GET',
                data: function(d) {
                    d.search = d.search.value;
                }
            },
            columns: [
                { 
                    data: null, 
                    name: 'no', 
                    orderable: false, 
                    searchable: false,
                    className: 'text-center align-middle',
                    render: function (data, type, row, meta) { 
                        return meta.row + meta.settings._iDisplayStart + 1; 
                    } 
                },
                { data: 'identitas', name: 'nama_lengkap', className: 'text-start align-middle' },
                { data: 'jabatan', name: 'jabatan', className: 'text-start align-middle', defaultContent: '-' },
                { 
                    data: 'kecamatan_binaan_count', 
                    name: 'kecamatan_binaan_count', 
                    searchable: false, 
                    orderable: false,
                    className: 'text-center align-middle',
                    render: function(data) {
                        return '<span class="badge light badge-primary font-w600">' + data + ' Kecamatan</span>';
                    }
                },
                { data: 'aksi', name: 'aksi', orderable: false, searchable: false, className: 'text-center align-middle' }
            ],
            language: {
                searchPlaceholder: "Cari (min 4)...",
                search: '',
                processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>'
            }
        });
    }

    // Initialize Datatable Binaan Kecamatan
    const tableKecamatan = $('#table-kecamatan').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('administrator.master.wilayah-binaan.list-kecamatan') }}",
            type: 'GET',
            data: function(d) {
                if ($('#filter_kecamatan').length > 0) {
                    d.kecamatan_id = $('#filter_kecamatan').val();
                }
                d.search = d.search.value;
            }
        },
        columns: [
            { 
                data: null, 
                name: 'no', 
                orderable: false, 
                searchable: false,
                className: 'text-center align-middle',
                render: function (data, type, row, meta) { 
                    return meta.row + meta.settings._iDisplayStart + 1; 
                } 
            },
            { data: 'identitas', name: 'nama_lengkap', className: 'text-start align-middle' },
            { data: 'jabatan', name: 'jabatan', className: 'text-start align-middle', defaultContent: '-' },
            { data: 'kecamatan.nama_kecamatan', name: 'kecamatan.nama_kecamatan', defaultContent: '-', className: 'text-start align-middle' },
            { 
                data: 'desa_binaan_count', 
                name: 'desa_binaan_count', 
                searchable: false, 
                orderable: false,
                className: 'text-center align-middle',
                render: function(data) {
                    return '<span class="badge light badge-info font-w600">' + data + ' Desa</span>';
                }
            },
            { data: 'aksi', name: 'aksi', orderable: false, searchable: false, className: 'text-center align-middle' }
        ],
        language: {
            searchPlaceholder: "Cari (min 4)...",
            search: '',
            processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>'
        }
    });

    // Debounce search input for Inspektorat Table
    const performSearchInspektorat = _.debounce(query => {
        if (tableInspektorat && (query.length >= 4 || query.length === 0)) tableInspektorat.search(query).draw();
    }, 1000);

    // Debounce search input for Kecamatan Table
    const performSearchKecamatan = _.debounce(query => {
        if (query.length >= 4 || query.length === 0) tableKecamatan.search(query).draw();
    }, 1000);

    // Bind custom search actions
    $('#table-inspektorat_filter input').unbind().on('input', function() {
        performSearchInspektorat($(this).val());
    });

    $('#table-kecamatan_filter input').unbind().on('input', function() {
        performSearchKecamatan($(this).val());
    });

    // Filter change listeners
    $('#filter_kecamatan').change(() => {
        tableKecamatan.ajax.reload();
        $('.selectpicker').selectpicker('refresh');
    });

    // Reset Filter Button
    $('#btnResetFilters').click(() => {
        $('#filter_kecamatan').val('').selectpicker('refresh');
        tableKecamatan.ajax.reload();
    });
</script>
