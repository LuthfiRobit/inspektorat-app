<script>
    const table = $('#example').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('administrator.master.petugas.inspektorat.list') }}',
            type: 'GET',
            data: function (d) {
                d.filter_status = $('#filter_status').val();
                d.filter_kecamatan = $('#filter_kecamatan').val();
                d.filter_desa = $('#filter_desa').val();
                d.filter_jabatan = $('#filter_jabatan').val();
                d.search = d.search.value;
            }
        },
        columns: [{
            data: 'checkbox',
            orderable: false,
            searchable: false,
            className: 'text-center align-middle',
        },
        {
            data: 'aksi',
            orderable: false,
            searchable: false,
            className: 'text-center align-middle',
        },
        {
            data: 'nip',
            name: 'nip',
            className: 'text-start align-middle',
        },
        {
            data: 'nama_lengkap',
            name: 'nama_lengkap',
            className: 'text-start align-middle',
        },
        {
            data: 'no_telp',
            name: 'no_telp',
            className: 'text-start align-middle',
        },
        {
            data: 'jabatan',
            name: 'jabatan',
            className: 'text-start align-middle',
        },
        {
            data: 'instansi',
            orderable: false,
            searchable: true,
            className: 'text-start align-middle',
        },
        {
            data: 'unit_kerja',
            name: 'unit_kerja',
            className: 'text-start align-middle',
        },
        {
            data: 'user_status',
            name: 'user_status',
            orderable: false,
            searchable: false,
            className: 'text-center align-middle',
        },
        {
            data: 'status',
            name: 'status',
            orderable: false,
            searchable: false,
            className: 'text-center align-middle',
        },
        ],
        language: {
            searchPlaceholder: "Cari (min 4 karakter)...",
            search: '',
            processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
            emptyTable: "Tidak ada data petugas.",
            zeroRecords: "Tidak ditemukan data yang sesuai.",
            lengthMenu: "Tampilkan _MENU_ data per halaman",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Tidak ada data tersedia",
        },
        drawCallback: function (settings) {
            // Re-enable tooltip setiap kali tabel di-render ulang
            $('[data-bs-toggle="tooltip"]').tooltip();
        }
    });

    // const optimizedSearch = _.debounce(query => {
    //     if (query.length >= 4 || query.length === 0) table.search(query).draw();
    // }, 1000);

    $('#example_filter input').unbind().on('input', function () {
        optimizedSearch($(this).val());
    });

    $('#filter_status, #filter_kecamatan, #filter_desa, #filter_jabatan').change(() => table.ajax.reload());

    $('#selectAll').on('click', function () {
        $('.table-checkbox:not(:disabled)').prop('checked', this.checked);
    });
</script>