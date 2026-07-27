<?php
// DEBUG LOGIN - REMOVE THIS FILE AFTER USE
// Creates a temporary admin session for local debugging only.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['user_id'] = 999999;
    $_SESSION['user_name'] = 'Debug Admin';
    $_SESSION['user_roles'] = ['super_admin'];
    $_SESSION['primary_role'] = 'super_admin';
    $_SESSION['user_type'] = 'admin';

    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Login — MyBalai</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="min-h-screen bg-slate-950 text-slate-100">
    <div class="min-h-screen flex items-center justify-center px-4 py-8">
        <div class="w-full max-w-6xl">
            <div class="hidden md:grid grid-cols-[1.5fr_1fr] gap-6">
                <section class="rounded-[32px] bg-slate-900/95 border border-slate-800 p-10 shadow-[0_40px_120px_rgba(15,23,42,0.55)]">
                    <div class="inline-flex items-center gap-3 rounded-full bg-emerald-500/15 px-4 py-2 text-sm font-semibold text-emerald-300 mb-6">
                        <i class="fas fa-bug"></i>
                        Debug Login Preview
                    </div>
                    <h1 class="text-4xl font-semibold text-white leading-tight">Temporary Admin Access</h1>
                    <p class="mt-5 max-w-lg text-slate-300">This debug utility creates a temporary admin session and redirects you to the admin dashboard. It is intended for local development only and should be removed from production.</p>
                    <div class="mt-10 grid gap-4">
                        <div class="rounded-3xl bg-slate-800 border border-slate-700 p-5">
                            <p class="text-sm text-slate-400 uppercase tracking-[0.24em]">Session Details</p>
                            <div class="mt-3 space-y-2 text-slate-200">
                                <p><span class="font-semibold">User:</span> Debug Admin</p>
                                <p><span class="font-semibold">Role:</span> Super Admin</p>
                                <p><span class="font-semibold">Access:</span> Full admin dashboard</p>
                            </div>
                        </div>
                        <div class="rounded-3xl bg-slate-800 border border-slate-700 p-5">
                            <p class="text-sm text-slate-400 uppercase tracking-[0.24em]">Important</p>
                            <p class="mt-3 text-slate-300">Remove this file after use to prevent accidental exposure of debug access. Do not share this page on public networks.</p>
                        </div>
                    </div>
                </section>
                <aside class="rounded-[32px] bg-slate-50 text-slate-900 p-8 shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm uppercase tracking-[0.24em] text-slate-500">Debug Controls</p>
                            <h2 class="mt-3 text-2xl font-semibold">Start session</h2>
                        </div>
                        <div class="rounded-3xl bg-emerald-500/10 px-3 py-2 text-emerald-600 text-sm font-semibold">Desktop mode</div>
                    </div>
                    <div class="mt-6 space-y-5">
                        <div class="rounded-3xl border border-slate-200 bg-white p-5">
                            <p class="text-sm text-slate-500">Quick action</p>
                            <p class="mt-2 text-lg font-semibold text-slate-900">Launch debug admin session</p>
                        </div>
                        <form method="POST">
                            <button type="submit" class="w-full rounded-full bg-emerald-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-500/20 transition hover:bg-emerald-500">Enter Debug Dashboard</button>
                        </form>
                    </div>
                </aside>
            </div>

            <div class="md:hidden rounded-[28px] bg-slate-900/95 border border-slate-800 p-6 shadow-[0_28px_80px_rgba(15,23,42,0.55)]">
                <div class="inline-flex items-center gap-3 rounded-full bg-emerald-500/15 px-3 py-2 text-sm font-semibold text-emerald-300 mb-4">
                    <i class="fas fa-bug"></i>
                    Debug Login Preview
                </div>
                <h1 class="text-3xl font-semibold text-white">Temporary Admin Access</h1>
                <p class="mt-4 text-sm leading-6 text-slate-300">This utility creates a temporary super admin session for local development. Remove this file when you're done.</p>
                <div class="mt-6 space-y-4">
                    <div class="rounded-3xl bg-slate-800 border border-slate-700 p-4">
                        <p class="text-xs uppercase tracking-[0.24em] text-slate-500">User</p>
                        <p class="mt-2 text-base font-semibold text-white">Debug Admin</p>
                        <p class="mt-1 text-sm text-slate-400">Role: Super Admin</p>
                    </div>
                    <div class="rounded-3xl bg-slate-800 border border-slate-700 p-4">
                        <p class="text-xs uppercase tracking-[0.24em] text-slate-500">Safety</p>
                        <p class="mt-2 text-sm text-slate-400">Only use this file locally. Delete it before publishing your project.</p>
                    </div>
                </div>
                <form method="POST" class="mt-6">
                    <button type="submit" class="w-full rounded-full bg-emerald-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-500/20 transition hover:bg-emerald-500">Enter Debug Dashboard</button>
                </form>
            </div>

            <p class="mt-6 text-center text-xs uppercase tracking-[0.24em] text-slate-500">Local development only</p>
        </div>
    </div>
</body>
</html>
