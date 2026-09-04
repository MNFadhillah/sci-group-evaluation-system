<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Groupizone – Sistem Evaluasi SCI</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
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
            width: 100%;
            height: 100%;
            margin: 0;
        }

        body {
            font-family:
                'Plus Jakarta Sans',
                system-ui,
                -apple-system,
                BlinkMacSystemFont,
                sans-serif;

            background: #f8f9fc;
            color: var(--text-dark);
            overflow: hidden;
        }

        /* NAVBAR */

        .navbar {
            height: 72px;

            padding-left: 4.5vw;
            padding-right: 4.5vw;

            background: #f8f9fc;

            border-bottom: 1px solid var(--border);

            box-shadow:
                0 1px 0 rgba(255, 255, 255, .8),
                0 3px 8px rgba(31, 41, 55, .045);

            position: relative;
            z-index: 10;
        }

        .navbar-brand {
            color: var(--primary) !important;

            font-size: 1.2rem;
            font-weight: 800;

            text-decoration: none;
        }

        .login-link {
            color: #26354a;

            font-size: .95rem;
            font-weight: 700;

            text-decoration: none;

            transition: color .2s ease;
        }

        .login-link:hover {
            color: var(--primary);
        }

        /* MAIN */

        .main-section {
            position: relative;

            width: 100%;
            min-height: calc(100vh - 112px);

            display: flex;

            overflow: hidden;
        }

        /* SIMPLE FLOATING CIRCLES (shared) */

        .float-circle {
            position: absolute;

            border-radius: 50%;

            background: var(--primary);

            opacity: .12;

            pointer-events: none;

            z-index: 0;
        }

        @keyframes floatUpDown {
            0%,
            100% {
                transform: translate(0, 0);
            }

            50% {
                transform: translate(20px, -55px);
            }
        }

        @keyframes floatDownUp {
            0%,
            100% {
                transform: translate(0, 0);
            }

            50% {
                transform: translate(-18px, 45px);
            }
        }

        /* HERO SIDE */

        .hero-side {
            position: relative;

            width: 58%;
            min-height: calc(100vh - 112px);

            display: flex;
            align-items: center;

            padding: 60px 5vw 60px 7vw;

            overflow: hidden;

            z-index: 1;
        }

        .hero-side .float-circle:nth-child(1) {
            width: 280px;
            height: 280px;

            left: -60px;
            top: 8%;

            animation: floatUpDown 9s ease-in-out infinite;
        }

        .hero-side .float-circle:nth-child(2) {
            width: 150px;
            height: 150px;

            right: 6%;
            top: 8%;

            opacity: .09;

            animation: floatDownUp 11s ease-in-out infinite;
        }

        .hero-side .float-circle:nth-child(3) {
            width: 110px;
            height: 110px;

            right: 4%;
            bottom: 6%;

            opacity: .15;

            animation: floatUpDown 8s ease-in-out infinite;
        }

        /* HERO */

        .hero-content {
            position: relative;
            z-index: 2;

            width: 100%;
            max-width: 850px;
        }

        .badge-platform {
            display: inline-flex;
            align-items: center;
            gap: .45rem;

            margin-bottom: 1.2rem;

            padding: .42rem .85rem;

            border-radius: 999px;

            background: var(--primary-soft);
            color: var(--primary-dark);

            font-size: .82rem;
            font-weight: 700;
        }

        .badge-dot {
            width: 6px;
            height: 6px;

            border-radius: 50%;

            background: var(--primary);
        }

        .hero h1 {
            max-width: 760px;

            margin: 0 0 1.15rem;

            font-size: clamp(2.8rem, 3.7vw, 3.9rem);

            line-height: 1.08;

            letter-spacing: -1.8px;

            font-weight: 900;

            color: #172033;
        }

        .hero h1 span {
            color: var(--primary);
            font-weight: 900;
        }

        .main-description {
            max-width: 700px;

            margin: 0 0 1.8rem;

            font-size: 1rem;
            line-height: 1.7;

            color: var(--text-muted);
        }

        .main-description strong {
            color: #26354a;
            font-weight: 700;
        }

        /* BUTTON */

        .btn-cta {
            display: inline-flex;
            align-items: center;

            gap: .35rem;

            padding: .8rem 1.9rem;

            border: none;
            border-radius: 9px;

            background: var(--primary);
            color: #fff;

            font-size: .95rem;
            font-weight: 700;

            box-shadow:
                0 8px 20px rgba(78, 115, 223, .20);

            transition:
                background .2s ease,
                transform .2s ease,
                box-shadow .2s ease;
        }

        .btn-cta:hover {
            background: var(--primary-dark);
            color: #fff;

            transform: translateY(-2px);

            box-shadow:
                0 10px 22px rgba(78, 115, 223, .25);
        }

        /* RIGHT SIDE */

        .info-side {
            position: relative;
            z-index: 2;

            width: 42%;
            min-height: calc(100vh - 112px);

            display: flex;
            align-items: center;

            padding: 60px 7vw 60px 5vw;

            background: #fff;

            border-left: 1px solid var(--border);

            box-shadow:
                -5px 0 15px rgba(31, 41, 55, .025);

            overflow: hidden;
        }

        .info-content {
            position: relative;
            z-index: 2;

            width: 100%;
            max-width: 500px;
        }

        .section-label {
            margin-bottom: .65rem;

            color: #4268d3;

            font-size: .78rem;
            font-weight: 800;

            text-transform: uppercase;
            letter-spacing: .8px;
        }

        .info-title {
            margin: 0 0 .7rem;

            color: #172033;

            font-size: 2rem;
            line-height: 1.15;

            font-weight: 800;

            letter-spacing: -.5px;
        }

        .info-description {
            max-width: 430px;

            margin: 0 0 1.8rem;

            color: var(--text-muted);

            font-size: .9rem;
            line-height: 1.65;
        }

        /* FEATURES */

        .feature-list {
            display: flex;
            flex-direction: column;
        }

        .feature-item {
            display: flex;
            align-items: flex-start;

            gap: 1rem;

            padding: 1.1rem 0;

            border-top: 1px solid #e3e7ef;
        }

        .feature-item:last-child {
            border-bottom: 1px solid #e3e7ef;
        }

        .feature-icon {
            flex-shrink: 0;

            width: 40px;
            height: 40px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 9px;

            background: rgba(79, 115, 220, .11);
            color: #4770dc;

            font-size: .95rem;
        }

        .feature-text {
            padding-top: .05rem;
        }

        .feature-title {
            margin: 0 0 .3rem;

            color: #1c293d;

            font-size: .9rem;
            line-height: 1.3;

            font-weight: 800;
        }

        .feature-description {
            max-width: 380px;

            margin: 0;

            color: #667386;

            font-size: .79rem;
            line-height: 1.55;
        }

        /* FOOTER */

        footer {
            height: 40px;

            display: flex;
            align-items: center;
            justify-content: center;

            background: #f8f9fc;

            border-top: 1px solid var(--border);

            box-shadow:
                0 -1px 0 rgba(255, 255, 255, .8),
                0 -3px 8px rgba(31, 41, 55, .045);

            color: #727d8c;

            font-size: .78rem;

            position: relative;
            z-index: 10;
        }

        /* TABLET */

        @media (max-width: 992px) {

            .hero-side {
                width: 55%;

                padding-left: 4vw;
                padding-right: 4vw;
            }

            .info-side {
                width: 45%;

                padding-left: 3vw;
                padding-right: 4vw;
            }

            .hero h1 {
                font-size: 3rem;
            }

            .info-title {
                font-size: 1.8rem;
            }

            .main-description {
                font-size: .95rem;
            }
        }

        /* MOBILE */

        @media (max-width: 768px) {

            body {
                overflow-x: hidden;
                overflow-y: auto;
            }

            .navbar {
                height: 64px;

                padding-left: 1.2rem;
                padding-right: 1.2rem;
            }

            .navbar-brand {
                font-size: 1.1rem;
            }

            .login-link {
                font-size: .9rem;
            }

            .main-section {
                min-height: auto;

                flex-direction: column;

                overflow: visible;
            }

            .hero-side {
                width: 100%;
                min-height: calc(100vh - 64px);

                padding: 65px 1.3rem;

                text-align: center;
            }

            .hero-content {
                max-width: 600px;
                margin: auto;
            }

            .hero h1 {
                font-size: 2.35rem;

                line-height: 1.12;

                letter-spacing: -.8px;
            }

            .main-description {
                max-width: 600px;

                margin-left: auto;
                margin-right: auto;

                font-size: .92rem;
                line-height: 1.6;
            }

            .btn-cta {
                padding: .75rem 1.6rem;
                font-size: .9rem;
            }

            .info-side {
                width: 100%;
                min-height: auto;

                padding: 65px 1.3rem 75px;

                border-left: none;
                border-top: 1px solid var(--border);

                box-shadow:
                    0 -4px 12px rgba(31, 41, 55, .035);
            }

            .info-content {
                max-width: 600px;
                margin: auto;

                text-align: center;
            }

            .info-title {
                font-size: 1.9rem;
            }

            .info-description {
                margin-left: auto;
                margin-right: auto;
            }

            .feature-list {
                text-align: left;
            }

            .feature-description {
                max-width: none;
            }

            footer {
                height: 40px;
                font-size: .75rem;
            }
        }

        /* REDUCE MOTION */

        @media (prefers-reduced-motion: reduce) {

            .float-circle {
                animation: none;
            }
        }
    </style>
