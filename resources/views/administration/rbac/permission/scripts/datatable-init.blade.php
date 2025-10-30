<script>
    const table = $('#example').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('administrator.rbac.permission.list') }}',
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
                searchable: true
            },
            {
                data: 'permission_name',
                orderable: false,
                searchable: true
            },
            {
                data: 'permission_description',
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
        try {
            if (query.length >= 4 || query.length === 0) {
                table.search(query).draw();
            }
        } catch (error) {
            console.error("Error during search:", error);
        }
    }, 3000);

    $('#example_filter input').unbind().on('input', function() {
        performOptimizedSearch($(this).val());
    });
</script>
