<script>
    // DataTable for Review List
    const table = $('#example').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('administrator.monev.review.list') }}',
            type: 'GET',
            data: function(d) {
                d.filter_status = $('#filter_status').val();
                d.filter_tahun = $('#filter_tahun').val();
                d.filter_desa = $('#filter_desa').val();
                d.search = d.search.value;
            }
        },
        columns: [{
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
            {
                data: 'review_info',
                name: 'review_info',
                orderable: false,
                searchable: false,
                className: 'text-center'
            }
        ],
        language: {
            searchPlaceholder: "Cari desa, kegiatan, kecamatan...",
            search: '',
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ hingga _END_ dari _TOTAL_ laporan",
            infoEmpty: "Menampilkan 0 hingga 0 dari 0 laporan",
            infoFiltered: "(disaring dari _MAX_ total laporan)",
            zeroRecords: "Tidak ada laporan yang perlu direview",
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
            [1, 'desc'], // Tahun desc
            [2, 'desc'] // Bulan desc
        ],
        drawCallback: function(settings) {
            // Enable tooltips after table redraw
            $('[data-bs-toggle="tooltip"]').tooltip();

            // Update counter for review items
            updateReviewCounter();
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

    // Enable tooltips on page load
    $(document).ready(function() {
        $('[data-bs-toggle="tooltip"]').tooltip();
    });

    // Function to update review counter
    function updateReviewCounter() {
        const needReviewCount = table.column(6).data().filter(function(value, index) {
            return value.includes('Butuh Review');
        }).count();

        const inRevisionCount = table.column(6).data().filter(function(value, index) {
            return value.includes('Sedang Direvisi');
        }).count();

        // Update badge counters if they exist in your UI
        $('.badge-review-count').text(needReviewCount);
        $('.badge-revision-count').text(inRevisionCount);
    }
</script>
