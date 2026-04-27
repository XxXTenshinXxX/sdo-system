<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle ?? 'User2 PhilHealth Remittance', ENT_QUOTES, 'UTF-8') ?></title>
<link rel="icon" type="image/png" href="../../assets/images/SDO-Logo.png">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
    :root {
        --bg: #f4f7fb;
        --surface: #ffffff;
        --surface-soft: #eef6ff;
        --text: #1f2937;
        --muted: #6b7280;
        --primary: #1d4ed8;
        --primary-dark: #163ea8;
        --accent: #0f766e;
        --danger: #dc2626;
        --border: #d9e2ec;
        --shadow: 0 18px 38px rgba(15, 23, 42, 0.08);
        --page-zoom: 0.9;
    }

    * {
        box-sizing: border-box;
    }

    html, body {
        margin: 0;
        width: 100%;
        min-height: 100vh;
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        background: var(--bg);
        color: var(--text);
    }

    body {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
        zoom: var(--page-zoom);
        min-height: calc(100vh / var(--page-zoom));
    }

    .layout {
        display: grid;
        grid-template-columns: 260px minmax(0, 1fr);
        flex: 1;
        min-height: 100vh;
        align-items: stretch;
    }

    .sidebar {
        background: #0f172a;
        color: #e5eefb;
        padding: 28px 18px;
        display: flex;
        flex-direction: column;
        gap: 22px;
        position: sticky;
        top: 0;
        height: calc(100vh / var(--page-zoom));
        min-height: calc(100vh / var(--page-zoom));
        overflow-y: auto;
    }

    .sidebar-brand {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 10px;
        padding: 4px 0 10px;
        text-align: center;
    }

    .sidebar-brand-logo {
        width: 92px;
        height: 92px;
        object-fit: contain;
    }

    .sidebar-brand-copy strong {
        display: block;
        color: #ffffff;
        font-size: 16px;
        line-height: 1.2;
        letter-spacing: 0.01em;
    }

    .sidebar-brand-copy span {
        display: block;
        margin-top: 4px;
        color: #9db0cf;
        font-size: 11px;
        line-height: 1.35;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .sidebar-section-title {
        margin: 0 0 10px;
        font-size: 12px;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #8ea0bd;
    }

    .menu {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .menu a {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 13px 14px;
        border-radius: 14px;
        color: inherit;
        text-decoration: none;
        transition: background 0.2s ease, transform 0.2s ease;
    }

    .menu a:hover,
    .menu a.active {
        background: rgba(148, 163, 184, 0.16);
        transform: translateX(3px);
    }

    .menu a i {
        width: 18px;
        min-width: 18px;
        text-align: center;
    }

    .content {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
        background: var(--bg);
        min-width: 0;
    }

    .navbar {
        min-height: 72px;
        background: var(--surface);
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 26px;
        margin-bottom: 24px;
        border-bottom: 1px solid var(--border);
        backdrop-filter: blur(10px);
        position: relative;
        z-index: 100;
    }

    .brand {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .brand-badge {
        width: 42px;
        height: 42px;
        border-radius: 14px;
        background: linear-gradient(135deg, #bfdbfe, #93c5fd);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary-dark);
        font-size: 18px;
    }

    .brand h1 {
        margin: 0;
        font-size: 20px;
    }

    .brand p {
        margin: 3px 0 0;
        color: var(--muted);
        font-size: 12px;
    }

    .nav-actions {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .nav-popover-wrap {
        position: relative;
    }

    .nav-icon-button {
        position: relative;
        width: 42px;
        height: 42px;
        border: 1px solid #dbeafe;
        border-radius: 14px;
        background: #f8fbff;
        color: var(--primary-dark);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        cursor: pointer;
        transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
    }

    .nav-icon-button:hover {
        background: #eff6ff;
        transform: translateY(-1px);
        box-shadow: 0 10px 20px rgba(29, 78, 216, 0.12);
    }

    .nav-icon-badge {
        position: absolute;
        top: 8px;
        right: 8px;
        width: 9px;
        height: 9px;
        border-radius: 50%;
        background: #ef4444;
        border: 2px solid #ffffff;
    }

    .notification-popover {
        position: absolute;
        top: calc(100% + 12px);
        right: 0;
        width: min(360px, calc(100vw - 32px));
        border: 1px solid #dbeafe;
        border-radius: 20px;
        background: linear-gradient(180deg, #ffffff, #f8fbff);
        box-shadow: 0 24px 44px rgba(15, 23, 42, 0.18);
        overflow: hidden;
        z-index: 50;
    }

    .notification-popover-head {
        padding: 16px 18px 14px;
        border-bottom: 1px solid #e2e8f0;
        background: rgba(239, 246, 255, 0.8);
    }

    .notification-popover-head strong {
        display: block;
        font-size: 15px;
        color: #0f172a;
    }

    .notification-popover-head span {
        display: block;
        margin-top: 4px;
        font-size: 12px;
        color: #64748b;
    }

    .notification-list {
        display: flex;
        flex-direction: column;
        gap: 0;
    }

    .notification-item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 16px 18px;
        border-top: 1px solid rgba(226, 232, 240, 0.8);
    }

    .notification-item:first-child {
        border-top: none;
    }

    .notification-item strong {
        display: block;
        font-size: 13px;
        color: #0f172a;
        margin-bottom: 4px;
    }

    .notification-item p {
        margin: 0;
        font-size: 12px;
        line-height: 1.55;
        color: #64748b;
    }

    .notification-dot {
        width: 10px;
        height: 10px;
        margin-top: 4px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .notification-dot.is-info {
        background: #2563eb;
        box-shadow: 0 0 0 6px rgba(37, 99, 235, 0.12);
    }

    .notification-dot.is-success {
        background: #16a34a;
        box-shadow: 0 0 0 6px rgba(22, 163, 74, 0.12);
    }

    .notification-dot.is-warn {
        background: #d97706;
        box-shadow: 0 0 0 6px rgba(217, 119, 6, 0.12);
    }

    .nav-chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 14px;
        border-radius: 999px;
        background: #eff6ff;
        color: var(--primary-dark);
        font-size: 13px;
        font-weight: 700;
    }

    .nav-chip.role-super-admin {
        background: #eff6ff;
        color: #1d4ed8;
    }

    .profile-button {
        width: 42px;
        height: 42px;
        border: none;
        border-radius: 50%;
        background: #dbeafe;
        color: var(--primary-dark);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .profile-trigger-button {
        cursor: pointer;
        box-shadow: 0 10px 18px rgba(37, 99, 235, 0.12);
        transition: transform 0.2s ease, box-shadow 0.2s ease, filter 0.2s ease;
    }

    .profile-trigger-button:hover {
        transform: translateY(-1px);
        box-shadow: 0 14px 24px rgba(37, 99, 235, 0.16);
        filter: brightness(1.02);
    }

    .profile-avatar-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: inherit;
        display: block;
    }

    .profile-menu-popover {
        position: absolute;
        top: calc(100% + 12px);
        right: 0;
        width: min(380px, calc(100vw - 32px));
        border: 1px solid #dbeafe;
        border-radius: 22px;
        background: linear-gradient(180deg, #ffffff, #f8fbff);
        box-shadow: 0 24px 44px rgba(15, 23, 42, 0.18);
        overflow: hidden;
        z-index: 60;
    }

    .profile-menu-head {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 18px;
        border-bottom: 1px solid #e2e8f0;
        background: rgba(239, 246, 255, 0.82);
    }

    .profile-menu-avatar,
    .profile-popover-avatar {
        width: 52px;
        height: 52px;
        border-radius: 18px;
        background: linear-gradient(135deg, #dbeafe, #bfdbfe);
        color: #1d4ed8;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        font-weight: 800;
        flex-shrink: 0;
    }

    .profile-menu-head strong {
        display: block;
        font-size: 15px;
        color: #0f172a;
    }

    .profile-menu-head span {
        display: block;
        margin-top: 4px;
        font-size: 12px;
        color: #64748b;
        word-break: break-word;
    }

    .profile-menu-links {
        display: flex;
        flex-direction: column;
        gap: 0;
        padding: 10px;
    }

    .profile-menu-link {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 14px 12px;
        border-radius: 16px;
        text-decoration: none;
        color: inherit;
        transition: background 0.2s ease, transform 0.2s ease;
    }

    .profile-menu-link:hover {
        background: #eff6ff;
        transform: translateY(-1px);
    }

    .profile-menu-link i {
        width: 38px;
        height: 38px;
        border-radius: 12px;
        background: #dbeafe;
        color: #1d4ed8;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
        flex-shrink: 0;
    }

    .profile-menu-link strong {
        display: block;
        font-size: 13px;
        color: #0f172a;
        margin-bottom: 4px;
    }

    .profile-menu-link span {
        margin: 0;
        font-size: 12px;
        line-height: 1.55;
        color: #64748b;
    }

    .content-body {
        flex: 1;
        padding: 0 28px 28px;
    }

    .section {
        margin-bottom: 24px;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 18px;
    }

    .stat-card,
    .panel {
        background: var(--surface);
        border-radius: 20px;
        box-shadow: var(--shadow);
        border: 1px solid rgba(217, 226, 236, 0.85);
    }

    .stat-card {
        padding: 20px;
    }

    .label {
        font-size: 13px;
        color: var(--muted);
        margin-bottom: 10px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .value {
        font-size: 32px;
        font-weight: 700;
        line-height: 1.15;
        color: #0f172a;
    }

    .trend {
        margin-top: 10px;
        font-size: 12px;
        font-weight: 600;
    }

    .trend.up {
        color: #15803d;
    }

    .trend.warn {
        color: #b45309;
    }

    .main-grid {
        display: grid;
        grid-template-columns: minmax(0, 2fr) minmax(320px, 1fr);
        gap: 20px;
    }

    .panel {
        padding: 22px;
    }

    .section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 18px;
    }

    .section-header h3 {
        margin: 0;
        font-size: 18px;
        color: #0f172a;
    }

    .section-header a {
        color: var(--primary);
        text-decoration: none;
        font-size: 13px;
        font-weight: 700;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
    }

    .table th,
    .table td {
        text-align: left;
        padding: 14px 12px;
        border-bottom: 1px solid #e5e7eb;
        font-size: 13px;
        vertical-align: top;
    }

    .table th {
        color: #64748b;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .table tbody tr:last-child td {
        border-bottom: none;
    }

    .quick-links {
        display: grid;
        gap: 14px;
    }

    .quick-link {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        padding: 18px;
        border-radius: 18px;
        text-decoration: none;
        background: linear-gradient(135deg, #eff6ff, #f8fbff);
        border: 1px solid #dbeafe;
        color: #0f172a;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .quick-link:hover {
        transform: translateY(-2px);
        box-shadow: 0 16px 28px rgba(37, 99, 235, 0.12);
    }

    .quick-link i {
        width: 42px;
        height: 42px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #dbeafe;
        color: #1d4ed8;
        font-size: 18px;
        flex-shrink: 0;
    }

    .quick-link strong {
        display: block;
        margin-bottom: 6px;
        font-size: 15px;
    }

    .quick-link div div {
        color: #64748b;
        font-size: 13px;
        line-height: 1.5;
    }

    .footer {
        padding: 18px 28px 26px;
        color: #64748b;
        font-size: 12px;
    }

    @media (max-width: 1200px) {
        .stats-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .main-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 920px) {
        body {
            zoom: 1;
            min-height: 100vh;
        }

        .layout {
            grid-template-columns: 1fr;
        }

        .sidebar {
            position: static;
            height: auto;
            min-height: 0;
        }

        .navbar {
            padding: 18px 20px;
            align-items: flex-start;
            gap: 16px;
            flex-direction: column;
        }

        .content-body {
            padding: 0 20px 22px;
        }
    }

    @media (max-width: 640px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }

        .nav-actions,
        .brand {
            width: 100%;
        }

        .nav-actions {
            justify-content: flex-end;
            flex-wrap: wrap;
        }

        .table {
            display: block;
            overflow-x: auto;
        }
    }
</style>
</head>
