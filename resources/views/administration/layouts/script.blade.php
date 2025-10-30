<script>
    function updateTime() {
        var currentTime = new Date();
        var hours = currentTime.getHours();
        var minutes = currentTime.getMinutes();
        var seconds = currentTime.getSeconds();

        // Add leading zero for single digit minutes/seconds
        minutes = minutes < 10 ? '0' + minutes : minutes;
        seconds = seconds < 10 ? '0' + seconds : seconds;

        // Format time in HH:mm:ss
        var timeString = hours + ':' + minutes + ':' + seconds;

        // Set the time in the HTML element
        document.getElementById('current-time').textContent = timeString;
    }

    // Update time immediately and every second thereafter
    updateTime();
    setInterval(updateTime, 1000);
</script>

<script defer>
    $(document).ready(function() {
        // Menambahkan listener untuk semua selectpicker dengan required
        $('.selectpicker[required]').each(function() {
            var $selectPicker = $(this);

            // Menangani validasi otomatis ketika selectpicker tidak dipilih
            $selectPicker.on('invalid', function() {
                if ($selectPicker.val() === "") {
                    $selectPicker.addClass('is-invalid');
                    // Menambahkan pesan validasi untuk error
                    if ($selectPicker.next('.invalid-feedback').length === 0) {
                        var feedbackElement = $(
                            '<div class="invalid-feedback">Please select an item in the list.</div>'
                        );
                        $selectPicker.after(feedbackElement);
                    }
                }
            });

            // Menangani perubahan nilai selectpicker
            $selectPicker.on('change', function() {
                if ($selectPicker.val() !== "") {
                    $selectPicker.removeClass('is-invalid');
                    $selectPicker.next('.invalid-feedback')
                        .remove(); // Hapus pesan error saat valid
                }
            });
        });
    });
</script>

<script>
    /**
     * Menampilkan loading animation dengan wave dots.
     */
    function showLoading() {
        Swal.fire({
            title: '',
            allowOutsideClick: false,
            showConfirmButton: false,
            customClass: {
                popup: 'loading-alert', // Class khusus untuk popup loading
            },
            didOpen: () => {
                Swal.hideLoading(); // Menghilangkan spinner bawaan
                Swal.getHtmlContainer().innerHTML = `
                    <div class="dots-container">
                    <span class="dot" style="background-color: #3498db;"></span>
                    <span class="dot" style="background-color: #e74c3c;"></span>
                    <span class="dot" style="background-color: #2ecc71;"></span>
                    <span class="dot" style="background-color: #f1c40f;"></span>
                    </div>
                `;
            },
            background: 'transparent', // Menghilangkan background popup
            backdrop: false, // Menghilangkan backdrop
        });
    }

    /**
     * Menutup loading animation.
     */
    function hideLoading() {
        Swal.close();
    }
</script>

<script>
    // Menambahkan tooltip (untuk ikon informasi di atas)
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
</script>
