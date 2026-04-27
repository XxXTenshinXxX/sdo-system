<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schools Division Office - Remittance & Leave Management System</title>
    <meta name="description" content="Official management system of the Schools Division Office. Manage PhilHealth remittances and employee leave monitoring in one secure platform.">
    <link rel="icon" type="image/png" href="assets/images/SDO-Logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --blue: #2196f3;
            --blue-dark: #1565c0;
            --blue-light: #e3f2fd;
            --blue-mid: #1976d2;
            --white: #ffffff;
            --gray-50: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-400: #94a3b8;
            --gray-600: #475569;
            --gray-800: #1e293b;
            --gray-900: #0f172a;
            --radius-sm: 12px;
            --radius: 20px;
            --radius-lg: 28px;
            --shadow-sm: 0 2px 8px rgba(33,150,243,0.08);
            --shadow: 0 8px 30px rgba(33,150,243,0.14);
            --shadow-lg: 0 20px 60px rgba(33,150,243,0.2);
        }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'Inter', 'Segoe UI', sans-serif;
            background: var(--white);
            color: var(--gray-800);
            overflow-x: hidden;
        }

        /* ── NAV ── */
        nav {
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 40px;
            height: 68px;
            background: rgba(255,255,255,0.88);
            backdrop-filter: blur(18px);
            border-bottom: 1px solid rgba(33,150,243,0.1);
            transition: box-shadow 0.3s;
        }
        nav.scrolled { box-shadow: 0 4px 20px rgba(33,150,243,0.12); }

        .nav-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }
        .nav-brand img { width: 38px; height: 38px; object-fit: contain; }
        .nav-brand-text { font-size: 15px; font-weight: 700; color: var(--gray-900); line-height: 1.2; }
        .nav-brand-text small { display: block; font-size: 11px; font-weight: 500; color: var(--gray-400); }

        .nav-login {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 22px;
            background: var(--blue);
            color: #fff;
            border-radius: 999px;
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 600;
            transition: background 0.2s, transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 4px 14px rgba(33,150,243,0.3);
        }
        .nav-login:hover {
            background: var(--blue-mid);
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(33,150,243,0.4);
        }

        /* ── HERO ── */
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 120px 24px 80px;
            position: relative;
            overflow: hidden;
            background: linear-gradient(160deg, #e3f2fd 0%, #ffffff 50%, #e8f4fd 100%);
        }

        .hero-blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(70px);
            opacity: 0.35;
            pointer-events: none;
        }
        .hero-blob-1 { width: 500px; height: 500px; background: #90caf9; top: -120px; right: -100px; animation: float 8s ease-in-out infinite; }
        .hero-blob-2 { width: 380px; height: 380px; background: #64b5f6; bottom: -80px; left: -80px; animation: float 10s ease-in-out infinite reverse; }
        .hero-blob-3 { width: 250px; height: 250px; background: #bbdefb; top: 40%; left: 10%; animation: float 7s ease-in-out infinite 2s; }

        @keyframes float {
            0%, 100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-24px) scale(1.04); }
        }

        .hero-inner { position: relative; z-index: 2; max-width: 780px; }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 7px 18px;
            background: rgba(33,150,243,0.1);
            border: 1px solid rgba(33,150,243,0.25);
            border-radius: 999px;
            font-size: 12.5px;
            font-weight: 600;
            color: var(--blue-mid);
            margin-bottom: 28px;
            animation: fadeUp 0.6s ease both;
        }
        .hero-badge i { font-size: 11px; }

        .hero-logo {
            width: 100px;
            height: 100px;
            object-fit: contain;
            margin: 0 auto 24px;
            display: block;
            filter: drop-shadow(0 8px 24px rgba(33,150,243,0.3));
            animation: fadeUp 0.7s ease 0.1s both;
        }

        .hero h1 {
            font-size: clamp(32px, 5vw, 58px);
            font-weight: 900;
            line-height: 1.13;
            letter-spacing: -1.5px;
            color: var(--gray-900);
            margin-bottom: 20px;
            animation: fadeUp 0.7s ease 0.2s both;
        }
        .hero h1 span {
            background: linear-gradient(135deg, var(--blue) 0%, #1565c0 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-sub {
            font-size: 17px;
            color: var(--gray-600);
            line-height: 1.7;
            max-width: 560px;
            margin: 0 auto 40px;
            animation: fadeUp 0.7s ease 0.3s both;
        }

        .hero-actions {
            display: flex;
            gap: 14px;
            justify-content: center;
            flex-wrap: wrap;
            animation: fadeUp 0.7s ease 0.4s both;
        }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            padding: 14px 30px;
            background: linear-gradient(135deg, var(--blue) 0%, var(--blue-mid) 100%);
            color: #fff;
            border-radius: 999px;
            text-decoration: none;
            font-size: 15px;
            font-weight: 700;
            box-shadow: 0 6px 24px rgba(33,150,243,0.35);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 10px 32px rgba(33,150,243,0.45); }

        .btn-outline {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            padding: 14px 30px;
            background: rgba(255,255,255,0.9);
            color: var(--blue-mid);
            border: 2px solid rgba(33,150,243,0.3);
            border-radius: 999px;
            text-decoration: none;
            font-size: 15px;
            font-weight: 700;
            transition: border-color 0.2s, background 0.2s, transform 0.2s;
        }
        .btn-outline:hover { border-color: var(--blue); background: #fff; transform: translateY(-2px); }

        .hero-stats {
            display: flex;
            justify-content: center;
            gap: 40px;
            margin-top: 60px;
            flex-wrap: wrap;
            animation: fadeUp 0.7s ease 0.5s both;
        }
        .stat { text-align: center; }
        .stat-num { font-size: 28px; font-weight: 800; color: var(--blue); line-height: 1; }
        .stat-label { font-size: 12px; color: var(--gray-400); font-weight: 500; margin-top: 4px; letter-spacing: 0.5px; text-transform: uppercase; }

        /* ── SECTIONS ── */
        section { padding: 96px 24px; }

        .section-label {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--blue);
            margin-bottom: 14px;
        }
        .section-label::before, .section-label::after {
            content: '';
            display: block;
            width: 24px;
            height: 2px;
            background: var(--blue);
            border-radius: 2px;
            opacity: 0.5;
        }

        .section-title {
            font-size: clamp(26px, 3.5vw, 42px);
            font-weight: 800;
            letter-spacing: -0.8px;
            color: var(--gray-900);
            margin-bottom: 14px;
            line-height: 1.18;
        }
        .section-title span {
            background: linear-gradient(135deg, var(--blue), var(--blue-dark));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .section-sub { font-size: 16px; color: var(--gray-600); line-height: 1.7; max-width: 580px; }

        .text-center { text-align: center; }
        .mx-auto { margin-left: auto; margin-right: auto; }

        /* ── SYSTEMS ── */
        .systems { background: var(--gray-50); }

        .systems-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 24px;
            max-width: 900px;
            margin: 56px auto 0;
        }

        .system-card {
            background: #fff;
            border: 1.5px solid var(--gray-200);
            border-radius: var(--radius-lg);
            padding: 36px 32px;
            text-decoration: none;
            color: inherit;
            display: block;
            transition: transform 0.25s, box-shadow 0.25s, border-color 0.25s;
            position: relative;
            overflow: hidden;
        }
        .system-card::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(33,150,243,0.04) 0%, transparent 60%);
            opacity: 0;
            transition: opacity 0.3s;
        }
        .system-card:hover { transform: translateY(-6px); box-shadow: var(--shadow-lg); border-color: rgba(33,150,243,0.4); }
        .system-card:hover::before { opacity: 1; }

        .system-icon {
            width: 64px;
            height: 64px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            margin-bottom: 22px;
            background: linear-gradient(135deg, var(--blue-light), #bbdefb);
            color: var(--blue);
            box-shadow: 0 4px 14px rgba(33,150,243,0.2);
        }

        .system-card h3 { font-size: 20px; font-weight: 800; color: var(--gray-900); margin-bottom: 10px; }
        .system-card p { font-size: 14px; color: var(--gray-600); line-height: 1.65; margin-bottom: 24px; }

        .system-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 600;
            color: var(--blue);
            padding: 5px 12px;
            background: var(--blue-light);
            border-radius: 999px;
        }

        /* ── FEATURES ── */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            max-width: 1100px;
            margin: 56px auto 0;
        }

        .feature-card {
            background: #fff;
            border: 1.5px solid var(--gray-200);
            border-radius: var(--radius);
            padding: 28px 26px;
            transition: transform 0.2s, box-shadow 0.2s, border-color 0.2s;
        }
        .feature-card:hover { transform: translateY(-4px); box-shadow: var(--shadow); border-color: rgba(33,150,243,0.3); }

        .feature-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: var(--blue-light);
            color: var(--blue);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            margin-bottom: 18px;
        }

        .feature-card h4 { font-size: 16px; font-weight: 700; color: var(--gray-900); margin-bottom: 8px; }
        .feature-card p { font-size: 13.5px; color: var(--gray-600); line-height: 1.65; }

        /* ── CTA ── */
        .cta {
            background: linear-gradient(135deg, #1565c0 0%, #1976d2 40%, #2196f3 100%);
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .cta::before {
            content: '';
            position: absolute;
            inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        .cta-inner { position: relative; z-index: 2; max-width: 640px; margin: 0 auto; }
        .cta h2 { font-size: clamp(26px, 3.5vw, 40px); font-weight: 800; color: #fff; margin-bottom: 16px; letter-spacing: -0.5px; }
        .cta p { font-size: 16px; color: rgba(255,255,255,0.82); line-height: 1.7; margin-bottom: 36px; }

        .btn-white {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            padding: 14px 32px;
            background: #fff;
            color: var(--blue-mid);
            border-radius: 999px;
            text-decoration: none;
            font-size: 15px;
            font-weight: 700;
            box-shadow: 0 6px 24px rgba(0,0,0,0.15);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .btn-white:hover { transform: translateY(-2px); box-shadow: 0 10px 32px rgba(0,0,0,0.22); }

        /* ── FOOTER ── */
        footer {
            background: var(--gray-900);
            color: rgba(255,255,255,0.55);
            text-align: center;
            padding: 36px 24px;
            font-size: 13.5px;
        }
        footer strong { color: rgba(255,255,255,0.85); }
        footer .footer-logo {
            width: 40px;
            opacity: 0.7;
            margin: 0 auto 14px;
            display: block;
            filter: grayscale(1) brightness(2);
        }

        /* ── ANIMATIONS ── */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(28px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .reveal {
            opacity: 0;
            transform: translateY(32px);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }
        .reveal.visible { opacity: 1; transform: translateY(0); }

        /* ── RESPONSIVE ── */
        @media (max-width: 640px) {
            nav { padding: 0 20px; }
            .hero-stats { gap: 24px; }
            .hero-actions { flex-direction: column; align-items: center; }
            section { padding: 64px 20px; }
        }
    </style>
</head>
<body>

<!-- NAV -->
<nav id="navbar">
    <a class="nav-brand" href="#">
        <img src="assets/images/SDO-Logo.png" alt="SDO Logo">
        <div class="nav-brand-text">
            Schools Division Office
            <small>Remittance & Leave Management System</small></small>
        </div>
    </a>
    <a class="nav-login" href="login.php" id="nav-login-btn">
        <i class="fa-solid fa-right-to-bracket"></i>
        Login
    </a>
</nav>

<!-- HERO -->
<section class="hero" id="home">
    <div class="hero-blob hero-blob-1"></div>
    <div class="hero-blob hero-blob-2"></div>
    <div class="hero-blob hero-blob-3"></div>
    <div class="hero-inner">
        <div class="hero-badge">
            <i class="fa-solid fa-shield-halved"></i>
            Official SDO Management Platform
        </div>
        <img src="assets/images/SDO-Logo.png" alt="SDO Logo" class="hero-logo">
        <h1>Schools Division Office<br><span>Management System</span></h1>
        <p class="hero-sub">
            A centralized, secure platform for managing PhilHealth remittances and employee leave monitoring — built for efficiency and reliability.
        </p>
        <div class="hero-actions">
            <a href="login.php" class="btn-primary" id="hero-login-btn">
                <i class="fa-solid fa-right-to-bracket"></i>
                Access System
            </a>
            <a href="#systems" class="btn-outline">
                <i class="fa-solid fa-th-large"></i>
                Explore Features
            </a>
        </div>
        <div class="hero-stats">
            <div class="stat">
                <div class="stat-num">2</div>
                <div class="stat-label">Integrated Systems</div>
            </div>
            <div class="stat">
                <div class="stat-num">100%</div>
                <div class="stat-label">Secure & Encrypted</div>
            </div>
            <div class="stat">
                <div class="stat-num">24/7</div>
                <div class="stat-label">Online Access</div>
            </div>
        </div>
    </div>
</section>

<!-- SYSTEMS -->
<section class="systems" id="systems">
    <div class="text-center">
        <div class="section-label mx-auto" style="justify-content:center;">Available Systems</div>
        <h2 class="section-title">Everything You Need<br><span>In One Place</span></h2>
        <p class="section-sub mx-auto">Two powerful tools for managing critical division-wide administrative processes.</p>
    </div>

    <div class="systems-grid">
        <div class="system-card reveal">
            <div class="system-icon"><i class="fa-solid fa-file-medical"></i></div>
            <h3>PhilHealth Remittance Reports</h3>
            <p>Generate, manage, and monitor PhilHealth remittance reports for all employees. Maintain accurate records and ensure timely compliance submissions.</p>
            <span class="system-tag"><i class="fa-solid fa-circle-check"></i> Active System</span>
        </div>
        <div class="system-card reveal" style="transition-delay:0.12s">
            <div class="system-icon"><i class="fa-solid fa-calendar-check"></i></div>
            <h3>Leave Monitoring System</h3>
            <p>Track and manage employee leave requests, status updates, and separation records. Easily upload, review, and maintain employee data with Excel integration.</p>
            <span class="system-tag"><i class="fa-solid fa-circle-check"></i> Active System</span>
        </div>
    </div>
</section>

<!-- FEATURES -->
<section id="features">
    <div class="text-center">
        <div class="section-label mx-auto" style="justify-content:center;">Why Use This Platform</div>
        <h2 class="section-title">Built for <span>Schools Division</span><br>Administration</h2>
        <p class="section-sub mx-auto">Designed around the real needs of SDO staff — fast, reliable, and easy to use.</p>
    </div>
    <div class="features-grid">
        <div class="feature-card reveal">
            <div class="feature-icon"><i class="fa-solid fa-lock"></i></div>
            <h4>Secure Login</h4>
            <p>Role-based access control ensures that only authorized personnel can access sensitive records and administrative functions.</p>
        </div>
        <div class="feature-card reveal" style="transition-delay:0.08s">
            <div class="feature-icon"><i class="fa-solid fa-file-excel"></i></div>
            <h4>XLSX Import</h4>
            <p>Upload employee data directly via Excel spreadsheets. The system parses and stores records automatically with validation.</p>
        </div>
        <div class="feature-card reveal" style="transition-delay:0.16s">
            <div class="feature-icon"><i class="fa-solid fa-chart-bar"></i></div>
            <h4>Real-Time Reports</h4>
            <p>Generate accurate, up-to-date remittance and leave reports instantly — no manual computation required.</p>
        </div>
        <div class="feature-card reveal" style="transition-delay:0.24s">
            <div class="feature-icon"><i class="fa-solid fa-users"></i></div>
            <h4>Multi-User Access</h4>
            <p>Supports multiple user roles including administrators, staff, and super admins — each with tailored access levels.</p>
        </div>
        <div class="feature-card reveal" style="transition-delay:0.32s">
            <div class="feature-icon"><i class="fa-solid fa-print"></i></div>
            <h4>Print & Export</h4>
            <p>Print employee records, remittance reports, and leave summaries directly from the browser with clean, formatted layouts.</p>
        </div>
        <div class="feature-card reveal" style="transition-delay:0.40s">
            <div class="feature-icon"><i class="fa-solid fa-cloud"></i></div>
            <h4>Cloud Accessible</h4>
            <p>Access the system from anywhere — whether at the office or in the field — through a secure web connection.</p>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="cta">
    <div class="cta-inner">
        <h2>Ready to Get Started?</h2>
        <p>Login to your account and access the full suite of SDO management tools. Authorized personnel only.</p>
        <a href="login.php" class="btn-white" id="cta-login-btn">
            <i class="fa-solid fa-right-to-bracket"></i>
            Login to Your Account
        </a>
    </div>
</section>

<!-- FOOTER -->
<footer>
    <img src="assets/images/SDO-Logo.png" alt="SDO Logo" class="footer-logo">
    <p><strong>Schools Division Office – Management System</strong></p>
    <p style="margin-top:8px;">© <?= date('Y') ?> Schools Division Office. All rights reserved.</p>
</footer>

<script>
    // Navbar scroll shadow
    const navbar = document.getElementById('navbar');
    window.addEventListener('scroll', function () {
        navbar.classList.toggle('scrolled', window.scrollY > 20);
    });

    // Reveal on scroll
    const reveals = document.querySelectorAll('.reveal');
    const observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.12 });
    reveals.forEach(function (el) { observer.observe(el); });
</script>
</body>
</html>
