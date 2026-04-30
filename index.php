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

        html { zoom: 0.97; }

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

        /* ── TEAM ── */
        .team { background: var(--gray-50); }

        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 24px;
            max-width: 1000px;
            margin: 56px auto 0;
        }

        .team-card {
            background: #fff;
            border: 1.5px solid var(--gray-200);
            border-radius: var(--radius-lg);
            padding: 36px 28px 32px;
            text-align: center;
            transition: transform 0.25s, box-shadow 0.25s, border-color 0.25s;
        }
        .team-card:hover { transform: translateY(-6px); box-shadow: var(--shadow-lg); border-color: rgba(33,150,243,0.35); }

        .team-card-concept .team-avatar-ring { background: linear-gradient(135deg, #fff9c4, #ffe082); }
        .team-card-concept .team-avatar { background: linear-gradient(135deg, #f59e0b, #d97706); }

        .team-avatar-ring {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--blue-light), #bbdefb);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            padding: 4px;
        }

        .team-avatar {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--blue), var(--blue-mid));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: #fff;
        }

        .team-role-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 14px;
            border-radius: 999px;
            font-size: 11.5px;
            font-weight: 700;
            letter-spacing: 0.4px;
            text-transform: uppercase;
            background: #fff9c4;
            color: #92400e;
            border: 1px solid #fde68a;
            margin-bottom: 12px;
        }
        .team-role-badge.team-role-dev {
            background: var(--blue-light);
            color: var(--blue-mid);
            border-color: #bfdbfe;
        }

        .team-name {
            font-size: 19px;
            font-weight: 800;
            color: var(--gray-900);
            margin-bottom: 10px;
        }

        .team-desc {
            font-size: 13.5px;
            color: var(--gray-600);
            line-height: 1.65;
        }


        /* ── MISSION VISION ── */
        .mv-section {
            background: linear-gradient(160deg, #e3f2fd 0%, #ffffff 60%, #e8f4fd 100%);
            position: relative;
            overflow: hidden;
        }
        .mv-section::before {
            content: '';
            position: absolute;
            width: 600px; height: 600px;
            background: radial-gradient(circle, rgba(33,150,243,0.08) 0%, transparent 70%);
            top: -200px; right: -200px;
            pointer-events: none;
        }
        .mv-section::after {
            content: '';
            position: absolute;
            width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(21,101,192,0.07) 0%, transparent 70%);
            bottom: -100px; left: -100px;
            pointer-events: none;
        }

        .mv-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 28px;
            max-width: 1100px;
            margin: 56px auto 0;
            position: relative;
            z-index: 2;
        }

        .mv-card {
            background: rgba(255,255,255,0.88);
            backdrop-filter: blur(12px);
            border: 1.5px solid rgba(33,150,243,0.18);
            border-radius: var(--radius-lg);
            padding: 38px 36px;
            transition: transform 0.25s, box-shadow 0.25s, border-color 0.25s;
            position: relative;
            overflow: hidden;
        }
        .mv-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            border-radius: var(--radius-lg) var(--radius-lg) 0 0;
        }
        .mv-card-mission::before { background: linear-gradient(90deg, #2196f3, #1565c0); }
        .mv-card-vision::before  { background: linear-gradient(90deg, #1565c0, #0d47a1); }
        .mv-card-values          { grid-column: 1 / -1; }
        .mv-card-values::before  { background: linear-gradient(90deg, #1976d2, #42a5f5); }
        .mv-card:hover { transform: translateY(-5px); box-shadow: var(--shadow-lg); border-color: rgba(33,150,243,0.4); }

        .mv-icon-wrap {
            width: 56px; height: 56px;
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px;
            margin-bottom: 20px;
        }
        .mv-card-mission .mv-icon-wrap { background: linear-gradient(135deg, var(--blue-light), #bbdefb); color: var(--blue); }
        .mv-card-vision .mv-icon-wrap  { background: linear-gradient(135deg, #e8eaf6, #c5cae9); color: #3949ab; }
        .mv-card-values .mv-icon-wrap  { background: linear-gradient(135deg, #e8f5e9, #c8e6c9); color: #2e7d32; }

        .mv-card-title {
            font-size: 20px;
            font-weight: 800;
            color: var(--gray-900);
            margin-bottom: 16px;
            letter-spacing: -0.3px;
        }

        .mv-text {
            font-size: 14.5px;
            color: var(--gray-600);
            line-height: 1.8;
        }

        .mv-quote {
            font-size: 14.5px;
            color: var(--gray-600);
            line-height: 1.8;
            border-left: 3px solid rgba(33,150,243,0.4);
            padding-left: 16px;
            margin-bottom: 14px;
            font-style: italic;
        }

        .mv-list {
            list-style: none;
            padding: 0; margin: 12px 0 0;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .mv-list li {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            font-size: 14px;
            color: var(--gray-600);
            line-height: 1.7;
        }
        .mv-list li i {
            margin-top: 3px;
            color: var(--blue);
            font-size: 12px;
            flex-shrink: 0;
        }

        .core-values-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-top: 20px;
        }
        .cv-pill {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            padding: 20px 16px;
            background: linear-gradient(135deg, var(--blue-light), #dbeafe);
            border: 1.5px solid rgba(33,150,243,0.2);
            border-radius: var(--radius);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .cv-pill:hover { transform: translateY(-3px); box-shadow: var(--shadow); }
        .cv-pill-icon {
            width: 44px; height: 44px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px;
            background: #fff;
            box-shadow: 0 4px 12px rgba(33,150,243,0.15);
        }
        .cv-pill span {
            font-size: 13px;
            font-weight: 700;
            color: var(--blue-dark);
            text-align: center;
            letter-spacing: 0.2px;
        }

        /* ── RESPONSIVE ── */
        @media (max-width: 900px) {
            .mv-grid { grid-template-columns: 1fr; }
            .mv-card-values { grid-column: 1; }
            .core-values-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 640px) {
            nav { padding: 0 20px; }
            .hero-stats { gap: 24px; }
            .hero-actions { flex-direction: column; align-items: center; }
            section { padding: 64px 20px; }
            .core-values-grid { grid-template-columns: repeat(2, 1fr); }
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

<!-- MISSION VISION CORE VALUES -->
<section class="mv-section" id="mission-vision">
    <div class="text-center" style="position:relative;z-index:2;">
        <div class="section-label mx-auto" style="justify-content:center;">Our Foundation</div>
        <h2 class="section-title">Mission, Vision &amp;<br><span>Core Values</span></h2>
        <p class="section-sub mx-auto">The guiding principles of the Schools Division Office, Quezon City — rooted in service, excellence, and Filipino values.</p>
    </div>

    <div class="mv-grid">
        <!-- MISSION -->
        <div class="mv-card mv-card-mission reveal">
            <div class="mv-icon-wrap"><i class="fa-solid fa-bullseye"></i></div>
            <div class="mv-card-title">Mission</div>
            <p class="mv-text" style="margin-bottom:16px;">To protect and promote the right of every Filipino to quality, equitable, culture-based, and complete basic education where:</p>
            <ul class="mv-list">
                <li><i class="fa-solid fa-circle-dot"></i>Students learn in a child-friendly, gender-sensitive, safe, and motivating environment.</li>
                <li><i class="fa-solid fa-circle-dot"></i>Teachers facilitate learning and constantly nurture every learner.</li>
                <li><i class="fa-solid fa-circle-dot"></i>Administrators and staff, as stewards of the institution, ensure an enabling and supportive environment for effective learning to happen.</li>
                <li><i class="fa-solid fa-circle-dot"></i>Family, community, and other stakeholders are actively engaged and share the responsibility for developing life-long learners.</li>
            </ul>
        </div>

        <!-- VISION -->
        <div class="mv-card mv-card-vision reveal" style="transition-delay:0.12s">
            <div class="mv-icon-wrap"><i class="fa-solid fa-eye"></i></div>
            <div class="mv-card-title">Vision</div>
            <p class="mv-quote">We dream of Filipinos who passionately love their country and whose competencies and values enable them to realize their potential and contribute meaningfully to building the nation.</p>
            <p class="mv-quote" style="margin-bottom:0;">As a learner-centered public institution, the Department of Education continuously improves itself to better serve its stakeholders.</p>
        </div>

        <!-- CORE VALUES -->
        <div class="mv-card mv-card-values reveal" style="transition-delay:0.22s">
            <div class="mv-icon-wrap"><i class="fa-solid fa-star"></i></div>
            <div class="mv-card-title">Core Values</div>
            <p class="mv-text" style="margin-bottom:4px;">The Department of Education upholds the following Filipino core values:</p>
            <div class="core-values-grid">
                <div class="cv-pill">
                    <div class="cv-pill-icon" style="color:#c0392b;"><i class="fa-solid fa-cross"></i></div>
                    <span>Maka-Diyos</span>
                </div>
                <div class="cv-pill">
                    <div class="cv-pill-icon" style="color:#2980b9;"><i class="fa-solid fa-people-group"></i></div>
                    <span>Maka-tao</span>
                </div>
                <div class="cv-pill">
                    <div class="cv-pill-icon" style="color:#27ae60;"><i class="fa-solid fa-leaf"></i></div>
                    <span>Makakalikasan</span>
                </div>
                <div class="cv-pill">
                    <div class="cv-pill-icon" style="color:#f39c12;"><i class="fa-solid fa-flag"></i></div>
                    <span>Makabansa</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- TEAM -->
<section class="team" id="team">
    <div class="text-center">
        <div class="section-label mx-auto" style="justify-content:center;">The People Behind It</div>
        <h2 class="section-title">Concept &amp; <span>Development Team</span></h2>
        <p class="section-sub mx-auto">Built by dedicated individuals committed to improving administrative efficiency in the Schools Division Office.</p>
    </div>

    <div class="team-grid">
        <div class="team-card team-card-concept reveal">
            <div class="team-avatar-ring">
                <div class="team-avatar">
                    <i class="fa-solid fa-lightbulb"></i>
                </div>
            </div>
            <div class="team-role-badge">Conceptualized By</div>
            <h3 class="team-name">Marivel E. Unciano</h3>
            <p class="team-desc">The vision and concept behind this system — providing a smarter way to manage remittance and leave records for the Schools Division Office.</p>
        </div>

        <div class="team-card reveal" style="transition-delay:0.1s">
            <div class="team-avatar-ring">
                <div class="team-avatar">
                    <i class="fa-solid fa-code"></i>
                </div>
            </div>
            <div class="team-role-badge team-role-dev">Lead Developer</div>
            <h3 class="team-name">Ronil John Gadot</h3>
            <p class="team-desc">Designed and developed the system architecture, user interface, and core functionality of the SDO Management System.</p>
        </div>

        <div class="team-card reveal" style="transition-delay:0.2s">
            <div class="team-avatar-ring">
                <div class="team-avatar">
                    <i class="fa-solid fa-laptop-code"></i>
                </div>
            </div>
            <div class="team-role-badge team-role-dev">Developer</div>
            <h3 class="team-name">Romeo Romero Jr.</h3>
            <p class="team-desc">Contributed to the development and implementation of key system features and functionality.</p>
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
    <p><strong>Schools Division Office – Remittance &amp; Leave Management System</strong></p>
    <p style="margin-top:10px;">Conceptualized by <strong style="color:rgba(255,255,255,0.8);">Marivel E. Unciano</strong> &nbsp;|&nbsp; Developed by <strong style="color:rgba(255,255,255,0.8);">Ronil John Gadot</strong> &amp; <strong style="color:rgba(255,255,255,0.8);">Romeo Romero Jr.</strong></p>
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
