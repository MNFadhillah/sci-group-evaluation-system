<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Registrasi · Evolevel</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root {
            --primary: #4e73df;
            --primary-dark: #224abe;
            --soft: rgba(78, 115, 223, .12);
            --text: #1f2937;
            --muted: #6b7280;
            --danger: #e11d48;
        }

        html,
        body {
            height: 100%;
            margin: 0;
            font-family: 'Nunito', system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(180deg, #f8f9fc, #eef2ff);
            overflow-x: hidden;
        }

        .register-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .register-card {
            width: 100%;
            max-width: 460px;
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 18px 40px rgba(78, 115, 223, .15);
            padding: 28px;
        }

        h1 {
            font-weight: 800;
            font-size: 1.35rem;
            margin-bottom: 6px;
            color: var(--text);
        }

        .lead {
            font-size: .9rem;
            color: var(--muted);
            margin-bottom: 1.2rem;
        }

        label {
            font-size: .85rem;
            font-weight: 600;
            margin-bottom: 6px;
        }

        .form-control,
        .form-select {
            border-radius: .6rem;
            font-size: .9rem;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 .2rem var(--soft);
        }

        .role-box {
            background: #f8f9fc;
            border-radius: .6rem;
            padding: .6rem .8rem;
            display: flex;
            gap: 1rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border: none;
            font-weight: 700;
            box-shadow: 0 10px 24px rgba(78, 115, 223, .35);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
        }

        .btn-outline {
            border: 1px solid #d1d5db;
            background: transparent;
            font-weight: 600;
        }

        .toggle-pass {
            background: transparent;
            border: none;
            font-size: .8rem;
            color: var(--muted);
            padding-left: .5rem;
        }

        .error {
            font-size: .85rem;
            color: var(--danger);
            margin-top: .5rem;
        }

        .hidden {
            display: none;
        }

        .footer {
            text-align: center;
            margin-top: 1rem;
            font-size: .85rem;
            color: var(--muted);
        }

        .footer a {
            font-weight: 600;
            color: var(--primary);
            text-decoration: none;
        }

        .footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>

    <div class="register-wrapper">
        <main class="register-card">

            <h1>Daftar Evolevel</h1>
            <p class="lead">Buat akun baru sebagai murid atau guru</p>

            <form id="regForm" novalidate>

                <!-- ROLE -->
                <div class="mb-3">
                    <label>Daftar sebagai</label>
                    <div class="role-box">
                        <label class="d-flex align-items-center gap-2 mb-0">
                            <input type="radio" name="role" value="murid" checked>
                            Murid
                        </label>
                        <label class="d-flex align-items-center gap-2 mb-0">
                            <input type="radio" name="role" value="guru">
                            Guru
                        </label>
                    </div>
                </div>

                <div class="mb-3">
                    <label>Nama Lengkap</label>
                    <input type="text" id="name" class="form-control" placeholder="Nama lengkap">
                    <div id="nameError" class="error hidden"></div>
                </div>


                <div class="mb-3">
                    <label>Email</label>
                    <input type="email" id="email" class="form-control" placeholder="nama@contoh.com">
                    <div id="emailError" class="error hidden"></div>
                </div>


                <div class="mb-3">
                    <label>Kata Sandi</label>
                    <div class="input-group">
                        <input type="password" id="password" class="form-control" placeholder="Minimal 6 karakter">
                        <button type="button" class="toggle-pass" id="togglePass">Tampilkan</button>
                    </div>
                    <div id="passwordError" class="error hidden"></div>
                </div>


                <div class="mb-3" id="kelasField">
                    <label>Kode Kelas (opsional)</label>
                    <input type="text" id="kodeKelas" class="form-control" placeholder="Misal: EVO-1234">
                    <div id="kelasError" class="error hidden"></div>
                </div>


                <!-- ID -->
                <div class="mb-3">
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

                <div class="mb-3">
                    <label>Nomor ID</label>
                    <input type="text" id="id_other" class="form-control" placeholder="Masukkan jika ada">
                    <div id="idError" class="error hidden"></div>
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
        const password = document.getElementById('password');
        const errMsg = document.getElementById('errMsg');

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
            togglePass.textContent = password.type === 'password' ? 'Tampilkan' : 'Sembunyikan';
        };

        document.getElementById('toLogin').onclick = () => {
            window.location.href = '{{ url("/login") }}';
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
            if (role === 'murid' && kodeKelas) {
                if (!/^EVO-\d{4}$/.test(kodeKelas)) {
                    kelasError.textContent = 'Kode kelas tidak valid. Contoh: EVO-1234';
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
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
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
                    confirmButtonColor: '#4e73df'
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