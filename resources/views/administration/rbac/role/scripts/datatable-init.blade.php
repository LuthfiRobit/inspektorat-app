<script defer>
    const table = $('#example').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('administrator.rbac.role.list') }}',
            type: 'GET',
            data: d => {
                // Tambahkan filter jika diperlukan
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
                data: 'role_scope',
                orderable: true,
                searchable: true
            },
            {
                data: 'role_name',
                orderable: true,
                searchable: true
            },
            {
                data: 'role_description',
                orderable: false,
                searchable: false
            }
        ],
        language: {
            searchPlaceholder: "Cari (min 4)...",
            search: ''
        }
    });

    const performOptimizedSearch = _.debounce(query => {
        if (query.length >= 4 || query.length === 0) {
            table.search(query).draw();
        }
    }, 3000);

    $('#example_filter input').unbind().on('input', function() {
        performOptimizedSearch($(this).val());
    });
</script>
