<script>
    const table = $('#example').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('administrator.master.persyaratan.list') }}',
            type: 'GET',
            data: function(d) {
                d.filter_status = $('#filter_status').val();
                d.filter_tipe = $('#filter_tipe').val(); // ganti kegiatan jadi tipe
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
                data: 'pertanyaan_info',
                name: 'pertanyaan_info',
                orderable: false
            },
            {
                data: 'nama_persyaratan',
                name: 'nama_persyaratan',
                orderable: false
            },
            {
                data: 'tipe',
                name: 'tipe',
                className: 'text-center',
                orderable: false
            },
            {
                data: 'urutan',
                name: 'urutan',
                className: 'text-center',
                orderable: true
            },
            {
                data: 'status',
                name: 'status',
                className: 'text-center',
                orderable: false
            }
        ],
        order: [
            [5, 'asc']
        ],
        language: {
            searchPlaceholder: "Cari (min 4 karakter)...",
            search: ''
        }
    });

    // Debounce search agar performa lebih baik
    const optimizedSearch = _.debounce(query => {
        if (query.length >= 4 || query.length === 0) {
            table.search(query).draw();
        }
    }, 500); // lebih responsif dari 3 detik

    // Ubah behavior input pencarian global
    $('#example_filter input').unbind().on('input', function() {
        optimizedSearch($(this).val());
    });

    // Reload saat filter berubah
    $('#filter_status, #filter_tipe').on('change', () => {
        table.ajax.reload();
    });

    // Select all checkbox
    $('#selectAll').on('click', function() {
        $('.table-checkbox:not(:disabled)').prop('checked', this.checked);
    });
</script>
