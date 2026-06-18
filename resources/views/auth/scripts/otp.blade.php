<script>
$(document).ready(function() {
    // Memaksa input agar hanya bisa diisi angka
    $('#otp_code').on('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    // Menangani aksi Submit Verifikasi
    $('#otpForm').on('submit', function(e) {
        e.preventDefault();
        
        let submitBtn = $('#submitBtn');
        let originalText = submitBtn.html();
        
        submitBtn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Memeriksa...').prop('disabled', true);

        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                // ResponseService mengembalikan status 200 untuk sukses
                if (response.status === 200) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = response.data.redirect;
                    });
                } else {
                    ResponseHandler.handleError(response.message);
                    submitBtn.html(originalText).prop('disabled', false);
                }
            },
            error: function(xhr) {
                ResponseHandler.handleHttpError(xhr);
                submitBtn.html(originalText).prop('disabled', false);
            }
        });
    });

    // Menangani aksi Kirim Ulang OTP
    $('#resendOtpBtn').on('click', function() {
        let btn = $(this);
        let originalText = btn.html();
        
        btn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>').prop('disabled', true);

        $.ajax({
            url: "{{ route('otp.resend') }}",
            method: 'POST',
            success: function(response) {
                // ResponseService mengembalikan status 200 untuk sukses
                if (response.status === 200) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Terkirim',
                        text: response.message,
                        timer: 3000,
                        showConfirmButton: false
                    });
                } else {
                    ResponseHandler.handleError(response.message);
                }
                btn.html(originalText).prop('disabled', false);
            },
            error: function(xhr) {
                ResponseHandler.handleHttpError(xhr);
                btn.html(originalText).prop('disabled', false);
            }
        });
    });
});
</script>
