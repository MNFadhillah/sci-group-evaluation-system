<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Login · Groupizone</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet">

    <style>
        :root {
            --primary: #4f73dc;
            --primary-dark: #3158c7;
            --primary-soft: rgba(79, 115, 220, .10);

            --text-dark: #182235;
            --text-muted: #59677a;
            --border: #d9dfeb;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            height: 100%;
            margin: 0;
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
            background: #f8f9fc;
            color: var(--text-dark);
        }

        .container-fluid,
        .row {
            margin: 0 !important;
            padding: 0 !important;
        }

        /* LEFT PANEL — senada dengan hero-side landing */

        .left-panel {
            min-height: 100vh;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem;
            position: relative;
            overflow: hidden;

            background: linear-gradient(150deg, #5f7ff2 0%, var(--primary) 50%, var(--primary-dark) 100%);
        }

        .left-panel::before {
            content: "";
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 65% 25%, rgba(255, 255, 255, .18), transparent 55%),
                radial-gradient(circle at 20% 80%, rgba(255, 255, 255, .12), transparent 60%);
            z-index: 0;
        }

        /* lingkaran mengambang tipis, senada landing */

        .float-circle {
            position: absolute;
            border-radius: 50%;
            background: #fff;
            opacity: .08;
            pointer-events: none;
            z-index: 0;
        }

        .float-circle:nth-child(1) {
            width: 260px;
            height: 260px;
            left: -70px;
            top: 8%;
            animation: floatA 9s ease-in-out infinite;
        }

        .float-circle:nth-child(2) {
            width: 140px;
            height: 140px;
            right: 8%;
            bottom: 12%;
            opacity: .12;
            animation: floatB 11s ease-in-out infinite;
        }

        @keyframes floatA {
            0%, 100% { transform: translate(0, 0); }
            50%      { transform: translate(20px, -40px); }
        }

        @keyframes floatB {
            0%, 100% { transform: translate(0, 0); }
            50%      { transform: translate(-18px, 30px); }
        }

        @media (prefers-reduced-motion: reduce) {
            .float-circle { animation: none; }
        }

        .left-content {
            position: relative;
            z-index: 1;
            max-width: 440px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: .5rem;

            font-weight: 800;
            font-size: 1.6rem;
            margin-bottom: 1.6rem;
        }

        .left-content .lead {
            font-size: 1.05rem;
            line-height: 1.65;
            color: rgba(255, 255, 255, .92);
            margin-bottom: 2rem;
        }

        .left-feature {
            display: flex;
            align-items: flex-start;
            gap: .85rem;
            margin-bottom: 1.3rem;
        }

        .left-feature:last-child {
            margin-bottom: 0;
        }

        .left-feature-icon {
            flex-shrink: 0;
            width: 38px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 9px;
            background: rgba(255, 255, 255, .16);
            font-size: .95rem;
        }

        .left-feature-text h6 {
            margin: 0 0 .15rem;
            font-weight: 700;
            font-size: .92rem;
        }

        .left-feature-text p {
            margin: 0;
            font-size: .82rem;
            line-height: 1.5;
            color: rgba(255, 255, 255, .82);
        }

        /* RIGHT PANEL */

        .right-panel {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .card-login {
            width: 100%;
            max-width: 420px;
            border-radius: 14px;
            border: 1px solid var(--border);
            box-shadow: 0 18px 40px rgba(78, 115, 223, .12);
        }

        .card-login .card-body {
            padding: 2.2rem;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            margin-bottom: 1.4rem;
            font-size: .85rem;
            font-weight: 700;
            color: var(--text-muted);
        }

        .back-link:hover {
            color: var(--primary);
            text-decoration: none;
        }

        .card-login h4 {
            font-weight: 800;
            letter-spacing: -.3px;
        }

        .card-login .text-muted {
            color: var(--text-muted) !important;
        }

        .form-label {
            font-weight: 700;
            font-size: .85rem;
            color: var(--text-dark);
        }

        .form-control {
            border-color: var(--border);
            padding: .6rem .85rem;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 .2rem var(--primary-soft);
        }

        .input-group .btn-outline-secondary {
            border-color: var(--border);
            color: var(--text-muted);
        }

        .input-group .btn-outline-secondary:hover {
            background: var(--primary-soft);
            color: var(--primary-dark);
            border-color: var(--border);
        }

        /* BUTTON */

        .btn-primary {
            background: var(--primary);
            border: none;
            font-weight: 700;
            padding: .75rem 1rem;
            border-radius: 9px;
            box-shadow: 0 8px 20px rgba(78, 115, 223, .25);
            transition: background .2s ease, transform .2s ease;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }

        /* LINKS */

        a {
            color: var(--primary);
            font-weight: 600;
            text-decoration: none;
        }

        a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        .forgot-link {
            font-size: .82rem;
        }

        /* MOBILE */

        @media (max-width: 767.98px) {
            .left-panel {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row g-0">

            <!-- LEFT -->
            <div class="col-md-6 left-panel d-none d-md-flex">

                <div class="float-circle"></div>
                <div class="float-circle"></div>

                <div class="left-content">
                    <div class="brand">
                        <i class="bi bi-layers-fill"></i>
                        Groupizone
                    </div>

                    <p class="lead">
                        Evaluasi kontribusi siswa yang adil dan transparan
                        melalui <strong>Student Contribution Index (SCI)</strong>
                        dengan pendekatan gamifikasi.
                    </p>

                    <div class="left-feature">
                        <div class="left-feature-icon">
                            <i class="bi bi-clipboard-check"></i>
                        </div>
                        <div class="left-feature-text">
                            <h6>Evaluasi Transparan</h6>
                            <p>Penilaian terstruktur yang mudah dipahami siswa.</p>
                        </div>
                    </div>

                    <div class="left-feature">
                        <div class="left-feature-icon">
                            <i class="bi bi-bar-chart"></i>
                        </div>
                        <div class="left-feature-text">
                            <h6>Student Contribution Index</h6>
                            <p>Mengukur kontribusi siswa secara terukur.</p>
                        </div>
                    </div>

                    <div class="left-feature">
                        <div class="left-feature-icon">
                            <i class="bi bi-trophy"></i>
                        </div>
                        <div class="left-feature-text">
                            <h6>Gamifikasi</h6>
                            <p>Proses evaluasi yang lebih menarik dan memotivasi.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT -->
            <div class="col-md-6 right-panel">
                <div class="card card-login">
                    <div class="card-body">

                        <a href="{{ url('/') }}" class="back-link">
                            <i class="bi bi-arrow-left"></i>
                            Kembali ke beranda
                        </a>

                        <h4 class="mb-1">Masuk ke Groupizone</h4>
                        <p class="text-muted mb-4 small">
                            Gunakan akun Anda untuk melanjutkan
                        </p>

                        <form id="loginForm" action="{{ route('login.process') }}" method="POST" novalidate>
                            @csrf

                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" placeholder="nama@contoh.com"
                                    required>
                                <div class="invalid-feedback">
                                    Masukkan alamat email yang valid.
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <label class="form-label mb-0">Kata Sandi</label>
                                    <a href="{{ url('/forgot-password') }}" class="forgot-link">Lupa kata sandi?</a>
                                </div>
                                <div class="input-group mt-1">
                                    <input type="password" name="password" class="form-control" id="password"
                                        placeholder="Kata sandi" required>
                                    <button class="btn btn-outline-secondary" type="button" id="togglePwd">
                                        <i class="bi bi-eye" id="eyeIcon"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback">
                                    Kata sandi tidak boleh kosong.
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                                    <label class="form-check-label small" for="remember">Ingat saya</label>
                                </div>
                            </div>

                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-primary">
                                    Masuk
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="card-footer text-center small text-muted bg-transparent border-0 pb-4">
                        Belum punya akun?
                        <a href="{{ url('/register') }}">Daftar sekarang</a>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('loginForm').addEventListener('submit', function (event) {
            event.preventDefault();

            const email = this.email.value.trim();
            const rawPassword = this.password.value;
            const password = rawPassword.trim();
            const minPasswordLength = 6;

            if (!email) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Email belum diisi',
                    text: 'Silakan masukkan alamat email Anda.',
                    confirmButtonColor: '#4f73dc'
                });
                return;
            }

            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(email)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Email tidak valid',
                    text: 'Gunakan format email yang benar.',
                    confirmButtonColor: '#4f73dc'
                });
                return;
            }

            if (!password) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Kata Sandi Kosong',
                    text: 'Kata sandi tidak boleh hanya berisi spasi.',
                    confirmButtonColor: '#4f73dc'
                });
                return;
            }

            if (/\s/.test(rawPassword)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Kata Sandi Tidak Valid',
                    text: 'Kata sandi tidak boleh mengandung spasi.',
                    confirmButtonColor: '#4f73dc'
                });
                return;
            }

            if (password.length < minPasswordLength) {
                Swal.fire({
                    icon: 'error',
                    title: 'Kata Sandi Terlalu Pendek',
                    text: `Kata sandi minimal ${minPasswordLength} karakter.`,
                    confirmButtonColor: '#4f73dc'
                });
                return;
            }

            Swal.fire({
                title: 'Memeriksa akun...',
                text: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            this.submit();
        });

        const togglePwd = document.getElementById('togglePwd');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');

        togglePwd.addEventListener('click', () => {
            const isHidden = passwordInput.type === 'password';
            passwordInput.type = isHidden ? 'text' : 'password';
            eyeIcon.className = isHidden ? 'bi bi-eye-slash' : 'bi bi-eye';
        });
    </script>
</body>

</html>