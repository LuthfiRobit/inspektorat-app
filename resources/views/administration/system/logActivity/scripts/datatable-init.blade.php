  <script>
      //   $(document).ready(function() {
      // Initialize DataTable
      var table = $('#example').DataTable({
          processing: true,
          serverSide: true,
          ajax: {
              url: '{{ route('administrator.system.log-activity.list') }}', // Adjust to your route
              type: 'GET',
              data: function(d) {
                  d.filter_status = $('#filter_status').val(); // Send filter status
              }
          },
          columns: [{
                  data: 'name',
                  name: 'name',
                  orderable: false,
                  searchable: false
              },
              {
                  data: 'action',
                  name: 'action',
                  orderable: false,
                  searchable: true
              },
              {
                  data: 'description',
                  name: 'description',
                  orderable: false,
                  searchable: true
              },
              {
                  data: 'ip_address',
                  name: 'ip_address',
                  orderable: false,
                  searchable: false

              },
              {
                  data: 'user_agent',
                  name: 'user_agent',
                  orderable: false,
                  searchable: false

              },
              {
                  data: 'created_at',
                  name: 'created_at',
                  orderable: false,
                  searchable: false
              }
          ],
          language: {
              searchPlaceholder: "Cari (min 4)...",
              search: ''
          },
      });


      const performOptimizedSearch = _.debounce(function(query) {
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

      $('#filter_status').change(function() {
          table.ajax.reload(); // Reload DataTable with new filter
      });
      //   });
  </script>
