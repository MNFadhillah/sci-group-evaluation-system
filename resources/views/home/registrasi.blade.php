<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Registrasi · Evolevel</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

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

    <!-- SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root {
            --primary: #4f73dc;
            --primary-dark: #3158c7;
            --soft: rgba(79, 115, 220, .10);
            --text: #182235;
            --muted: #59677a;
            --border: #d9dfeb;
            --danger: #e11d48;
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
            color: var(--text);
            overflow: hidden;
        }

        .register-wrapper {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
            position: relative;
            overflow: hidden;
        }

        /* lingkaran mengambang tipis, senada landing & login */

        .float-circle {
            position: absolute;
            border-radius: 50%;
            background: var(--primary);
            opacity: .10;
            pointer-events: none;
            z-index: 0;
        }

        .float-circle:nth-child(1) {
            width: 300px;
            height: 300px;
            left: -80px;
            top: 8%;
            animation: floatA 9s ease-in-out infinite;
        }

        .float-circle:nth-child(2) {
            width: 170px;
            height: 170px;
            right: 6%;
            top: 10%;
            opacity: .08;
            animation: floatB 11s ease-in-out infinite;
        }

        .float-circle:nth-child(3) {
            width: 130px;
            height: 130px;
            right: 10%;
            bottom: 8%;
            opacity: .13;
            animation: floatA 8s ease-in-out infinite;
        }

        .float-circle:nth-child(4) {
            width: 110px;
            height: 110px;
            left: 6%;
            bottom: 10%;
            opacity: .11;
            animation: floatB 10s ease-in-out infinite;
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

        .register-card {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 920px;
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 14px;
            box-shadow: 0 18px 40px rgba(78, 115, 223, .12);
            padding: 22px 40px;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            margin-bottom: .7rem;
            font-size: .85rem;
            font-weight: 700;
            color: var(--muted);
            text-decoration: none;
        }

        .back-link:hover {
            color: var(--primary);
        }

        h1 {
            font-weight: 800;
            font-size: 1.3rem;
            letter-spacing: -.3px;
            margin-bottom: 4px;
            color: var(--text);
        }

        .lead {
            font-size: .87rem;
            color: var(--muted);
            margin-bottom: .9rem;
        }

        label {
            font-size: .82rem;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .form-control,
        .form-select {
            border-color: var(--border);
            border-radius: 8px;
            font-size: .88rem;
            padding: .48rem .8rem;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 .2rem var(--soft);
        }

        /* ROLE SELECTOR — segmented pill */

        .role-box {
            display: flex;
            gap: .4rem;
            background: #f1f3f9;
            border-radius: 10px;
            padding: .25rem;
        }

        .role-box input[type="radio"] {
            display: none;
        }

        .role-box label {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .4rem;
            margin: 0;
            padding: .42rem .6rem;
            border-radius: 7px;
            font-size: .83rem;
            font-weight: 700;
            color: var(--muted);
            cursor: pointer;
            transition: background .15s ease, color .15s ease, box-shadow .15s ease;
        }

        .role-box input[type="radio"]:checked+label {
            background: #fff;
            color: var(--primary-dark);
            box-shadow: 0 2px 6px rgba(31, 41, 55, .08);
        }

        .input-group .btn-outline-secondary {
            border-color: var(--border);
            color: var(--muted);
            border-radius: 0 8px 8px 0;
        }

        .input-group .btn-outline-secondary:hover {
            background: var(--soft);
            color: var(--primary-dark);
            border-color: var(--border);
        }

        .btn-primary {
            background: var(--primary);
            border: none;
            font-weight: 700;
            padding: .6rem 1rem;
            border-radius: 9px;
            box-shadow: 0 8px 20px rgba(78, 115, 223, .25);
            transition: background .2s ease, transform .2s ease;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }

        .btn-outline {
            border: 1px solid var(--border);
            background: transparent;
            color: var(--text);
            font-weight: 700;
            border-radius: 9px;
            padding: .55rem 1rem;
        }

        .btn-outline:hover {
            background: var(--soft);
            border-color: var(--border);
        }

        .error {
            font-size: .78rem;
            font-weight: 600;
            color: var(--danger);
            margin-top: .3rem;
        }

        .hidden {
            display: none;
        }

        .footer {
            text-align: center;
            margin-top: .8rem;
            font-size: .83rem;
            color: var(--muted);
        }

        .footer a {
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
        }

        .footer a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }
    </style>
</head>

<body>

    <div class="register-wrapper">

        <div class="float-circle"></div>
        <div class="float-circle"></div>
        <div class="float-circle"></div>
        <div class="float-circle"></div>

        <main class="register-card">

            <a href="{{ url('/') }}" class="back-link">
                <i class="bi bi-arrow-left"></i>
                Kembali ke beranda
            </a>

            <h1>Daftar Evolevel</h1>
            <p class="lead">Buat akun baru sebagai murid atau guru</p>

            <form id="regForm" novalidate>

                <!-- ROLE -->
                <div class="mb-2">
                    <label>Daftar sebagai</label>
                    <div class="role-box">
                        <input type="radio" name="role" id="roleMurid" value="murid" checked>
                        <label for="roleMurid">
                            <i class="bi bi-mortarboard"></i>
                            Murid
                        </label>

                        <input type="radio" name="role" id="roleGuru" value="guru">
                        <label for="roleGuru">
                            <i class="bi bi-person-workspace"></i>
                            Guru
                        </label>
                    </div>
                </div>

                <div class="row g-3">

                    <div class="col-md-6">
                        <label>Nama Lengkap</label>
                        <input type="text" id="name" class="form-control" placeholder="Nama lengkap">
                        <div id="nameError" class="error hidden"></div>
                    </div>

                    <div class="col-md-6">
                        <label>Email</label>
                        <input type="email" id="email" class="form-control" placeholder="nama@contoh.com">
                        <div id="emailError" class="error hidden"></div>
                    </div>

                    <div class="col-md-6">
                        <label>Kata Sandi</label>
                        <div class="input-group">
                            <input type="password" id="password" class="form-control"
                                placeholder="Minimal 6 karakter">
                            <button type="button" class="btn btn-outline-secondary" id="togglePass">
                                <i class="bi bi-eye" id="togglePassIcon"></i>
                            </button>
                        </div>
                        <div id="passwordError" class="error hidden"></div>
                    </div>

                    <div class="col-md-6" id="kelasField">
                        <label>Kode Kelas</label>
                        <input type="text" id="kodeKelas" class="form-control" placeholder="Misal: KLS7TOKEN">
                        <div id="kelasError" class="error hidden"></div>
                    </div>

                    <div class="col-md-6">
                        <label>Jenis ID</label>
                        <select id="type_id_other" class="form-select">
                            <option value="">— Pilih jenis ID —</option>
                            <option value="NISN">NISN</option>
                            <option value="NIM">NIM</option>
                            <option value="NIP">NIP</option>
                            <option value="NIDN">NIDN</option>
                            <option value="NUPTK">NUPTK</option>
                            <option value="id_lainnya">ID Lainnya</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label>Nomor ID</label>
                        <input type="text" id="id_other" class="form-control" placeholder="Masukkan jika ada">
                        <div id="idError" class="error hidden"></div>
                    </div>

                </div>

                <div class="d-grid gap-2 mt-3">
                    <button type="submit" class="btn btn-primary">Daftar</button>
                    <button type="button" class="btn btn-outline" id="toLogin">Masuk</button>
                </div>

                <div class="footer">
                    Sudah punya akun? <a href="{{ url('/login') }}">Masuk di sini</a>
                </div>
            </form>
        </main>
    </div>

    <script>
        const roleInputs = document.querySelectorAll('input[name="role"]');
        const kelasField = document.getElementById('kelasField');
        const togglePass = document.getElementById('togglePass');
        const togglePassIcon = document.getElementById('togglePassIcon');
        const password = document.getElementById('password');

        const nameInput = document.getElementById('name');
        const emailInput = document.getElementById('email');
        const kodeKelasInput = document.getElementById('kodeKelas');
        const typeIdOtherSelect = document.getElementById('type_id_other');
        const idOtherInput = document.getElementById('id_other');

        const nameError = document.getElementById('nameError');
        const emailError = document.getElementById('emailError');
        const passwordError = document.getElementById('passwordError');
        const kelasError = document.getElementById('kelasError');
        const idError = document.getElementById('idError');

        function resetErrors() {
            [nameError, emailError, passwordError, kelasError, idError]
                .forEach(e => e.classList.add('hidden'));
        }

        function updateRole() {
            const role = document.querySelector('input[name="role"]:checked').value;
            kelasField.style.display = role === 'murid' ? 'block' : 'none';
        }
        roleInputs.forEach(r => r.addEventListener('change', updateRole));
        updateRole();

        togglePass.onclick = () => {
            password.type = password.type === 'password' ? 'text' : 'password';
            togglePassIcon.className = password.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
        };

        document.getElementById('toLogin').onclick = () => {
            window.location.href = '{{ url('/login') }}';
        };

        document.getElementById('regForm').addEventListener('submit', async e => {
            e.preventDefault();
            resetErrors();

            const name = nameInput.value.trim();
            const email = emailInput.value.trim();
            const pass = password.value;
            const role = document.querySelector('input[name="role"]:checked').value;
            const kodeKelas = kodeKelasInput.value.trim();
            const idOther = idOtherInput.value.trim();

            /* =====================
               VALIDASI NAMA
            ===================== */
            if (!name) {
                nameError.textContent = 'Nama tidak boleh kosong.';
                nameError.classList.remove('hidden');
                return;
            }

            if (name.length < 2) {
                nameError.textContent = 'Nama terlalu pendek (minimal 2 karakter).';
                nameError.classList.remove('hidden');
                return;
            }

            if (!/^[A-Za-zÀ-ÖØ-öø-ÿ\s]+$/.test(name)) {
                nameError.textContent = 'Nama tidak valid (tidak boleh angka atau simbol).';
                nameError.classList.remove('hidden');
                return;
            }

            /* =====================
               VALIDASI EMAIL
            ===================== */
            if (!email) {
                emailError.textContent = 'Email tidak boleh kosong.';
                emailError.classList.remove('hidden');
                return;
            }

            if (/\s/.test(email)) {
                emailError.textContent = 'Email tidak boleh mengandung spasi.';
                emailError.classList.remove('hidden');
                return;
            }

            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                emailError.textContent = 'Format email tidak valid.';
                emailError.classList.remove('hidden');
                return;
            }

            /* =====================
               VALIDASI PASSWORD
            ===================== */
            if (pass.length < 6) {
                passwordError.textContent = 'Password minimal 6 karakter.';
                passwordError.classList.remove('hidden');
                return;
            }

            if (/^\d+$/.test(pass)) {
                passwordError.textContent = 'Password terlalu lemah (tidak boleh hanya angka).';
                passwordError.classList.remove('hidden');
                return;
            }

            /* =====================
               VALIDASI KODE KELAS
            ===================== */
            if (role === 'murid') {
                if (!kodeKelas) {
                    kelasError.textContent = 'Kode kelas wajib diisi.';
                    kelasError.classList.remove('hidden');
                    return;
                }
                if (kodeKelas.length < 4) {
                    kelasError.textContent = 'Kode kelas terlalu pendek.';
                    kelasError.classList.remove('hidden');
                    return;
                }
            }

            /* =====================
               VALIDASI ID
            ===================== */
            if (idOther) {
                if (!/^\d+$/.test(idOther)) {
                    idError.textContent = 'Nomor ID harus berupa angka.';
                    idError.classList.remove('hidden');
                    return;
                }

                if (idOther.length < 6) {
                    idError.textContent = 'Nomor ID terlalu pendek.';
                    idError.classList.remove('hidden');
                    return;
                }
            }

            const payload = {
                name: name,
                email: email,
                password: pass,
                role: role,
                kodeKelas: kodeKelas || null,
                type_id_other: typeIdOtherSelect.value || null,
                id_other: idOther || null
            };

            try {
                const res = await fetch("{{ route('register.submit') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(payload)
                });

                const json = await res.json();

                if (!res.ok) {

                    // 🔴 VALIDASI LARAVEL (422)
                    if (res.status === 422) {

                        // error email unique
                        if (json.errors && json.errors.email) {
                            emailError.textContent = json.errors.email[0];
                            emailError.classList.remove('hidden');
                            return;
                        }

                        // error lain (fallback)
                        Swal.fire({
                            icon: 'error',
                            title: 'Validasi gagal',
                            text: json.message || 'Data tidak valid'
                        });
                        return;
                    }

                    throw new Error(json.message || 'Registrasi gagal');
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Akun berhasil dibuat',
                    confirmButtonColor: '#4f73dc'
                }).then(() => {
                    if (json.redirect) {
                        window.location.href = json.redirect;
                    } else {
                        window.location.href = '/login';
                    }
                });

            } catch (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: err.message,
                    confirmButtonColor: '#e11d48'
                });
            }
        });
    </script>

</body>

</html>