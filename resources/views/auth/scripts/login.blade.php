<script>
    document.addEventListener('DOMContentLoaded', function() {
        const loginForm = document.getElementById('loginForm');

        if (!loginForm) return;

        loginForm.addEventListener('submit', function(event) {
            event.preventDefault();

            const login = document.getElementById('login').value;
            const password = document.getElementById('password').value;
            const remember = document.getElementById('remember').checked;
            const submitBtn = document.getElementById('submitBtn');

            // Aktifkan loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML =
                `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Memproses...`;

            fetch('{{ route('login') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                            .getAttribute('content')
                    },
                    body: JSON.stringify({
                        login,
                        password,
                        remember
                    })
                })
                .then(response => response.json().then(data => ({
                    ok: response.ok,
                    status: response.status,
                    body: data
                })))
                .then(({
                    ok,
                    status,
                    body
                }) => {
                    const throttleTimerElement = document.getElementById('throttle-timer');
                    const timerElement = document.getElementById('timer');

                    // Reset tombol
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Masuk';

                    if (status === 422) {
                        const errors = body.data || {};
                        let messages = Object.values(errors).flat().join('\n');
                        Swal.fire({
                            title: 'Validasi Gagal!',
                            text: messages || body.message,
                            icon: 'warning',
                            confirmButtonText: 'Ok'
                        });
                    } else if (status === 403 || status === 401) {
                        Swal.fire({
                            title: 'Login Gagal!',
                            text: body.message,
                            icon: 'error',
                            confirmButtonText: 'Ok'
                        });
                    } else if (status === 429) {
                        if (throttleTimerElement && timerElement) {
                            throttleTimerElement.classList.remove('d-none');

                            let remainingSeconds = parseInt(body.message.match(/\d+/)?.[0] || '0',
                                10);

                            const timerInterval = setInterval(() => {
                                if (remainingSeconds <= 0) {
                                    clearInterval(timerInterval);
                                    throttleTimerElement.classList.add('d-none');
                                } else {
                                    timerElement.textContent = remainingSeconds;
                                    remainingSeconds--;
                                }
                            }, 1000);
                        }

                        Swal.fire({
                            title: 'Terlalu Banyak Percobaan!',
                            text: body.message,
                            icon: 'error',
                            confirmButtonText: 'Ok'
                        });
                    } else if (status === 200) {
                        if (body.data?.redirect) {
                            window.location.href = body.data.redirect;
                        } else {
                            Swal.fire({
                                title: 'Berhasil!',
                                text: body.message,
                                icon: 'success',
                                confirmButtonText: 'Ok'
                            });
                        }
                    } else {
                        Swal.fire({
                            title: 'Terjadi Kesalahan!',
                            text: body.message || 'Terjadi kesalahan yang tidak diketahui.',
                            icon: 'error',
                            confirmButtonText: 'Ok'
                        });
                    }
                })
                .catch(error => {
                    console.error('Unexpected error:', error);
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Masuk';
                    Swal.fire({
                        title: 'Kesalahan!',
                        text: 'Terjadi kesalahan jaringan atau server.',
                        icon: 'error',
                        confirmButtonText: 'Ok'
                    });
                });
        });

    });
</script>
