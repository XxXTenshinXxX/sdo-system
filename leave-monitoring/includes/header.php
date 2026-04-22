<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle ?? 'Leave Monitoring', ENT_QUOTES, 'UTF-8') ?></title>
<link rel="icon" type="image/png" href="../assets/images/SDO-Logo.png">
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

    .navbar {
        height: 72px;
        background: var(--surface);
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 26px;
        position: sticky;
        top: 0;
        z-index: 20;
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
        cursor: pointer;
        transition: background 0.2s ease, transform 0.2s ease;
    }

    .profile-button:hover {
        background: #bfdbfe;
        transform: translateY(-1px);
    }

    .nav-chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 14px;
        border-radius: 999px;
        background: transparent;
        color: var(--text);
        font-size: 13px;
        font-weight: 600;
    }

    .nav-chip.role-super-admin {
        background: #eff6ff;
        color: #1d4ed8;
        font-weight: 700;
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

    .sidebar-brand-copy {
        min-width: 0;
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

    .menu-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .menu-group summary {
        list-style: none;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 13px 14px;
        border-radius: 14px;
        color: inherit;
        cursor: pointer;
        transition: background 0.2s ease, transform 0.2s ease;
    }

    .menu-group summary::-webkit-details-marker {
        display: none;
    }

    .menu-group summary span {
        display: inline-flex;
        align-items: center;
        gap: 12px;
    }

    .menu-group summary:hover,
    .menu-group summary.active {
        background: rgba(148, 163, 184, 0.16);
        transform: translateX(3px);
    }

    .menu-caret {
        font-size: 12px;
        color: #8ea0bd;
        transition: transform 0.2s ease;
    }

    .menu-group[open] .menu-caret {
        transform: rotate(180deg);
    }

    .submenu {
        display: grid;
        gap: 8px;
        padding-left: 16px;
    }

    .submenu a {
        padding: 11px 14px;
        border-radius: 12px;
        font-size: 13px;
        color: #cbd5e1;
        background: rgba(15, 23, 42, 0.22);
    }

    .submenu a:hover,
    .submenu a.active {
        background: rgba(59, 130, 246, 0.18);
        color: #eff6ff;
    }

    .sidebar-card {
        margin-top: auto;
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 18px;
        padding: 18px;
    }

    .sidebar-card h3 {
        margin: 0 0 10px;
        font-size: 16px;
    }

    .sidebar-card p {
        margin: 0 0 16px;
        color: #cbd5e1;
        line-height: 1.5;
        font-size: 13px;
    }

    .sidebar-card a {
        display: inline-block;
        padding: 10px 14px;
        border-radius: 10px;
        background: #38bdf8;
        color: #082f49;
        text-decoration: none;
        font-weight: 700;
        font-size: 13px;
    }

    .content {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
        background: var(--bg);
        min-width: 0;
    }

    .content .navbar {
        position: static;
        border: none;
        border-bottom: none;
        border-radius: 0;
        margin-bottom: 24px;
        box-shadow: none;
    }

    .content-body {
        flex: 1;
        padding: 0 28px 28px;
    }

    .hero {
        background: linear-gradient(135deg, #dbeafe, #eff6ff);
        border: 1px solid #cfe0fb;
        border-radius: 24px;
        padding: 28px;
        display: flex;
        justify-content: space-between;
        gap: 20px;
        align-items: center;
        box-shadow: var(--shadow);
    }

    .hero h2 {
        margin: 0 0 10px;
        font-size: 30px;
    }

    .hero p {
        margin: 0;
        max-width: 580px;
        color: #475569;
        line-height: 1.6;
    }

    .hero-stats {
        display: grid;
        grid-template-columns: repeat(2, minmax(120px, 1fr));
        gap: 14px;
        min-width: 260px;
    }

    .mini-stat {
        background: rgba(255, 255, 255, 0.82);
        border-radius: 18px;
        padding: 16px;
        text-align: center;
    }

    .mini-stat strong {
        display: block;
        font-size: 24px;
        margin-bottom: 6px;
    }

    .mini-stat span {
        color: var(--muted);
        font-size: 12px;
    }

    .section {
        margin-top: 26px;
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
        gap: 16px;
        flex-wrap: wrap;
    }

    .section-header h3 {
        margin: 0;
        font-size: 20px;
    }

    .section-header-copy {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .section-subcopy {
        margin: 0;
        color: var(--muted);
        font-size: 13px;
        line-height: 1.45;
    }

    .section-header-actions {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        margin-left: auto;
        justify-content: flex-end;
        flex: 1 1 520px;
    }

    .section-header a {
        color: var(--primary);
        text-decoration: none;
        font-size: 13px;
        font-weight: 700;
        white-space: nowrap;
    }

    .table-search-field {
        min-width: 260px;
        flex: 1 1 320px;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 10px 14px;
        border: 1px solid #dbeafe;
        border-radius: 14px;
        background: #f8fbff;
        color: #1d4ed8;
    }

    .table-search-field input {
        width: 100%;
        min-width: 0;
        border: none;
        outline: none;
        background: transparent;
        color: var(--text);
        font-size: 13px;
    }

    .table-search-field input::placeholder {
        color: #94a3b8;
    }

    .search-empty-row[hidden] {
        display: none;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 18px;
    }

    .stat-card,
    .panel {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 20px;
        padding: 22px;
        box-shadow: var(--shadow);
    }

    .stat-card {
        position: relative;
        overflow: hidden;
    }

    .stat-card::after {
        content: "";
        position: absolute;
        top: -18px;
        right: -18px;
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: rgba(59, 130, 246, 0.08);
    }

    .stat-card .label {
        color: var(--muted);
        font-size: 13px;
        margin-bottom: 10px;
    }

    .stat-card .value {
        font-size: 32px;
        font-weight: 800;
        margin-bottom: 8px;
    }

    .stat-card .trend {
        font-size: 13px;
        font-weight: 600;
    }

    .trend.up {
        color: var(--accent);
    }

    .trend.warn {
        color: #b45309;
    }

    .main-grid {
        display: grid;
        grid-template-columns: 1.5fr 1fr;
        gap: 18px;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
    }

    .table th,
    .table td {
        padding: 14px 0;
        border-bottom: 1px solid #e5e7eb;
        font-size: 14px;
    }

    .table th {
        text-align: center;
        color: var(--muted);
        font-weight: 700;
    }

    .table td {
        text-align: center;
        vertical-align: middle;
    }

    .table-checkbox {
        width: 18px;
        height: 18px;
        accent-color: #2563eb;
        cursor: pointer;
    }

    .status {
        display: inline-flex;
        align-items: center;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
    }

    .status.approved {
        background: #dcfce7;
        color: #166534;
    }

    .status.pending {
        background: #fef3c7;
        color: #92400e;
    }

    .status.review {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .quick-links {
        display: grid;
        gap: 12px;
    }

    .quick-link {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 16px;
        border-radius: 16px;
        background: var(--surface-soft);
        text-decoration: none;
        color: inherit;
    }

    .quick-link i {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        background: #bfdbfe;
        color: var(--primary-dark);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .toolbar-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 20px;
        padding: 20px 22px;
        box-shadow: var(--shadow);
    }

    .toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
    }

    .toolbar h2 {
        margin: 0 0 6px;
        font-size: 24px;
    }

    .toolbar p {
        margin: 0;
        color: var(--muted);
        font-size: 14px;
    }

    .toolbar-actions {
        display: flex;
        gap: 12px;
        align-items: center;
        flex-wrap: wrap;
    }

    .upload-form {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .file-input {
        padding: 10px 12px;
        border: 1px solid var(--border);
        border-radius: 12px;
        background: #fff;
        font-size: 13px;
        color: var(--muted);
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        border: none;
        border-radius: 12px;
        padding: 11px 16px;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        text-decoration: none;
        transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
    }

    .btn:hover {
        transform: translateY(-1px);
    }

    .btn-primary {
        background: var(--primary);
        color: #fff;
        box-shadow: 0 12px 22px rgba(29, 78, 216, 0.18);
    }

    .btn-primary:hover {
        background: var(--primary-dark);
    }

    .btn-soft {
        background: #eff6ff;
        color: var(--primary-dark);
    }

    .btn-outline {
        background: #fff;
        color: var(--primary-dark);
        border: 1px solid #bfdbfe;
    }

    .btn-danger {
        background: linear-gradient(135deg, #dc2626, #ef4444);
        color: #fff;
        box-shadow: 0 12px 22px rgba(220, 38, 38, 0.2);
    }

    .btn-danger:hover {
        background: linear-gradient(135deg, #b91c1c, #dc2626);
    }

    .table-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 20px;
        padding: 22px;
        box-shadow: var(--shadow);
        overflow-x: auto;
    }

    .table-card .table {
        min-width: 820px;
    }

    .alert-banner {
        margin-bottom: 18px;
        padding: 14px 16px;
        border-radius: 14px;
        font-size: 14px;
        font-weight: 600;
    }

    .alert-success {
        background: #dcfce7;
        color: #166534;
        border: 1px solid #bbf7d0;
    }

    .alert-warning {
        background: #fef3c7;
        color: #92400e;
        border: 1px solid #fcd34d;
    }

    .alert-error {
        background: #fee2e2;
        color: #b91c1c;
        border: 1px solid #fecaca;
    }

    .alert-banner-title {
        font-weight: 700;
    }

    .upload-result-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-top: 12px;
    }

    .upload-result-item {
        padding: 10px 12px;
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.58);
        border: 1px solid rgba(148, 163, 184, 0.24);
    }

    .upload-result-item strong,
    .upload-result-item span {
        display: block;
    }

    .upload-result-item strong {
        margin-bottom: 4px;
        font-size: 13px;
    }

    .upload-result-item span {
        font-size: 12px;
        line-height: 1.5;
    }

    .upload-result-item-success {
        border-color: rgba(34, 197, 94, 0.3);
    }

    .upload-result-item-error {
        border-color: rgba(239, 68, 68, 0.28);
    }

    .empty-state-cell {
        text-align: center;
        color: var(--muted);
        padding: 24px 12px !important;
    }

    .table-actions {
        display: inline-flex;
        gap: 8px;
        align-items: center;
        flex-wrap: wrap;
    }

    .inline-action-form {
        margin: 0;
    }

    .table-btn {
        border: none;
        border-radius: 10px;
        padding: 8px 12px;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
    }

    .table-btn.edit {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .table-btn.delete {
        background: #fee2e2;
        color: #b91c1c;
    }

    .action-icon-btn {
        width: 38px;
        height: 38px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
    }

    .edit-employee-btn {
        background: #ca8a04 !important;
        color: #fff7ed !important;
    }

    .edit-employee-btn:hover {
        background: #a16207 !important;
    }

    .alert-banner {
        transition: opacity 0.35s ease, transform 0.35s ease, max-height 0.35s ease, margin 0.35s ease, padding 0.35s ease;
        opacity: 1;
        transform: translateY(0);
        max-height: 120px;
        overflow: hidden;
    }

    .alert-banner.is-hiding {
        opacity: 0;
        transform: translateY(-6px);
        max-height: 0;
        margin: 0;
        padding-top: 0;
        padding-bottom: 0;
    }

    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.45);
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
        z-index: 1000;
    }

    .modal-overlay.show {
        display: flex;
    }

    .modal-card {
        width: 100%;
        max-width: 520px;
        background: var(--surface);
        border-radius: 22px;
        padding: 24px;
        box-shadow: 0 24px 48px rgba(15, 23, 42, 0.2);
        animation: modalIn 0.2s ease;
    }

    .modal-card h3 {
        margin: 0 0 8px;
        font-size: 24px;
    }

    .modal-card p {
        margin: 0 0 18px;
        color: var(--muted);
        line-height: 1.5;
        font-size: 14px;
    }

    .modal-card .upload-form {
        flex-direction: column;
        align-items: stretch;
    }

    .file-upload-field {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .modal-card .file-input {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border: 0;
    }

    .file-picker {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 18px;
        border: 1px dashed #93c5fd;
        border-radius: 18px;
        background: linear-gradient(135deg, #f8fbff, #eef6ff);
        cursor: pointer;
        transition: border-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
    }

    .file-picker:hover {
        border-color: var(--primary);
        transform: translateY(-1px);
        box-shadow: 0 14px 28px rgba(29, 78, 216, 0.12);
    }

    .file-picker-icon {
        width: 52px;
        height: 52px;
        border-radius: 16px;
        background: #dbeafe;
        color: var(--primary-dark);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        flex-shrink: 0;
    }

    .file-picker-copy {
        display: flex;
        flex-direction: column;
        gap: 4px;
        min-width: 0;
        flex: 1;
    }

    .file-picker-copy strong {
        font-size: 15px;
        color: var(--text);
    }

    .file-picker-copy span {
        font-size: 13px;
        line-height: 1.5;
        color: var(--muted);
    }

    .file-picker-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 10px 14px;
        border-radius: 12px;
        background: #fff;
        border: 1px solid #bfdbfe;
        color: var(--primary-dark);
        font-size: 13px;
        font-weight: 700;
        white-space: nowrap;
    }

    .file-selected-name {
        margin: 0;
        padding: 10px 14px;
        border-radius: 12px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #475569;
        font-size: 13px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        min-height: 42px;
        display: flex;
        align-items: center;
    }

    .upload-status-indicator {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 14px;
        border-radius: 12px;
        border: 1px solid #dbeafe;
        background: #f8fbff;
        color: #475569;
        font-size: 13px;
        line-height: 1.45;
    }

    .upload-status-dot {
        width: 10px;
        height: 10px;
        border-radius: 999px;
        background: #94a3b8;
        flex-shrink: 0;
    }

    .upload-status-indicator.is-ready .upload-status-dot {
        background: #22c55e;
    }

    .upload-status-indicator.is-uploading .upload-status-dot {
        background: #2563eb;
        box-shadow: 0 0 0 6px rgba(37, 99, 235, 0.14);
    }

    .btn[disabled] {
        opacity: 0.72;
        cursor: wait;
        pointer-events: none;
    }

    .modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin: 4px 24px 24px;
        flex-wrap: wrap;
    }

    .employee-form {
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

    .employee-modal-card {
        max-width: 720px;
        padding: 0;
        overflow: hidden;
    }

    .confirm-delete-card {
        max-width: 560px;
        padding: 0;
        overflow: hidden;
        border: 1px solid rgba(248, 113, 113, 0.18);
        background:
            radial-gradient(circle at top right, rgba(248, 113, 113, 0.12), transparent 32%),
            linear-gradient(180deg, #ffffff, #fff7f7);
    }

    .confirm-delete-hero {
        display: flex;
        gap: 18px;
        padding: 26px 24px 20px;
        border-bottom: 1px solid #fee2e2;
    }

    .confirm-delete-icon {
        width: 60px;
        height: 60px;
        border-radius: 20px;
        background: linear-gradient(135deg, #fee2e2, #fecaca);
        color: #b91c1c;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.65);
        flex-shrink: 0;
    }

    .confirm-delete-kicker {
        display: inline-flex;
        align-items: center;
        padding: 6px 10px;
        border-radius: 999px;
        background: #fee2e2;
        color: #b91c1c;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        margin-bottom: 10px;
    }

    .confirm-delete-hero h3 {
        margin: 0 0 8px;
        font-size: 24px;
        line-height: 1.2;
        color: #0f172a;
    }

    .confirm-delete-hero p {
        margin: 0;
        color: #64748b;
        font-size: 14px;
        line-height: 1.6;
    }

    .confirm-delete-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        padding: 20px 24px 24px;
        flex-wrap: wrap;
        background: rgba(255, 255, 255, 0.72);
    }

    .leave-modal-card {
        width: min(760px, 92vw);
        max-width: 760px;
        padding: 0;
        overflow: hidden;
        max-height: min(88vh, 860px);
        display: flex;
        flex-direction: column;
    }

    .employee-modal-hero {
        display: flex;
        align-items: flex-start;
        gap: 16px;
        padding: 24px 24px 18px;
        background: linear-gradient(135deg, #eff6ff, #f8fbff 65%, #ffffff);
        border-bottom: 1px solid #dbeafe;
    }

    .employee-modal-badge {
        width: 56px;
        height: 56px;
        border-radius: 18px;
        background: linear-gradient(135deg, #1d4ed8, #3b82f6);
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        box-shadow: 0 14px 24px rgba(29, 78, 216, 0.2);
        flex-shrink: 0;
    }

    .employee-modal-hero h3 {
        margin: 0 0 8px;
        font-size: 24px;
    }

    .employee-modal-hero p {
        margin: 0;
        max-width: 520px;
    }

    .leave-modal-hero {
        background: linear-gradient(135deg, #eefbf3, #f8fffb 65%, #ffffff);
        border-bottom: 1px solid #d1fae5;
        padding: 18px 20px 14px;
    }

    .leave-entry-form {
        gap: 12px;
        padding: 16px 0 0;
        overflow-y: auto;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }

    .employee-form-section {
        margin: 0 24px;
        padding: 18px;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        background: linear-gradient(180deg, #ffffff, #f8fafc);
    }

    .leave-entry-form .employee-form-section {
        margin: 0 20px;
        padding: 14px;
        border-radius: 16px;
    }

    .leave-entry-form .form-grid {
        gap: 12px;
    }

    .leave-entry-form .employee-section-heading {
        margin-bottom: 12px;
    }

    .leave-entry-form .modal-actions {
        margin: 0 20px 20px;
        padding-top: 4px;
    }

    .employee-section-heading {
        display: flex;
        flex-direction: column;
        gap: 4px;
        margin-bottom: 16px;
    }

    .employee-section-heading span {
        font-size: 14px;
        font-weight: 800;
        color: #0f172a;
        letter-spacing: 0.01em;
    }

    .employee-section-heading small {
        font-size: 12px;
        color: #64748b;
    }

    .form-field {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .form-field label {
        font-size: 13px;
        font-weight: 700;
        color: #334155;
    }

    .form-field input,
    .form-field select,
    .form-field textarea {
        width: 100%;
        padding: 12px 14px;
        border: 1px solid var(--border);
        border-radius: 12px;
        background: #fff;
        color: var(--text);
        font-size: 14px;
        outline: none;
        transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
    }

    .form-field input:focus,
    .form-field select:focus,
    .form-field textarea:focus {
        border-color: #93c5fd;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.12);
        transform: translateY(-1px);
    }

    .form-field textarea {
        resize: vertical;
        min-height: 72px;
        font-family: inherit;
    }

    .form-field-full {
        grid-column: 1 / -1;
    }

    .image-preview-card {
        width: min(1180px, 94vw);
        max-width: none;
        padding: 0;
        overflow: hidden;
        border-radius: 28px;
        background: #f8fbff;
    }

    .image-preview-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        padding: 24px 28px 20px;
        background: linear-gradient(180deg, #ffffff, #f8fbff);
        border-bottom: 1px solid #e2e8f0;
    }

    .image-preview-copy {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .image-preview-actions {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-shrink: 0;
    }

    .image-preview-print-btn {
        min-height: 44px;
        padding-inline: 16px;
        border-radius: 14px;
    }

    .image-preview-add-btn {
        min-height: 44px;
        padding-inline: 16px;
        border-radius: 14px;
        box-shadow: 0 10px 20px rgba(29, 78, 216, 0.12);
    }

    .image-preview-kicker {
        display: inline-flex;
        align-items: center;
        width: fit-content;
        padding: 6px 10px;
        border-radius: 999px;
        background: #e0ecff;
        color: #1d4ed8;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }

    .preview-close-btn {
        width: 44px;
        height: 44px;
        border: 1px solid #dbeafe;
        border-radius: 14px;
        background: #fff;
        color: #1d4ed8;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
    }

    .preview-close-btn:hover {
        background: #eff6ff;
        transform: translateY(-1px);
        box-shadow: 0 10px 20px rgba(29, 78, 216, 0.12);
    }

    .image-preview-frame {
        margin: 18px;
        border: 1px solid #dbe4f0;
        border-radius: 22px;
        background:
            linear-gradient(180deg, rgba(255,255,255,0.98), rgba(248,250,252,0.98)),
            repeating-linear-gradient(0deg, transparent, transparent 31px, rgba(148, 163, 184, 0.08) 31px, rgba(148, 163, 184, 0.08) 32px);
        padding: 18px;
        height: min(72vh, 820px);
        overflow: auto;
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.8);
    }

    .preview-empty {
        margin: 0;
        color: var(--muted);
        font-size: 14px;
    }

    .xlsx-preview-shell {
        min-width: max-content;
        background: #fff;
        border: 1px solid #d9e2ec;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 18px 36px rgba(15, 23, 42, 0.08);
    }

    .xlsx-preview-sheet {
        padding: 10px;
    }

    .xlsx-preview-shell table.excel {
        width: max-content;
        border-collapse: collapse;
        table-layout: fixed;
        font-size: 12px;
        color: #111827;
        background: #fff;
    }

    .xlsx-preview-shell table.excel th,
    .xlsx-preview-shell table.excel td {
        border: 1px solid #cbd5e1;
        padding: 4px 6px;
        vertical-align: middle;
        text-align: center;
        white-space: pre-wrap !important;
        word-break: break-word;
        line-height: 1.35;
        min-width: 56px;
    }

    .xlsx-preview-shell table.excel th {
        font-weight: 700;
        background: #fff;
    }

    .leave-table-preview-shell {
        background: #fff;
        border: 1px solid #d9e2ec;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 18px 36px rgba(15, 23, 42, 0.08);
    }

    .leave-form-top {
        border-bottom: 1px solid #dbe4f0;
        background: #fff;
    }

    .leave-form-title {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 2px;
        padding: 12px 16px 10px;
        text-align: center;
    }

    .leave-form-title strong {
        font-size: 18px;
        line-height: 1.1;
        color: #0f172a;
    }

    .leave-form-title span {
        font-size: 12px;
        color: #334155;
    }

    .leave-person-grid {
        display: flex;
        flex-direction: column;
    }

    .leave-person-row {
        display: grid;
        grid-template-columns: 90px 1.15fr 1.15fr 0.7fr 1.15fr;
    }

    .leave-person-row-birth {
        grid-template-columns: 90px 1.5fr 1.5fr 1.2fr;
    }

    .leave-person-label,
    .leave-person-cell,
    .leave-person-note {
        border-top: 1px solid #dbe4f0;
        border-right: 1px solid #dbe4f0;
        padding: 10px 12px;
        min-height: 72px;
        background: #fff;
    }

    .leave-person-row > *:last-child {
        border-right: none;
    }

    .leave-person-label {
        display: flex;
        align-items: center;
        font-weight: 800;
        color: #0f172a;
    }

    .leave-person-cell {
        display: flex;
        flex-direction: column;
        justify-content: center;
        text-align: center;
        gap: 6px;
    }

    .leave-person-cell strong {
        font-size: 15px;
        color: #0f172a;
        font-weight: 800;
        display: inline-block;
        border-bottom: 2px solid #0f172a;
        padding: 0 8px 4px;
        min-width: 120px;
        align-self: center;
    }

    .leave-person-cell span {
        font-size: 12px;
        color: #334155;
    }

    .leave-person-note {
        font-size: 10px;
        line-height: 1.35;
        color: #334155;
        display: flex;
        align-items: center;
        padding: 8px 10px;
    }

    .leave-person-note-birth {
        align-items: center;
        word-break: break-word;
        white-space: normal;
        min-height: 78px;
        padding: 8px 10px;
        font-size: 10px;
        line-height: 1.25;
    }

    .leave-period-heading {
        text-align: center !important;
        padding: 14px 16px;
        background: #eff6ff;
        color: #1e3a8a;
        border-bottom: 1px solid #dbe4f0;
        vertical-align: middle;
    }

    .leave-period-heading strong {
        display: block;
        font-size: 13px;
        font-weight: 800;
        line-height: 1.2;
    }

    .leave-period-heading span {
        display: block;
        font-size: 11px;
        line-height: 1.2;
    }

    .leave-period-spacer {
        background: #fff !important;
        border-bottom: 1px solid #dbe4f0;
    }

    .leave-table-preview {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
        color: #0f172a;
        background: #fff;
    }

    .leave-table-preview th,
    .leave-table-preview td {
        border: 1px solid #dbe4f0;
        padding: 10px 12px;
        text-align: center;
        vertical-align: middle;
        line-height: 1.45;
        word-break: break-word;
    }

    .leave-table-preview thead th {
        background: #eff6ff;
        color: #1e3a8a;
        font-weight: 800;
        text-align: center;
        vertical-align: middle;
    }

    .leave-table-preview thead th[rowspan] {
        vertical-align: middle !important;
    }

    .leave-actions-column,
    .leave-row-actions {
        width: 96px;
    }

    .leave-row-actions {
        vertical-align: middle !important;
    }

    .leave-preview-actions {
        display: flex;
        flex-direction: row;
        justify-content: center;
        align-items: center;
        gap: 8px;
        width: 100%;
        flex-wrap: nowrap;
    }

    .leave-preview-actions .table-btn.edit {
        background: #a16207;
        color: #fffbeb;
    }

    .leave-preview-actions .table-btn.edit:hover {
        background: #854d0e;
    }

    .leave-preview-actions .action-icon-btn {
        width: 34px;
        height: 34px;
        min-width: 34px;
        min-height: 34px;
        padding: 0;
        margin: 0;
        font-size: 12px;
        line-height: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .leave-footer-preview {
        display: flex;
        flex-direction: column;
        gap: 28px;
        padding: 28px 22px 22px;
        border-top: 1px solid #dbe4f0;
        background: #fff;
    }

    .leave-footer-row {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 48px;
        align-items: start;
    }

    .leave-footer-col {
        min-height: 48px;
    }

    .leave-footer-purpose,
    .leave-footer-date {
        justify-self: start;
        min-width: 260px;
    }

    .leave-footer-certify,
    .leave-footer-signature {
        justify-self: end;
        width: min(100%, 420px);
        text-align: left;
    }

    .leave-inline-pair {
        display: inline-flex;
        align-items: baseline;
        gap: 10px;
        flex-wrap: wrap;
    }

    .leave-inline-label,
    .leave-inline-value,
    .leave-certify-block strong,
    .leave-certify-block span,
    .leave-date-block strong,
    .leave-signature-block strong,
    .leave-date-block span,
    .leave-signature-block span,
    .leave-certify-block p {
        margin: 0;
        color: #0f172a;
        line-height: 1.5;
        font-size: 12px;
    }

    .leave-inline-label {
        font-weight: 700;
    }

    .leave-certify-block strong,
    .leave-signature-block strong,
    .leave-date-block strong {
        display: block;
        font-weight: 800;
        font-size: 11px;
    }

    .leave-certify-block {
        display: flex;
        flex-direction: column;
        gap: 0;
    }

    .leave-certify-block span {
        display: block;
        line-height: 1.2;
    }

    .leave-date-block {
        text-align: center;
        width: fit-content;
        min-width: 140px;
    }

    .leave-date-block strong,
    .leave-signature-block strong {
        text-decoration: underline;
        text-underline-offset: 3px;
    }

    .leave-date-block span,
    .leave-signature-block span {
        display: block;
        font-size: 11px;
    }

    .leave-signature-block {
        text-align: center;
    }

    @keyframes modalIn {
        from {
            opacity: 0;
            transform: translateY(8px) scale(0.98);
        }

        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    .footer {
        background: var(--surface);
        border-top: 1px solid var(--border);
        padding: 16px 24px;
        display: flex;
        justify-content: center;
        align-items: center;
        color: var(--muted);
        font-size: 13px;
        margin-top: auto;
        flex-shrink: 0;
        text-align: center;
    }

    @media (max-width: 1100px) {
        .stats-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .main-grid,
        .hero {
            grid-template-columns: 1fr;
            flex-direction: column;
            align-items: flex-start;
        }

        .hero-stats {
            width: 100%;
        }
    }

    @media (max-width: 860px) {
        .layout {
            grid-template-columns: 1fr;
        }

        .sidebar {
            gap: 16px;
            position: static;
            top: auto;
            height: auto;
            min-height: 100vh;
            overflow-y: visible;
        }
    }

    @media (max-width: 640px) {
        .navbar,
        .footer {
            flex-direction: column;
            gap: 10px;
            height: auto;
            text-align: center;
            padding: 16px;
        }

        .hero h2 {
            font-size: 24px;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }

        .content-body {
            padding: 0 18px 18px;
        }

        .toolbar {
            align-items: flex-start;
        }

        .file-picker {
            align-items: flex-start;
            flex-direction: column;
        }

        .file-picker-action {
            width: 100%;
        }

        .form-grid {
            grid-template-columns: 1fr;
        }

        .image-preview-header {
            flex-direction: column;
        }

        .image-preview-card {
            width: min(96vw, 1180px);
        }

        .section-header-actions {
            width: 100%;
            margin-left: 0;
        }

        .table-search-field {
            width: 100%;
        }

        .table-search-field input {
            width: 100%;
        }

        .image-preview-frame {
            margin: 14px;
            height: 68vh;
            padding: 12px;
        }

        .employee-modal-hero {
            flex-direction: column;
        }

        .confirm-delete-hero {
            flex-direction: column;
        }

        .confirm-delete-actions {
            justify-content: stretch;
        }

        .confirm-delete-actions .btn {
            width: 100%;
        }

        .employee-form-section {
            margin: 0 18px;
        }

        .leave-modal-card {
            width: min(96vw, 760px);
            max-height: 92vh;
        }

        .leave-entry-form .form-grid {
            grid-template-columns: 1fr;
        }

        .leave-entry-form .employee-form-section {
            margin: 0 18px;
        }

        .leave-entry-form .modal-actions {
            margin: 0 18px 18px;
        }
    }
</style>
</head>
