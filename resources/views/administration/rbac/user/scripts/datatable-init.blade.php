<script>
    const table = $('#example').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('administrator.rbac.user.list') }}',
            type: 'GET',
            data: d => {
                d.filter_status = $('#filter_status').val();
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
                data: 'name',
                orderable: true,
                searchable: true
            },
            {
                data: 'username',
                orderable: true,
                searchable: true
            },
            {
                data: 'email',
                orderable: true,
                searchable: true
            },
            {
                data: 'status',
                orderable: false,
                searchable: false
            },
        ],
        language: {
            searchPlaceholder: "Cari pengguna (min 4 karakter)...",
            search: ''
        }
    });

    const performOptimizedSearch = _.debounce(query => {
        if (query.length >= 4 || query.length === 0) table.search(query).draw();
    }, 3000);

    $('#example_filter input').unbind().on('input', function() {
        performOptimizedSearch($(this).val());
    });

    $('#filter_status').change(() => table.ajax.reload());

    $('#selectAll').click(function() {
        $('.table-checkbox:not(:disabled)').prop('checked', this.checked);
    });
</script>
