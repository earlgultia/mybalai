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
        $stats[$key] = (int) $pdo->query($sql)->fetchColumn();
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyBalai - Smart Barangay Services Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="assets/css/app.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');
        * {
            font-family: 'Poppins', sans-serif;
        }
        .hero-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        .service-icon {
            transition: all 0.3s ease;
        }
        .service-card:hover .service-icon {
            transform: scale(1.1);
        }
        .btn-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: all 0.3s ease;
        }
        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102,126,234,0.3);
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
            transition: transform 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease;
        }
        .mobile-toggle-anchor:hover {
            transform: translateY(-1px);
            box-shadow: 0 14px 24px rgba(15, 23, 42, 0.12);
        }
        .mobile-toggle-anchor {
            width: 52px;
            height: 52px;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid rgba(226, 232, 240, 0.9);
            box-shadow: 0 12px 26px rgba(15, 23, 42, 0.10);
            backdrop-filter: blur(14px);
        }
        .mobile-toggle-anchor.is-open {
            background: linear-gradient(135deg, #1e40af, #0f766e);
            border-color: transparent;
            color: #fff;
            box-shadow: 0 16px 30px rgba(30, 64, 175, 0.28);
        }
        .header-cta-group {
            margin-left: auto;
            flex-shrink: 0;
        }
        .mobile-panel {
            display: block;
            max-height: 0;
            opacity: 0;
            overflow: hidden;
            transform: translateY(-8px);
            pointer-events: none;
            transition: max-height 0.28s ease, opacity 0.2s ease, transform 0.2s ease;
        }
        .mobile-panel.is-open {
            max-height: 520px;
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
        /* Mobile specific tweaks */
        .site-header .container { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; }

        @media (max-width: 768px) {
            .site-header { position: sticky; top: 0; z-index: 60; padding-top: 6px; padding-bottom: 6px; }
            /* Reduce inner container padding to make header visually smaller on mobile */
            .site-header .container > div { padding-top: 4px; padding-bottom: 4px; }
            .site-header .container { position: relative; }
            /* Pin the toggle to the top-right corner on mobile so it doesn't overlap content */
            .mobile-toggle-anchor { position: absolute; z-index: 90; right: 1rem; top: 8px; margin-right: 0; }
            /* Slightly smaller floating toggle to reduce header footprint */
            .mobile-toggle-anchor { width: 44px; height: 44px; border-radius: 12px; }
            .mobile-toggle-anchor i { display: inline-flex; align-items: center; justify-content: center; width: 1em; line-height: 1; font-size: 17px; }
            .brand-mark { width: 40px; height: 40px; }
            .brand-mark i { font-size: 18px; }
            .header-cta-group { margin-left: 0; }
            .hero-gradient { padding-top: 18px; padding-bottom: 12px; }
            .hero-gradient h1 { font-size: 1.75rem; line-height: 1.1; }
            .hero-gradient p { font-size: 0.95rem; }
            .container { padding-left: 1rem; padding-right: 1rem; }
            .service-card { padding: 1rem; }
            .service-icon { width: 48px; height: 48px; }
            .service-icon i { font-size: 18px; }
            .stats-number { font-size: 1.6rem; }
            #mobileMenu { position: relative; left: auto; right: auto; top: auto; z-index: 65; width: 100%; }
            .mobile-panel { width: 100%; }
            .mobile-panel .rounded-2xl {
                border-radius: 18px;
                background: rgba(255, 255, 255, 0.98);
                box-shadow: 0 24px 48px rgba(15, 23, 42, 0.12);
                overflow: hidden;
            }
            .mobile-panel .grid.gap-2 { padding-bottom: 0.25rem; }
            .mobile-panel .grid.gap-2 .mobile-menu-link {
                border-radius: 14px;
                background: #f8fafc;
                border: 1px solid #e2e8f0;
                color: #0f172a;
                font-weight: 600;
                padding: 0.9rem 1rem;
            }
            .mobile-panel .grid.gap-2 .mobile-menu-link:hover {
                background: #eff6ff;
                border-color: #bfdbfe;
                color: #1d4ed8;
            }
            .mobile-panel .mt-3 a {
                border-radius: 14px;
            }
            .nav-link { display: none; }
            .mobile-menu-link { display: flex !important; }
            .header-login { padding: 0.6rem 0.9rem; }
            .hero-gradient img { max-width: 220px; }
            footer .grid { grid-template-columns: 1fr; }
            footer .container { padding-left: 1rem; padding-right: 1rem; }
            .footer-contact li { display: flex; align-items: center; gap: 8px; }
            .hero-gradient .lg\:w-1\/2 { width: 100%; }
            .lg\:w-1\/2 img { margin-top: 12px; }
        }

        /* Desktop header alignment tweaks */
        @media (min-width: 769px) {
            .site-header { padding-top: 8px; padding-bottom: 8px; }
            /* ensure container is positioned so we can absolutely place CTAs */
            .site-header .container { align-items: center; position: relative; }
            /* Pin CTAs to the far-right and vertically center them */
            .header-cta-group { position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); z-index: 90; display: flex; align-items: center; gap: 0.75rem; margin-left: 0; }
            .header-cta-group a { height: 44px; display: inline-flex; align-items: center; gap: 0.5rem; padding-left: 0.9rem; padding-right: 0.9rem; }
            .header-login { padding-left: 1rem; padding-right: 1rem; height: 44px; }
            .brand-mark { margin-right: 0.5rem; }
            /* center the main nav links horizontally in desktop view */
            .nav-links { position: absolute; left: 50%; transform: translateX(-50%); display: flex; gap: 1rem; }
        }

        /* Extra small devices - ensure no overlaps */
        @media (max-width: 420px) {
            .brand-mark { width: 36px; height: 36px; }
            .hero-gradient h1 { font-size: 1.5rem; }
            .service-card { padding: 0.75rem; }
            .service-icon { width: 44px; height: 44px; }
            .stats-number { font-size: 1.4rem; }
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
                        <span class="block text-lg font-bold leading-tight text-slate-900 sm:text-xl">MyBalai</span>
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

                <button type="button" id="mobileMenuButton" class="mobile-toggle-anchor shrink-0 flex h-11 w-11 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-700 md:hidden" aria-label="Open menu" aria-expanded="false">
                    <i class="fas fa-bars"></i>
                </button>
            </div>

            <div id="mobileMenu" class="mobile-panel pb-4 md:hidden mt-2">
                <div class="rounded-2xl border border-slate-200 bg-white p-3 shadow-lg">
                    <div class="grid gap-2">
                        <a href="#home" class="nav-link mobile-menu-link">Home</a>
                        <a href="#services" class="nav-link mobile-menu-link">Services</a>
                        <a href="#features" class="nav-link mobile-menu-link">Features</a>
                        <a href="#about" class="nav-link mobile-menu-link">About</a>
                        <a href="#contact" class="nav-link mobile-menu-link">Contact</a>
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
    <section id="home" class="hero-gradient text-white pt-32 pb-20 sm:pt-36">
        <div class="container mx-auto px-6">
            <div class="flex flex-col lg:flex-row items-center justify-between">
                <div class="lg:w-1/2 mb-10 lg:mb-0" data-aos="fade-right">
                    <h1 class="text-5xl lg:text-6xl font-bold mb-6">
                        Smart Barangay <br>
                        <span class="text-yellow-300">Services Portal</span>
                    </h1>
                    <p class="text-xl mb-8 opacity-90">
                        Get your barangay documents online, file complaints, book appointments, 
                        and stay connected with your community - all in one place.
                    </p>
                    <div class="flex space-x-4">
                        <a href="register.php" class="bg-white text-indigo-600 px-8 py-3 rounded-lg font-semibold hover:shadow-xl transition transform hover:scale-105">
                            Create Account <i class="fas fa-arrow-right ml-2"></i>
                        </a>
                        <a href="#services" class="border-2 border-white px-8 py-3 rounded-lg font-semibold hover:bg-white hover:text-indigo-600 transition">
                            Learn More
                        </a>
                    </div>
                </div>
                <div class="lg:w-1/2" data-aos="fade-left">
                    <img src="https://cdn-icons-png.flaticon.com/512/6191/6191682.png" alt="Hero Image" class="w-full max-w-md mx-auto">
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="bg-white py-12">
        <div class="container mx-auto px-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                <div class="text-center" data-aos="fade-up">
                    <div class="text-4xl font-bold text-indigo-600 stats-number" data-stat="active_residents"><?php echo number_format($homepageStats['active_residents']); ?></div>
                    <div class="text-gray-600 mt-2">Active Residents</div>
                </div>
                <div class="text-center" data-aos="fade-up" data-aos-delay="100">
                    <div class="text-4xl font-bold text-indigo-600 stats-number" data-stat="document_requests"><?php echo number_format($homepageStats['document_requests']); ?></div>
                    <div class="text-gray-600 mt-2">Document Requests</div>
                </div>
                <div class="text-center" data-aos="fade-up" data-aos-delay="200">
                    <div class="text-4xl font-bold text-indigo-600 stats-number" data-stat="open_complaints"><?php echo number_format($homepageStats['open_complaints']); ?></div>
                    <div class="text-gray-600 mt-2">Open Complaints</div>
                </div>
                <div class="text-center" data-aos="fade-up" data-aos-delay="300">
                    <div class="text-4xl font-bold text-indigo-600 stats-number" data-stat="appointments"><?php echo number_format($homepageStats['appointments']); ?></div>
                    <div class="text-gray-600 mt-2">Appointments</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="py-20 bg-gray-50">
        <div class="container mx-auto px-6">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">Our Services</h2>
                <p class="text-xl text-gray-600">Access barangay services anytime, anywhere</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
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
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">Why Choose MyBalai?</h2>
                <p class="text-xl text-gray-600">Revolutionizing barangay services through technology</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                <div class="flex items-start space-x-4" data-aos="fade-right">
                    <div class="bg-green-100 p-3 rounded-full">
                        <i class="fas fa-clock text-green-600 text-2xl"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-800 mb-2">24/7 Accessibility</h3>
                        <p class="text-gray-600">Access barangay services anytime, anywhere without visiting the hall in person.</p>
                    </div>
                </div>
                <div class="flex items-start space-x-4" data-aos="fade-left">
                    <div class="bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-chart-line text-blue-600 text-2xl"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-800 mb-2">Real-time Tracking</h3>
                        <p class="text-gray-600">Track your document requests and complaints status in real-time.</p>
                    </div>
                </div>
                <div class="flex items-start space-x-4" data-aos="fade-right" data-aos-delay="100">
                    <div class="bg-purple-100 p-3 rounded-full">
                        <i class="fas fa-shield-alt text-purple-600 text-2xl"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-800 mb-2">Secure & Verified</h3>
                        <p class="text-gray-600">QR code verification ensures document authenticity and security.</p>
                    </div>
                </div>
                <div class="flex items-start space-x-4" data-aos="fade-left" data-aos-delay="100">
                    <div class="bg-yellow-100 p-3 rounded-full">
                        <i class="fas fa-bell text-yellow-600 text-2xl"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-800 mb-2">Instant Notifications</h3>
                        <p class="text-gray-600">Receive updates and reminders via email and SMS.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section id="about" class="hero-gradient py-20">
        <div class="container mx-auto px-6 text-center" data-aos="zoom-in">
            <h2 class="text-4xl font-bold text-white mb-6">Ready to experience smart barangay services?</h2>
            <p class="text-xl text-white opacity-90 mb-8">Join thousands of residents who already enjoy convenient online services.</p>
            <a href="login.php" class="bg-white text-indigo-600 px-8 py-4 rounded-lg font-semibold text-lg hover:shadow-xl transition inline-block">
                Get Started Now <i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer id="contact" class="bg-gray-900 text-white py-12">
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
        });

        document.querySelectorAll('.mobile-menu-link').forEach(link => {
            link.addEventListener('click', () => {
                mobileMenu.classList.remove('is-open');
                mobileMenuButton?.setAttribute('aria-expanded', 'false');
                mobileMenuButton?.classList.remove('is-open');
                if (mobileMenuIcon) {
                    mobileMenuIcon.className = 'fas fa-bars';
                }
            });
        });

        document.addEventListener('click', (event) => {
            if (!mobileMenu.classList.contains('is-open')) return;
            const clickedInsideHeader = event.target.closest('.site-header');
            if (!clickedInsideHeader) {
                mobileMenu.classList.remove('is-open');
                mobileMenuButton?.setAttribute('aria-expanded', 'false');
                mobileMenuButton?.classList.remove('is-open');
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
</body>
</html>