</head>

<body>

    <!-- NAVBAR -->

    <nav class="navbar">

        <div class="container-fluid">

            <a class="navbar-brand" href="#">
                <i class="bi bi-layers-fill me-1"></i>
                Groupizone
            </a>

            <div class="ms-auto">
                <a href="/login" class="login-link">
                    Masuk
                </a>
            </div>

        </div>

    </nav>


    <!-- MAIN -->

    <main class="main-section">

        <!-- LEFT -->

        <section class="hero-side">

            <div class="float-circle"></div>
            <div class="float-circle"></div>
            <div class="float-circle"></div>

            <div class="hero-content">

                <div class="badge-platform">

                    <span class="badge-dot"></span>

                    Sistem Evaluasi Student Contribution Index

                </div>


                <h1 style="
                    font-weight: 900;
                    letter-spacing: -1.8px;
                    line-height: 1.08;
                ">

                    Evaluasi Belajar yang<br>

                    <span style="
                        color: #4f73dc;
                        font-weight: 900;
                    ">
                        Adil dan Transparan
                    </span>

                </h1>


                <p class="main-description">

                    Evaluasi kontribusi siswa melalui
                    <strong>Student Contribution Index (SCI)</strong>
                    dengan pendekatan gamifikasi untuk menciptakan
                    proses evaluasi yang lebih menarik dan terukur.

                </p>


                <a href="/login" class="btn btn-cta">

                    Mulai Sekarang

                    <i class="bi bi-arrow-right"></i>

                </a>

            </div>

        </section>


        <!-- RIGHT -->

        <section class="info-side">

            <div class="info-content">

                <div class="section-label">
                    Tentang Groupizone
                </div>


                <h2 class="info-title">
                    Tiga Bagian Utama
                </h2>


                <p class="info-description">

                    Groupizone menggabungkan evaluasi yang transparan,
                    pengukuran kontribusi siswa melalui SCI,
                    dan gamifikasi dalam satu sistem evaluasi.

                </p>


                <div class="feature-list">

                    <!-- EVALUASI -->

                    <div class="feature-item">

                        <div class="feature-icon">
                            <i class="bi bi-clipboard-check"></i>
                        </div>

                        <div class="feature-text">

                            <h3 class="feature-title">
                                Evaluasi Transparan
                            </h3>

                            <p class="feature-description">
                                Penilaian dilakukan secara terstruktur
                                dan dapat dipahami oleh siswa.
                            </p>

                        </div>

                    </div>


                    <!-- SCI -->

                    <div class="feature-item">

                        <div class="feature-icon">
                            <i class="bi bi-bar-chart"></i>
                        </div>

                        <div class="feature-text">

                            <h3 class="feature-title">
                                Student Contribution Index
                            </h3>

                            <p class="feature-description">
                                Mengukur kontribusi siswa dalam
                                proses evaluasi pembelajaran.
                            </p>

                        </div>

                    </div>


                    <!-- GAMIFIKASI -->

                    <div class="feature-item">

                        <div class="feature-icon">
                            <i class="bi bi-trophy"></i>
                        </div>

                        <div class="feature-text">

                            <h3 class="feature-title">
                                Gamifikasi
                            </h3>

                            <p class="feature-description">
                                Membuat proses evaluasi lebih menarik
                                melalui elemen permainan.
                            </p>

                        </div>

                    </div>

                </div>

            </div>

        </section>

    </main>


    <!-- FOOTER -->

    <footer>
        &copy; 2026 Groupizone
    </footer>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>