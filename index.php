<?php
session_start();
require_once 'config/database.php';

function getHomepageStats($pdo) {
    $queries = [
        'active_residents' => "
            SELECT COUNT(DISTINCT u.user_id)
            FROM users u
            JOIN user_role_assignments ura ON ura.user_id = u.user_id AND ura.is_active = 1
            JOIN roles r ON r.role_id = ura.role_id
            WHERE r.role_name = 'resident' AND u.is_active = 1
        ",
        'document_requests' => "SELECT COUNT(*) FROM document_requests",
        'open_complaints' => "SELECT COUNT(*) FROM complaints WHERE status IN ('submitted', 'in_progress')",
        'appointments' => "SELECT COUNT(*) FROM appointments"
    ];

    $stats = [];
    foreach ($queries as $key => $sql) {
        try {
            $statement = $pdo->query($sql);
            $stats[$key] = (int) ($statement ? $statement->fetchColumn() : 0);
        } catch (Throwable $e) {
            $stats[$key] = 0;
        }
    }

    return $stats;
}

$homepageStats = getHomepageStats($pdo);

// Check if already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_type'] == 'resident') {
        header("Location: resident/dashboard.php");
    } else {
        header("Location: admin/dashboard.php");
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#2563eb">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="MyBalai">
    <meta name="mobile-web-app-capable" content="yes">
    <title>MyBalai - Smart Barangay Services Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="assets/css/app.css" rel="stylesheet">
    <link rel="manifest" href="manifest.json?v=20260727">
    <link rel="apple-touch-icon" href="assets/icons/appicon.png">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');

        * {
            font-family: 'Poppins', sans-serif;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
            overflow-x: hidden;
        }

        body {
            overflow-x: hidden;
            background: radial-gradient(circle at top left, rgba(99, 102, 241, 0.13), transparent 30%), linear-gradient(180deg, #f8fbff 0%, #eef4ff 100%);
            color: #0f172a;
        }

        body.mobile-menu-open {
            overflow: hidden;
            touch-action: none;
            position: relative;
            width: 100%;
        }

        section {
            scroll-margin-top: 96px;
        }

        .hero-gradient {
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 40%, #7c3aed 100%);
        }

        .hero-gradient::before,
        .hero-gradient::after {
            content: '';
            position: absolute;
            border-radius: 999px;
            filter: blur(8px);
            opacity: 0.32;
            pointer-events: none;
        }

        .hero-gradient::before {
            width: 320px;
            height: 320px;
            background: rgba(255, 255, 255, 0.16);
            top: -140px;
            right: -110px;
        }

        .hero-gradient::after {
            width: 240px;
            height: 240px;
            background: rgba(255, 255, 255, 0.12);
            bottom: -120px;
            left: -80px;
        }

        .hero-shell {
            position: relative;
            z-index: 1;
            padding: 1.4rem;
            border-radius: 32px;
            border: 1px solid rgba(255, 255, 255, 0.24);
            background: rgba(255, 255, 255, 0.12);
            box-shadow: 0 24px 70px rgba(15, 23, 42, 0.22);
            backdrop-filter: blur(18px);
        }

        .hero-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            padding: 0.55rem 0.9rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.18);
            border: 1px solid rgba(255, 255, 255, 0.24);
            font-size: 0.9rem;
            font-weight: 600;
            color: #f8fafc;
            margin-bottom: 1rem;
        }

        .hero-visual-card {
            position: relative;
            width: 100%;
            border-radius: 28px;
            background: rgba(248, 250, 252, 0.9);
            padding: 1rem;
            box-shadow: 0 20px 50px rgba(15, 23, 42, 0.16);
        }

        .hero-visual-badge {
            position: absolute;
            left: 1rem;
            bottom: 1rem;
            padding: 0.55rem 0.85rem;
            border-radius: 999px;
            background: linear-gradient(135deg, #1d4ed8, #2563eb);
            color: #fff;
            font-size: 0.8rem;
            font-weight: 700;
            box-shadow: 0 10px 24px rgba(29, 78, 216, 0.25);
        }

        .card-hover {
            transition: all 0.3s ease;
        }

        .card-hover:hover {
            transform: translateY(-8px);
            box-shadow: 0 24px 48px rgba(15, 23, 42, 0.12);
        }

        .service-icon {
            transition: all 0.3s ease;
        }

        .service-card {
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(226, 232, 240, 0.95);
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.96);
            box-shadow: 0 16px 40px rgba(15, 23, 42, 0.07);
        }

        .service-card::before {
            content: '';
            position: absolute;
            inset: 0 auto auto 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, #2563eb, #8b5cf6);
        }

        .service-card:hover .service-icon {
            transform: scale(1.08);
        }

        .section-title {
            font-size: clamp(1.8rem, 2.3vw, 2.6rem);
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 0.75rem;
        }

        .section-subtitle {
            font-size: 1rem;
            color: #475569;
            line-height: 1.7;
            max-width: 710px;
            margin: 0 auto;
        }

        .stat-card {
            border: 1px solid rgba(226, 232, 240, 0.9);
            border-radius: 22px;
            background: rgba(255, 255, 255, 0.9);
            box-shadow: 0 16px 36px rgba(15, 23, 42, 0.06);
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 44px rgba(15, 23, 42, 0.10);
        }

        .feature-card {
            border: 1px solid rgba(226, 232, 240, 0.95);
            border-radius: 24px;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.98), rgba(248, 250, 252, 0.96));
            box-shadow: 0 16px 36px rgba(15, 23, 42, 0.05);
            padding: 1.2rem;
        }

        .feature-icon {
            width: 3rem;
            height: 3rem;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            margin-bottom: 1rem;
        }

        .cta-card {
            border-radius: 32px;
            background: linear-gradient(135deg, rgba(29, 78, 216, 0.95), rgba(124, 58, 237, 0.95));
            box-shadow: 0 24px 60px rgba(29, 78, 216, 0.24);
        }

        .site-header {
            background: rgba(255, 255, 255, 0.92);
            border-bottom: 1px solid rgba(219, 228, 239, 0.9);
            backdrop-filter: blur(16px);
        }

        .brand-mark {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, #1e40af, #0f766e);
            box-shadow: 0 10px 22px rgba(37, 99, 235, 0.22);
        }

        .nav-link {
            color: #475569;
            font-weight: 600;
            padding: 0.625rem 0.85rem;
            border-radius: 999px;
        }

        .nav-link:hover {
            color: #1e40af;
            background: #eff6ff;
        }

        .header-login {
            background: linear-gradient(135deg, #1e40af, #0f766e);
            box-shadow: 0 12px 24px rgba(15, 118, 110, 0.2);
        }

        .header-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 16px 30px rgba(15, 118, 110, 0.28);
        }

        .mobile-toggle-anchor {
            width: 46px;
            height: 46px;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.96);
            border: 1px solid rgba(226, 232, 240, 0.95);
            box-shadow: 0 10px 22px rgba(15, 23, 42, 0.08);
            backdrop-filter: blur(14px);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            flex-shrink: 0;
        }

        .mobile-toggle-anchor:hover {
            box-shadow: 0 12px 22px rgba(15, 23, 42, 0.12);
        }

        .mobile-toggle-anchor.is-open {
            background: linear-gradient(135deg, #1e40af, #0f766e);
            border-color: transparent;
            color: #fff;
            box-shadow: 0 14px 26px rgba(30, 64, 175, 0.24);
        }

        .header-cta-group {
            margin-left: auto;
            flex-shrink: 0;
        }

        .mobile-panel {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            width: 100%;
            display: block;
            max-height: 0;
            opacity: 0;
            overflow: hidden;
            transform: translateY(-8px);
            pointer-events: none;
            transition: max-height 0.28s ease, opacity 0.2s ease, transform 0.2s ease;
            z-index: 45;
        }

        .mobile-panel.is-open {
            max-height: 640px;
            opacity: 1;
            transform: translateY(0);
            pointer-events: auto;
        }

        .stats-number {
            animation: countUp 2s ease-out;
        }

        @keyframes countUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .site-header .container { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; }

        @media (min-width: 1024px) {
            .hero-shell {
                display: grid;
                grid-template-columns: 1.08fr 0.92fr;
                gap: 2rem;
                align-items: center;
                padding: 2.2rem 2.3rem;
            }

            .hero-copy {
                max-width: 620px;
            }

            .hero-visual {
                display: flex;
                justify-content: center;
            }

            .hero-visual-card {
                padding: 1.2rem;
            }

            .hero-visual-card img {
                max-width: 420px;
                width: 100%;
                margin: 0 auto;
                filter: drop-shadow(0 20px 35px rgba(15, 23, 42, 0.18));
            }

            .service-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .feature-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .site-header { padding-top: 8px; padding-bottom: 8px; }
            .site-header .container { align-items: center; position: relative; }
            .header-cta-group {
                position: absolute;
                right: 1rem;
                top: 50%;
                transform: translateY(-50%);
                z-index: 90;
                display: flex;
                align-items: center;
                gap: 0.75rem;
                margin-left: 0;
            }
            .header-cta-group a { height: 44px; display: inline-flex; align-items: center; gap: 0.5rem; padding-left: 0.9rem; padding-right: 0.9rem; }
            .header-login { padding-left: 1rem; padding-right: 1rem; height: 44px; }
            .brand-mark { margin-right: 0.5rem; }
            .nav-links { position: absolute; left: 50%; transform: translateX(-50%); display: flex; gap: 1rem; }
        }

        @media (max-width: 1023px) {
            .hero-shell {
                display: flex;
                flex-direction: column;
                gap: 1rem;
                padding: 1.35rem;
            }

            .hero-copy {
                width: 100%;
            }

            .hero-visual-card img {
                max-width: 320px;
                width: 100%;
                margin: 0 auto;
            }
        }

        @media (max-width: 768px) {
            body { padding-top: 76px; }
            .site-header { position: fixed; top: 0; left: 0; right: 0; width: 100%; z-index: 60; padding-top: 8px; padding-bottom: 8px; box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08); }
            .site-header .container { position: relative; display: flex; align-items: center; justify-content: space-between; flex-wrap: nowrap; gap: 0.6rem; padding-right: 0; }
            .site-header .container > div { padding-top: 0; padding-bottom: 0; }
            .site-header .container > a { min-width: 0; flex: 1 1 auto; max-width: calc(100% - 58px); gap: 0.65rem; overflow: hidden; }
            .site-header .container > a > span { min-width: 0; }
            .site-header .container > a > span:last-child { min-width: 0; overflow: hidden; }
            .mobile-toggle-anchor { position: absolute; right: 0.25rem; top: 50%; transform: translateY(-50%); margin-left: 0; flex: 0 0 46px; width: 46px; height: 46px; border-radius: 14px; z-index: 90; }
            .mobile-toggle-anchor:hover { transform: translateY(-1px); }
            .mobile-toggle-anchor i { display: inline-flex; align-items: center; justify-content: center; width: 1em; line-height: 1; font-size: 16px; }
            .brand-mark { width: 38px; height: 38px; }
            .brand-mark i { font-size: 17px; }
            .site-header .container > a > span:last-child > span:first-child { font-size: 0.92rem; line-height: 1.05; }
            .site-header .container > a > span:last-child > span:last-child { display: none; }
            .header-cta-group { margin-left: 0; }
            .hero-gradient { padding-top: 22px; padding-bottom: 22px; }
            .hero-gradient h1 { font-size: 1.9rem; line-height: 1.08; letter-spacing: -0.02em; }
            .hero-gradient p { font-size: 0.98rem; line-height: 1.65; opacity: 0.95; }
            .hero-actions { flex-direction: column; gap: 0.75rem; }
            .hero-actions a { width: 100%; justify-content: center; border-radius: 16px; padding-top: 0.9rem; padding-bottom: 0.9rem; }
            .container { padding-left: 1rem; padding-right: 1rem; }
            .service-card { padding: 1rem; border-radius: 20px; }
            .service-card h3 { font-size: 1.05rem; }
            .service-card p, .service-card li { font-size: 0.94rem; line-height: 1.55; }
            .service-icon { width: 48px; height: 48px; border-radius: 16px; }
            .service-icon i { font-size: 18px; }
            .stats-number { font-size: 1.6rem; }
            .stats-number + div { font-size: 0.92rem; }
            .stat-card { padding: 1rem; }
            .feature-card { padding: 1rem; }
            .service-grid,
            .feature-grid,
            footer .grid,
            .bg-white.py-12 .grid { grid-template-columns: 1fr; }
            .mobile-panel { width: 100%; }
            .mobile-panel .rounded-2xl {
                width: 100%;
                border-radius: 0 0 22px 22px;
                background: rgba(255, 255, 255, 0.98);
                box-shadow: 0 18px 40px rgba(15, 23, 42, 0.12);
                overflow: hidden;
            }
            .mobile-panel .grid.gap-2 { padding-bottom: 0.25rem; }
            .mobile-panel .grid.gap-2 .mobile-menu-link {
                display: flex;
                align-items: center;
                gap: 0.75rem;
                border-radius: 14px;
                background: #f8fafc;
                border: 1px solid #e2e8f0;
                color: #0f172a;
                font-weight: 600;
                padding: 0.9rem 1rem;
                transition: background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
            }
            .mobile-panel .grid.gap-2 .mobile-menu-link:hover {
                background: #eff6ff;
                border-color: #bfdbfe;
                color: #1d4ed8;
                transform: translateX(2px);
            }
            .mobile-panel .grid.gap-2 .mobile-menu-link.is-active {
                background: linear-gradient(135deg, #1e40af, #0f766e);
                border-color: transparent;
                color: #fff;
                box-shadow: 0 14px 28px rgba(30, 64, 175, 0.18);
            }
            .mobile-panel .grid.gap-2 .mobile-menu-link .menu-link-icon {
                width: 2rem;
                height: 2rem;
                flex: 0 0 2rem;
                border-radius: 12px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                background: #eff6ff;
                color: #1d4ed8;
                transition: inherit;
            }
            .mobile-panel .grid.gap-2 .mobile-menu-link.is-active .menu-link-icon {
                background: rgba(255, 255, 255, 0.18);
                color: #fff;
            }
            .mobile-panel .grid.gap-2 .mobile-menu-link .menu-link-label {
                flex: 1 1 auto;
            }
            .mobile-panel .mt-3 a {
                border-radius: 14px;
            }
            .nav-link { display: none; }
            .mobile-menu-link { display: flex !important; }
            .header-cta-group { display: none !important; }
            .header-login { padding: 0.75rem 0.9rem; }
            .hero-gradient img { max-width: 220px; width: 100%; margin-top: 0.5rem; filter: drop-shadow(0 18px 24px rgba(15, 23, 42, 0.14)); }
            .section-title { font-size: 1.7rem; }
            .section-subtitle { font-size: 0.95rem; }
        }

        @media (max-width: 420px) {
            .site-header .container { align-items: center; justify-content: space-between; flex-wrap: nowrap; gap: 0.35rem; padding-right: 0.35rem; }
            .site-header .container > a { min-width: 0; flex: 1 1 auto; max-width: calc(100% - 54px); }
            .brand-mark { width: 36px; height: 36px; }
            .brand-mark i { font-size: 16px; }
            .site-header .container > a > span:last-child > span:first-child { font-size: 0.88rem; line-height: 1.05; }
            .mobile-toggle-anchor { position: absolute; right: 0.2rem; top: 50%; transform: translateY(-50%); margin-left: 0; flex: 0 0 42px; width: 42px; height: 42px; border-radius: 12px; z-index: 90; }
            .mobile-toggle-anchor:hover { transform: translateY(-1px); }
            .mobile-toggle-anchor i { font-size: 16px; }
            .hero-gradient { padding-top: 20px; padding-bottom: 20px; }
            .hero-gradient h1 { font-size: 1.6rem; }
            .hero-gradient p { font-size: 0.92rem; }
            .service-card { padding: 0.85rem; }
            .service-icon { width: 44px; height: 44px; }
            .stats-number { font-size: 1.35rem; }
            .bg-white.py-12 { padding-top: 2.25rem; padding-bottom: 2.25rem; }
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="site-header fixed w-full z-50">
        <div class="container mx-auto px-4 sm:px-6">
            <div class="flex flex-nowrap items-center justify-between gap-3 py-4">
                <a href="#home" class="flex min-w-0 items-center gap-3" aria-label="MyBalai home">
                    <span class="brand-mark flex shrink-0 items-center justify-center rounded-lg text-white">
                        <i class="fas fa-home text-xl"></i>
                    </span>
                    <span class="min-w-0">
                        <span class="block text-base font-bold leading-tight tracking-tight text-slate-900 sm:text-xl">MyBalai</span>
                        <span class="hidden text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500 sm:block">Smart Barangay Services</span>
                    </span>
                </a>

                <div class="hidden items-center gap-1 lg:flex nav-links">
                    <a href="#home" class="nav-link">Home</a>
                    <a href="#services" class="nav-link">Services</a>
                    <a href="#features" class="nav-link">Features</a>
                    <a href="#about" class="nav-link">About</a>
                    <a href="#contact" class="nav-link">Contact</a>
                </div>

                <button type="button" id="mobileMenuButton" class="mobile-toggle-anchor md:hidden" aria-label="Open menu" aria-expanded="false">
                    <i class="fas fa-bars"></i>
                </button>

                <div class="header-cta-group hidden items-center gap-3 md:flex">
                    <a href="register.php" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-3 font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">
                        <i class="fas fa-user-plus"></i>
                        <span>Create Account</span>
                    </a>
                    <a href="login.php" class="header-login inline-flex items-center gap-2 rounded-lg px-5 py-3 font-semibold text-white">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Login</span>
                    </a>
                </div>
            </div>
        </div>

        <div id="mobileMenu" class="mobile-panel pb-4 md:hidden">
            <div class="rounded-2xl border border-slate-200 bg-white p-3 shadow-lg">
                <div class="grid gap-2">
                        <a href="#home" class="nav-link mobile-menu-link">
                            <span class="menu-link-icon"><i class="fas fa-house"></i></span>
                            <span class="menu-link-label">Home</span>
                        </a>
                        <a href="#services" class="nav-link mobile-menu-link">
                            <span class="menu-link-icon"><i class="fas fa-grid-2"></i></span>
                            <span class="menu-link-label">Services</span>
                        </a>
                        <a href="#features" class="nav-link mobile-menu-link">
                            <span class="menu-link-icon"><i class="fas fa-star"></i></span>
                            <span class="menu-link-label">Features</span>
                        </a>
                        <a href="#about" class="nav-link mobile-menu-link">
                            <span class="menu-link-icon"><i class="fas fa-circle-info"></i></span>
                            <span class="menu-link-label">About</span>
                        </a>
                        <a href="#contact" class="nav-link mobile-menu-link">
                            <span class="menu-link-icon"><i class="fas fa-phone"></i></span>
                            <span class="menu-link-label">Contact</span>
                        </a>
                    </div>
                    <div class="mt-3 grid gap-2">
                        <a href="login.php" class="header-login flex items-center justify-center gap-2 rounded-lg px-5 py-3 font-semibold text-white">
                            <i class="fas fa-sign-in-alt"></i>
                            <span>Login</span>
                        </a>
                        <a href="register.php" class="flex items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-5 py-3 font-semibold text-slate-700">
                            <i class="fas fa-user-plus"></i>
                            <span>Create Resident Account</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero-gradient text-white pt-28 pb-20 sm:pt-36">
        <div class="container mx-auto px-6">
            <div class="hero-shell">
                <div class="hero-copy" data-aos="fade-right">
                    <div class="hero-pill">
                        <i class="fas fa-shield-alt"></i>
                        <span>Official barangay services, digitized</span>
                    </div>
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold leading-tight mb-5">
                        Smart Barangay
                        <span class="block text-yellow-300">Services Portal</span>
                    </h1>
                    <p class="text-lg sm:text-xl mb-8 text-slate-100/95">
                        Request documents, file complaints, book appointments, and stay connected with your community in one secure digital hub.
                    </p>
                    <div class="hero-actions flex flex-col sm:flex-row gap-3">
                        <a href="register.php" class="bg-white text-indigo-600 px-8 py-3 rounded-xl font-semibold hover:shadow-xl transition transform hover:scale-105 inline-flex items-center justify-center gap-2">
                            Create Account <i class="fas fa-arrow-right"></i>
                        </a>
                        <a href="#services" class="border-2 border-white px-8 py-3 rounded-xl font-semibold hover:bg-white hover:text-indigo-600 transition inline-flex items-center justify-center gap-2">
                            Explore Services
                        </a>
                    </div>
                </div>
                <div class="hero-visual" data-aos="fade-left">
                    <div class="hero-visual-card">
                        <img src="https://cdn-icons-png.flaticon.com/512/6191/6191682.png" alt="Hero Image" class="w-full max-w-md mx-auto">
                        <div class="hero-visual-badge">Available 24/7</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="py-12">
        <div class="container mx-auto px-6">
            <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-3 mb-8">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full bg-indigo-100 px-3 py-1 text-sm font-semibold text-indigo-700 mb-3">
                        <i class="fas fa-chart-line"></i>
                        Community overview
                    </div>
                    <h2 class="section-title mb-2">Barangay activity at a glance</h2>
                </div>
                <p class="text-slate-600 max-w-xl">Live updates on how residents are using the platform to access services and stay informed.</p>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
                <div class="stat-card p-5 text-center" data-aos="fade-up">
                    <div class="text-3xl md:text-4xl font-bold text-indigo-600 stats-number" data-stat="active_residents"><?php echo number_format($homepageStats['active_residents']); ?></div>
                    <div class="text-gray-600 mt-2">Active Residents</div>
                </div>
                <div class="stat-card p-5 text-center" data-aos="fade-up" data-aos-delay="100">
                    <div class="text-3xl md:text-4xl font-bold text-indigo-600 stats-number" data-stat="document_requests"><?php echo number_format($homepageStats['document_requests']); ?></div>
                    <div class="text-gray-600 mt-2">Document Requests</div>
                </div>
                <div class="stat-card p-5 text-center" data-aos="fade-up" data-aos-delay="200">
                    <div class="text-3xl md:text-4xl font-bold text-indigo-600 stats-number" data-stat="open_complaints"><?php echo number_format($homepageStats['open_complaints']); ?></div>
                    <div class="text-gray-600 mt-2">Open Complaints</div>
                </div>
                <div class="stat-card p-5 text-center" data-aos="fade-up" data-aos-delay="300">
                    <div class="text-3xl md:text-4xl font-bold text-indigo-600 stats-number" data-stat="appointments"><?php echo number_format($homepageStats['appointments']); ?></div>
                    <div class="text-gray-600 mt-2">Appointments</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="py-20 bg-gray-50/70">
        <div class="container mx-auto px-6">
            <div class="text-center mb-12" data-aos="fade-up">
                <div class="inline-flex items-center gap-2 rounded-full bg-emerald-100 px-3 py-1 text-sm font-semibold text-emerald-700 mb-3">
                    <i class="fas fa-bolt"></i>
                    Modern barangay support
                </div>
                <h2 class="section-title mb-4">Everything you need, simplified</h2>
                <p class="section-subtitle">Access barangay services anytime, anywhere with a faster and more organized experience.</p>
            </div>
            <div class="service-grid grid gap-8">
                <!-- Service 1 -->
                <div class="bg-white rounded-xl shadow-lg p-8 card-hover service-card" data-aos="fade-up">
                    <div class="service-icon bg-gradient-to-r from-blue-500 to-indigo-600 w-16 h-16 rounded-lg flex items-center justify-center mb-6">
                        <i class="fas fa-file-alt text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-3">Document Requests</h3>
                    <p class="text-gray-600 mb-4">Request Barangay Clearance, Certificate of Residency, and other documents online.</p>
                    <ul class="text-sm text-gray-500 space-y-2">
                        <li><i class="fas fa-check-circle text-green-500 mr-2"></i>Track request status</li>
                        <li><i class="fas fa-check-circle text-green-500 mr-2"></i>QR code verification</li>
                        <li><i class="fas fa-check-circle text-green-500 mr-2"></i>Pickup notifications</li>
                    </ul>
                </div>

                <!-- Service 2 -->
                <div class="bg-white rounded-xl shadow-lg p-8 card-hover service-card" data-aos="fade-up" data-aos-delay="100">
                    <div class="service-icon bg-gradient-to-r from-purple-500 to-pink-600 w-16 h-16 rounded-lg flex items-center justify-center mb-6">
                        <i class="fas fa-gavel text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-3">Complaints & Blotter</h3>
                    <p class="text-gray-600 mb-4">File complaints online and track resolution progress.</p>
                    <ul class="text-sm text-gray-500 space-y-2">
                        <li><i class="fas fa-check-circle text-green-500 mr-2"></i>Anonymous reporting</li>
                        <li><i class="fas fa-check-circle text-green-500 mr-2"></i>Real-time updates</li>
                        <li><i class="fas fa-check-circle text-green-500 mr-2"></i>Mediation scheduling</li>
                    </ul>
                </div>

                <!-- Service 3 -->
                <div class="bg-white rounded-xl shadow-lg p-8 card-hover service-card" data-aos="fade-up" data-aos-delay="200">
                    <div class="service-icon bg-gradient-to-r from-green-500 to-teal-600 w-16 h-16 rounded-lg flex items-center justify-center mb-6">
                        <i class="fas fa-calendar-check text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-3">Appointment Booking</h3>
                    <p class="text-gray-600 mb-4">Schedule appointments with barangay officials online.</p>
                    <ul class="text-sm text-gray-500 space-y-2">
                        <li><i class="fas fa-check-circle text-green-500 mr-2"></i>Choose preferred date/time</li>
                        <li><i class="fas fa-check-circle text-green-500 mr-2"></i>Automatic reminders</li>
                        <li><i class="fas fa-check-circle text-green-500 mr-2"></i>Reschedule options</li>
                    </ul>
                </div>

                <!-- Service 4 -->
                <div class="bg-white rounded-xl shadow-lg p-8 card-hover service-card" data-aos="fade-up" data-aos-delay="300">
                    <div class="service-icon bg-gradient-to-r from-yellow-500 to-orange-600 w-16 h-16 rounded-lg flex items-center justify-center mb-6">
                        <i class="fas fa-bullhorn text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-3">Announcements</h3>
                    <p class="text-gray-600 mb-4">Stay updated with barangay news and announcements.</p>
                    <ul class="text-sm text-gray-500 space-y-2">
                        <li><i class="fas fa-check-circle text-green-500 mr-2"></i>Real-time notifications</li>
                        <li><i class="fas fa-check-circle text-green-500 mr-2"></i>Emergency alerts</li>
                        <li><i class="fas fa-check-circle text-green-500 mr-2"></i>Event schedules</li>
                    </ul>
                </div>

                <!-- Service 5 -->
                <div class="bg-white rounded-xl shadow-lg p-8 card-hover service-card" data-aos="fade-up" data-aos-delay="400">
                    <div class="service-icon bg-gradient-to-r from-red-500 to-pink-600 w-16 h-16 rounded-lg flex items-center justify-center mb-6">
                        <i class="fas fa-users text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-3">Household Profiling</h3>
                    <p class="text-gray-600 mb-4">Update your household information for better services.</p>
                    <ul class="text-sm text-gray-500 space-y-2">
                        <li><i class="fas fa-check-circle text-green-500 mr-2"></i>Family member records</li>
                        <li><i class="fas fa-check-circle text-green-500 mr-2"></i>Demographic data</li>
                        <li><i class="fas fa-check-circle text-green-500 mr-2"></i>Special needs tagging</li>
                    </ul>
                </div>

                <!-- Service 6 -->
                <div class="bg-white rounded-xl shadow-lg p-8 card-hover service-card" data-aos="fade-up" data-aos-delay="500">
                    <div class="service-icon bg-gradient-to-r from-indigo-500 to-purple-600 w-16 h-16 rounded-lg flex items-center justify-center mb-6">
                        <i class="fas fa-qrcode text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-3">QR Resident ID</h3>
                    <p class="text-gray-600 mb-4">Digital ID with QR code for easy verification.</p>
                    <ul class="text-sm text-gray-500 space-y-2">
                        <li><i class="fas fa-check-circle text-green-500 mr-2"></i>Secure digital identity</li>
                        <li><i class="fas fa-check-circle text-green-500 mr-2"></i>Quick verification</li>
                        <li><i class="fas fa-check-circle text-green-500 mr-2"></i>Contactless transactions</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 bg-white">
        <div class="container mx-auto px-6">
            <div class="text-center mb-12" data-aos="fade-up">
                <div class="inline-flex items-center gap-2 rounded-full bg-violet-100 px-3 py-1 text-sm font-semibold text-violet-700 mb-3">
                    <i class="fas fa-star"></i>
                    Why residents love it
                </div>
                <h2 class="section-title mb-4">Why Choose MyBalai?</h2>
                <p class="section-subtitle">Revolutionizing barangay services through thoughtful design, speed, and reliability.</p>
            </div>
            <div class="feature-grid grid gap-6">
                <div class="feature-card" data-aos="fade-right">
                    <div class="feature-icon bg-green-500">
                        <i class="fas fa-clock text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">24/7 Accessibility</h3>
                    <p class="text-gray-600">Access barangay services anytime, anywhere without going through long queues or waiting in person.</p>
                </div>
                <div class="feature-card" data-aos="fade-left">
                    <div class="feature-icon bg-blue-500">
                        <i class="fas fa-chart-line text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Real-time Tracking</h3>
                    <p class="text-gray-600">Track your document requests and complaints status in real-time so you always know what’s next.</p>
                </div>
                <div class="feature-card" data-aos="fade-right" data-aos-delay="100">
                    <div class="feature-icon bg-purple-500">
                        <i class="fas fa-shield-alt text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Secure & Verified</h3>
                    <p class="text-gray-600">QR code verification and profile checks keep every transaction authentic and protected.</p>
                </div>
                <div class="feature-card" data-aos="fade-left" data-aos-delay="100">
                    <div class="feature-icon bg-yellow-500">
                        <i class="fas fa-bell text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Instant Notifications</h3>
                    <p class="text-gray-600">Receive updates and reminders quickly so you never miss a request, appointment, or important announcement.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section id="about" class="py-20">
        <div class="container mx-auto px-6" data-aos="zoom-in">
            <div class="cta-card px-8 py-12 text-center text-white">
                <h2 class="text-3xl sm:text-4xl font-bold mb-4">Ready to experience smart barangay services?</h2>
                <p class="text-lg text-white/90 mb-8 max-w-2xl mx-auto">Join the growing number of residents who already enjoy convenient online services with a smoother, faster experience.</p>
                <a href="login.php" class="bg-white text-indigo-600 px-8 py-4 rounded-xl font-semibold text-lg hover:shadow-xl transition inline-flex items-center gap-2">
                    Get Started Now <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer id="contact" class="bg-slate-950 text-white py-12">
        <div class="container mx-auto px-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <div class="flex items-center space-x-2 mb-4">
                        <i class="fas fa-home text-2xl text-indigo-400"></i>
                        <span class="text-xl font-bold">MyBalai</span>
                    </div>
                    <p class="text-gray-400">Smart Barangay Services Portal making government services accessible to everyone.</p>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Quick Links</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#home" class="hover:text-white transition">Home</a></li>
                        <li><a href="#services" class="hover:text-white transition">Services</a></li>
                        <li><a href="#features" class="hover:text-white transition">Features</a></li>
                        <li><a href="#about" class="hover:text-white transition">About</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Contact Info</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><i class="fas fa-map-marker-alt mr-2"></i> Barangay Alejawan Lutao, Duero, Bohol</li>
                        <li><i class="fas fa-phone mr-2"></i> 09944462851</li>
                        <li><i class="fas fa-envelope mr-2"></i> info@mybalai.com</li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Follow Us</h4>
                    <div class="flex space-x-4">
                        <a href="#" class="bg-gray-800 w-10 h-10 rounded-full flex items-center justify-center hover:bg-indigo-600 transition">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="bg-gray-800 w-10 h-10 rounded-full flex items-center justify-center hover:bg-indigo-600 transition">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="bg-gray-800 w-10 h-10 rounded-full flex items-center justify-center hover:bg-indigo-600 transition">
                            <i class="fab fa-instagram"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; 2026 MyBalai. All rights reserved.</p>
                <p>Developer: EARL O. GULTIA</p>
            </div>
        </div>
    </footer>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 1000,
            once: true
        });

        // Smooth scrolling
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            });
        });

        const mobileMenuButton = document.getElementById('mobileMenuButton');
        const mobileMenu = document.getElementById('mobileMenu');
        const mobileMenuIcon = mobileMenuButton?.querySelector('i');

        mobileMenuButton?.addEventListener('click', () => {
            const isOpen = mobileMenu.classList.toggle('is-open');
            mobileMenuButton.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            mobileMenuButton.classList.toggle('is-open', isOpen);
            mobileMenuIcon.className = isOpen ? 'fas fa-times' : 'fas fa-bars';
            document.body.classList.toggle('mobile-menu-open', isOpen);
        });

        document.querySelectorAll('.mobile-menu-link').forEach(link => {
            link.addEventListener('click', () => {
                setActiveMobileLink(link.getAttribute('href'));
                mobileMenu.classList.remove('is-open');
                mobileMenuButton?.setAttribute('aria-expanded', 'false');
                mobileMenuButton?.classList.remove('is-open');
                document.body.classList.remove('mobile-menu-open');
                if (mobileMenuIcon) {
                    mobileMenuIcon.className = 'fas fa-bars';
                }
            });
        });

        const mobileMenuLinks = Array.from(document.querySelectorAll('.mobile-menu-link'));
        const sectionIds = mobileMenuLinks
            .map(link => link.getAttribute('href'))
            .filter(href => href && href.startsWith('#'))
            .map(href => href.slice(1));

        const setActiveMobileLink = (href) => {
            mobileMenuLinks.forEach(link => {
                const isActive = link.getAttribute('href') === href;
                link.classList.toggle('is-active', isActive);
                link.setAttribute('aria-current', isActive ? 'page' : 'false');
            });
        };

        const updateActiveMobileLink = () => {
            const headerOffset = 120;
            let currentHref = '#home';

            sectionIds.forEach(id => {
                const section = document.getElementById(id);
                if (!section) return;
                const rect = section.getBoundingClientRect();
                if (rect.top - headerOffset <= 0) {
                    currentHref = `#${id}`;
                }
            });

            setActiveMobileLink(currentHref);
        };

        updateActiveMobileLink();
        window.addEventListener('scroll', () => {
            window.requestAnimationFrame(updateActiveMobileLink);
        }, { passive: true });

        document.addEventListener('click', (event) => {
            if (!mobileMenu.classList.contains('is-open')) return;
            const clickedInsideHeader = event.target.closest('.site-header');
            if (!clickedInsideHeader) {
                mobileMenu.classList.remove('is-open');
                mobileMenuButton?.setAttribute('aria-expanded', 'false');
                mobileMenuButton?.classList.remove('is-open');
                document.body.classList.remove('mobile-menu-open');
                if (mobileMenuIcon) {
                    mobileMenuIcon.className = 'fas fa-bars';
                }
            }
        });

        // Stats animation
        const stats = document.querySelectorAll('.stats-number');
        const animateStats = (entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.animation = 'countUp 1s ease-out';
                    observer.unobserve(entry.target);
                }
            });
        };
        
        const statsObserver = new IntersectionObserver(animateStats);
        stats.forEach(stat => statsObserver.observe(stat));

        const formatter = new Intl.NumberFormat();
        const refreshStats = async () => {
            try {
                const response = await fetch('api/homepage_stats.php', {
                    headers: { 'Accept': 'application/json' },
                    cache: 'no-store'
                });

                if (!response.ok) return;
                const data = await response.json();

                document.querySelectorAll('[data-stat]').forEach(element => {
                    const key = element.dataset.stat;
                    if (Object.prototype.hasOwnProperty.call(data, key)) {
                        element.textContent = formatter.format(Number(data[key]) || 0);
                    }
                });
            } catch (error) {
                console.warn('Stats refresh failed', error);
            }
        };

        setInterval(refreshStats, 10000);
    </script>
    <script src="assets/js/pwa.js?v=20260727"></script>
</body>
</html>
