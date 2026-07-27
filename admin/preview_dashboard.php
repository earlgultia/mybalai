<?php
require_once '_admin_common.php';

adminHeader('Preview Admin Dashboard', 'dashboard');
?>

<div class="space-y-6">
    <section class="md:hidden rounded-[28px] bg-slate-950 text-white p-5 shadow-lg">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-xs uppercase tracking-[0.24em] text-slate-400">Preview mode</p>
                <h1 class="mt-3 text-2xl font-semibold">Mobile sidebar preview</h1>
                <p class="mt-2 text-sm text-slate-300">Use this view to verify the admin sidebar and header behavior on smaller screens.</p>
            </div>
            <div class="rounded-3xl bg-slate-800 px-4 py-2 text-sm font-semibold text-slate-200">Mobile view</div>
        </div>
        <div class="mt-5 grid grid-cols-2 gap-3">
            <div class="rounded-3xl bg-slate-900 border border-slate-700 p-4">
                <p class="text-xs uppercase tracking-[0.24em] text-slate-500">Sidebar</p>
                <p class="mt-3 text-xl font-semibold">Collapsed</p>
            </div>
            <div class="rounded-3xl bg-slate-900 border border-slate-700 p-4">
                <p class="text-xs uppercase tracking-[0.24em] text-slate-500">Header</p>
                <p class="mt-3 text-xl font-semibold">Sticky</p>
            </div>
        </div>
    </section>

    <section class="hidden md:grid md:grid-cols-[1.4fr_0.6fr] gap-6">
        <div class="rounded-[32px] bg-white border border-slate-200 p-6 shadow-sm">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-sm uppercase tracking-[0.24em] text-slate-500">Desktop preview</p>
                    <h2 class="mt-2 text-3xl font-semibold text-slate-900">Admin sidebar preview</h2>
                </div>
                <span class="rounded-3xl bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-700">Desktop</span>
            </div>
            <div class="mt-6 grid grid-cols-2 gap-4">
                <div class="rounded-3xl bg-slate-50 border border-slate-200 p-5">
                    <p class="text-sm text-slate-500">Sidebar width</p>
                    <p class="mt-3 text-2xl font-semibold text-slate-900">280px</p>
                </div>
                <div class="rounded-3xl bg-slate-50 border border-slate-200 p-5">
                    <p class="text-sm text-slate-500">Content padding</p>
                    <p class="mt-3 text-2xl font-semibold text-slate-900">1.5rem</p>
                </div>
            </div>
        </div>
        <aside class="rounded-[32px] bg-white border border-slate-200 p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-slate-900">Preview notes</h3>
            <p class="mt-4 text-sm text-slate-600 leading-6">This page is designed to let you test header/sidebar interaction and responsive layout behavior without actual admin content. Resize the window to confirm the mobile menu, content spacing, and action visibility.</p>
        </aside>
    </section>

    <section class="bg-white rounded-3xl border border-slate-200 shadow p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-slate-900">Sample content area</h2>
                <p class="mt-2 text-sm text-slate-600">This long content block simulates the admin page body so you can check scrolling and header behavior.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <span class="rounded-full bg-slate-100 px-4 py-2 text-sm text-slate-700">Header behavior</span>
                <span class="rounded-full bg-slate-100 px-4 py-2 text-sm text-slate-700">Sidebar interaction</span>
                <span class="rounded-full bg-slate-100 px-4 py-2 text-sm text-slate-700">Responsive preview</span>
            </div>
        </div>
        <div class="mt-6 rounded-[28px] border border-slate-200 bg-slate-50 p-6" style="min-height: 600px;">
            <div class="h-full rounded-3xl bg-gradient-to-br from-slate-100 via-slate-50 to-white p-6 shadow-inner">
                <p class="text-sm text-slate-500">Resize your browser to mobile width and verify that the admin sidebar collapses into the mobile navigation, the topbar remains visible, and the content stays accessible.</p>
                <div class="mt-6 grid gap-4 sm:grid-cols-2">
                    <div class="rounded-3xl bg-white border border-slate-200 p-4">
                        <p class="text-sm text-slate-500">Large content section</p>
                        <div class="mt-3 h-32 rounded-2xl bg-slate-100"></div>
                    </div>
                    <div class="rounded-3xl bg-white border border-slate-200 p-4">
                        <p class="text-sm text-slate-500">Secondary panel</p>
                        <div class="mt-3 h-32 rounded-2xl bg-slate-100"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php adminFooter(); ?>
