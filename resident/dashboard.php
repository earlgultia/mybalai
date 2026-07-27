
    <?php
    require_once '_resident_common.php';

    $user_id = $_SESSION['user_id'];

    // Get resident profile
    $stmt = $pdo->prepare("SELECT * FROM resident_profiles WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $profile = $stmt->fetch();

    // Get user data
    $user = getUserData($user_id);

    // Get document requests
    $stmt = $pdo->prepare("SELECT * FROM document_requests WHERE user_id = ? ORDER BY requested_at DESC LIMIT 5");
    $stmt->execute([$user_id]);
    $documentRequests = $stmt->fetchAll();

    // Get complaints
    $stmt = $pdo->prepare("SELECT * FROM complaints WHERE complainant_id = ? ORDER BY created_at DESC LIMIT 5");
    $stmt->execute([$user_id]);
    $complaints = $stmt->fetchAll();

    // Get appointments
    $stmt = $pdo->prepare("SELECT * FROM appointments WHERE user_id = ? ORDER BY preferred_date ASC LIMIT 5");
    $stmt->execute([$user_id]);
    $appointments = $stmt->fetchAll();

    // Get announcements
    $stmt = $pdo->query("SELECT * FROM announcements WHERE is_active = 1 AND (target_audience = 'all' OR target_audience = 'residents_only') ORDER BY published_date DESC LIMIT 5");
    $announcements = $stmt->fetchAll();

    // Get subscription status
    $stmt = $pdo->prepare("SELECT * FROM subscriptions WHERE user_id = ? ORDER BY due_date DESC LIMIT 1");
    $stmt->execute([$user_id]);
    $subscription = $stmt->fetch();

    // Count statistics
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_requests FROM document_requests WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $totalRequests = $stmt->fetch()['total_requests'];

    $stmt = $pdo->prepare("SELECT COUNT(*) as total_complaints FROM complaints WHERE complainant_id = ?");
    $stmt->execute([$user_id]);
    $totalComplaints = $stmt->fetch()['total_complaints'];

    $stmt = $pdo->prepare("SELECT COUNT(*) as pending_requests FROM document_requests WHERE user_id = ? AND status = 'pending'");
    $stmt->execute([$user_id]);
    $pendingRequests = $stmt->fetch()['pending_requests'];

    residentHeader('Dashboard', 'dashboard');
    ?>

        <style>
            .dashboard-shell { padding: 0 1rem; }
            @media (min-width: 768px) { .dashboard-shell { padding: 0; } }
            .dashboard-hero-grid { grid-template-columns: 1fr; }
            .dashboard-summary-grid { grid-template-columns: 1fr; }
            .dashboard-action-grid { grid-template-columns: 1fr; }
            @media (min-width: 1024px) {
                .dashboard-hero-grid { grid-template-columns: 1.55fr 0.95fr; }
                .dashboard-summary-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
                .dashboard-action-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            }
            @media (max-width: 640px) {
                .dashboard-mobile-summary { display: grid !important; grid-template-columns: 1fr !important; }
            }
        </style>

        <div class="dashboard-shell mx-auto max-w-6xl space-y-6 py-4 sm:py-6">
            <section class="overflow-hidden rounded-[32px] bg-gradient-to-br from-slate-950 via-blue-900 to-cyan-700 p-6 text-white shadow-[0_30px_80px_rgba(15,23,42,0.22)]">
                <div class="grid dashboard-hero-grid gap-6 lg:items-center">
                    <div>
                        <p class="text-sm uppercase tracking-[0.32em] text-cyan-200/75">Resident dashboard</p>
                        <h1 class="mt-3 text-3xl font-semibold tracking-[-0.03em] sm:text-4xl">Welcome back, <?php echo e($user['first_name']); ?>!</h1>
                        <p class="mt-4 max-w-2xl text-sm leading-7 text-cyan-100/80 sm:text-base">Your resident portal is the fastest way to request barangay documents, submit concerns, schedule appointments, and stay informed.</p>

                        <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                            <div class="rounded-[24px] bg-white/10 p-4 ring-1 ring-white/15 backdrop-blur-sm">
                                <p class="text-xs uppercase tracking-[0.28em] text-cyan-200/80">Total documents</p>
                                <p class="mt-3 text-2xl font-semibold text-white"><?php echo $totalRequests; ?></p>
                            </div>
                            <div class="rounded-[24px] bg-white/10 p-4 ring-1 ring-white/15 backdrop-blur-sm">
                                <p class="text-xs uppercase tracking-[0.28em] text-cyan-200/80">Pending</p>
                                <p class="mt-3 text-2xl font-semibold text-amber-200"><?php echo $pendingRequests; ?></p>
                            </div>
                            <div class="rounded-[24px] bg-white/10 p-4 ring-1 ring-white/15 backdrop-blur-sm">
                                <p class="text-xs uppercase tracking-[0.28em] text-cyan-200/80">Complaints</p>
                                <p class="mt-3 text-2xl font-semibold text-white"><?php echo $totalComplaints; ?></p>
                            </div>
                        </div>
                    </div>

                    <aside class="rounded-[28px] bg-white/10 p-5 ring-1 ring-white/15">
                        <p class="text-xs uppercase tracking-[0.3em] text-cyan-200/85">Snapshot</p>
                        <div class="mt-5 space-y-4">
                            <div class="rounded-[24px] bg-slate-950/80 p-4">
                                <p class="text-sm text-cyan-200/80">Upcoming appointment</p>
                                <?php if (count($appointments) > 0): ?>
                                    <p class="mt-3 text-lg font-semibold text-white"><?php echo date('M d, Y', strtotime($appointments[0]['preferred_date'])); ?></p>
                                    <p class="text-sm text-cyan-100/80">at <?php echo date('g:i A', strtotime($appointments[0]['preferred_time'])); ?></p>
                                    <p class="mt-3 text-sm text-slate-300"><?php echo e(str_replace('_', ' ', ucfirst($appointments[0]['status']))); ?></p>
                                <?php else: ?>
                                    <p class="mt-3 text-lg font-semibold text-white">No appointments</p>
                                    <p class="text-sm text-slate-300">Book one from quick actions.</p>
                                <?php endif; ?>
                            </div>

                            <div class="rounded-[24px] bg-slate-950/80 p-4">
                                <p class="text-sm text-cyan-200/80">Subscription status</p>
                                <p class="mt-3 text-2xl font-semibold <?php echo ($subscription && $subscription['status'] == 'paid') ? 'text-emerald-200' : 'text-rose-200'; ?>">
                                    <?php echo ($subscription && $subscription['status'] == 'paid') ? 'Paid' : 'Pending'; ?>
                                </p>
                                <?php if ($subscription): ?>
                                    <p class="mt-2 text-sm text-slate-300">Due <?php echo date('M d, Y', strtotime($subscription['due_date'])); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </aside>
                </div>

                <div class="mt-6 hidden lg:grid dashboard-summary-grid gap-4">
                    <div class="rounded-[24px] bg-white/10 p-5 ring-1 ring-white/10">
                        <p class="text-xs uppercase tracking-[0.28em] text-cyan-200/80">Current requests</p>
                        <p class="mt-3 text-2xl font-semibold text-white"><?php echo $totalRequests; ?></p>
                    </div>
                    <div class="rounded-[24px] bg-white/10 p-5 ring-1 ring-white/10">
                        <p class="text-xs uppercase tracking-[0.28em] text-cyan-200/80">Open issues</p>
                        <p class="mt-3 text-2xl font-semibold text-amber-200"><?php echo $pendingRequests; ?></p>
                    </div>
                    <div class="rounded-[24px] bg-white/10 p-5 ring-1 ring-white/10">
                        <p class="text-xs uppercase tracking-[0.28em] text-cyan-200/80">Logged complaints</p>
                        <p class="mt-3 text-2xl font-semibold text-white"><?php echo $totalComplaints; ?></p>
                    </div>
                </div>

                <div class="mt-6 dashboard-mobile-summary gap-4 lg:hidden">
                    <div class="rounded-[24px] bg-white/10 p-4 ring-1 ring-white/10">
                        <p class="text-xs uppercase tracking-[0.28em] text-cyan-200/80">Quick summary</p>
                        <div class="mt-3 text-sm text-slate-200">Tap to review your latest requests, complaints, and appointments.</div>
                    </div>
                    <div class="rounded-[24px] bg-white/10 p-4 ring-1 ring-white/10">
                        <p class="text-xs uppercase tracking-[0.28em] text-cyan-200/80">Need help?</p>
                        <div class="mt-3 text-sm text-slate-200">Use the action cards below to get started.</div>
                    </div>
                </div>
            </section>

            <section class="grid gap-6 xl:grid-cols-[1.7fr_0.95fr]">
                <div class="space-y-6">
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-4">
                        <div class="rounded-[28px] bg-white shadow p-6">
                            <p class="text-sm text-slate-500">Total Documents</p>
                            <p class="mt-4 text-3xl font-semibold text-slate-900"><?php echo $totalRequests; ?></p>
                        </div>
                        <div class="rounded-[28px] bg-white shadow p-6">
                            <p class="text-sm text-slate-500">Pending Requests</p>
                            <p class="mt-4 text-3xl font-semibold text-amber-600"><?php echo $pendingRequests; ?></p>
                        </div>
                        <div class="rounded-[28px] bg-white shadow p-6">
                            <p class="text-sm text-slate-500">Total Complaints</p>
                            <p class="mt-4 text-3xl font-semibold text-slate-900"><?php echo $totalComplaints; ?></p>
                        </div>
                        <div class="rounded-[28px] bg-white shadow p-6">
                            <p class="text-sm text-slate-500">Subscription Status</p>
                            <p class="mt-4 text-3xl font-semibold <?php echo ($subscription && $subscription['status'] == 'paid') ? 'text-emerald-600' : 'text-rose-600'; ?>">
                                <?php echo ($subscription && $subscription['status'] == 'paid') ? 'Paid' : 'Pending'; ?>
                            </p>
                        </div>
                    </div>

                    <div class="bg-white rounded-[32px] shadow p-6">
                        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h3 class="text-xl font-semibold text-slate-900">Recent Document Requests</h3>
                                <p class="text-sm text-slate-500">Latest five requests from your account.</p>
                            </div>
                            <a href="requests.php" class="text-blue-600 text-sm font-semibold hover:text-blue-700">View All</a>
                        </div>
                        <?php if (count($documentRequests) > 0): ?>
                            <div class="space-y-3">
                                <?php foreach ($documentRequests as $request): ?>
                                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                        <div>
                                            <p class="font-semibold text-slate-900"><?php echo e(documentTypeLabel($request['document_type'] ?? 'Document')); ?></p>
                                            <p class="text-sm text-slate-500">Requested <?php echo date('M d, Y', strtotime($request['requested_at'])); ?></p>
                                        </div>
                                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold <?php echo statusBadge($request['status'] ?? 'pending'); ?>">
                                            <?php echo labelize($request['status'] ?? 'pending'); ?>
                                        </span>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-center text-slate-500 py-8">No document requests yet.</p>
                        <?php endif; ?>
                    </div>

                    <div class="bg-white rounded-[32px] shadow p-6">
                        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h3 class="text-xl font-semibold text-slate-900">Recent Complaints</h3>
                                <p class="text-sm text-slate-500">Track the latest complaints you submitted.</p>
                            </div>
                            <a href="complaints.php" class="text-blue-600 text-sm font-semibold hover:text-blue-700">View All</a>
                        </div>
                        <?php if (count($complaints) > 0): ?>
                            <div class="space-y-3">
                                <?php foreach ($complaints as $complaint): ?>
                                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                        <div>
                                            <p class="font-semibold text-slate-900"><?php echo ucfirst($complaint['complaint_type']); ?></p>
                                            <p class="text-sm text-slate-500"><?php echo substr($complaint['description'], 0, 100); ?>...</p>
                                        </div>
                                        <div class="space-y-1 text-right">
                                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold <?php echo statusBadge($complaint['status'] ?? 'submitted'); ?>">
                                                <?php echo labelize($complaint['status'] ?? 'submitted'); ?>
                                            </span>
                                            <p class="text-xs text-slate-400">Filed <?php echo date('M d, Y', strtotime($complaint['created_at'])); ?></p>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-center text-slate-500 py-8">No complaints filed yet.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-white rounded-[32px] shadow p-6">
                        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h3 class="text-xl font-semibold text-slate-900">Quick Actions</h3>
                                <p class="text-sm text-slate-500">Jump to the tools you use most.</p>
                            </div>
                            <span class="text-sm font-semibold text-blue-600">Fast access</span>
                        </div>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <button onclick="location.href='requests.php'" class="group rounded-[24px] border border-slate-200 bg-slate-50 p-5 text-left transition hover:border-blue-300 hover:bg-white">
                                <div class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-600 text-white shadow-sm">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <p class="mt-4 font-semibold text-slate-900">Request Document</p>
                                <p class="mt-2 text-sm text-slate-500">Create a new request.</p>
                            </button>
                            <button onclick="location.href='complaints.php'" class="group rounded-[24px] border border-slate-200 bg-slate-50 p-5 text-left transition hover:border-red-300 hover:bg-white">
                                <div class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-red-600 text-white shadow-sm">
                                    <i class="fas fa-gavel"></i>
                                </div>
                                <p class="mt-4 font-semibold text-slate-900">File Complaint</p>
                                <p class="mt-2 text-sm text-slate-500">Submit a new complaint.</p>
                            </button>
                            <button onclick="location.href='appointments.php'" class="group rounded-[24px] border border-slate-200 bg-slate-50 p-5 text-left transition hover:border-emerald-300 hover:bg-white">
                                <div class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-600 text-white shadow-sm">
                                    <i class="fas fa-calendar-check"></i>
                                </div>
                                <p class="mt-4 font-semibold text-slate-900">Book Appointment</p>
                                <p class="mt-2 text-sm text-slate-500">Schedule a visit.</p>
                            </button>
                            <button onclick="location.href='view_qr.php'" class="group rounded-[24px] border border-slate-200 bg-slate-50 p-5 text-left transition hover:border-purple-300 hover:bg-white">
                                <div class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-purple-600 text-white shadow-sm">
                                    <i class="fas fa-qrcode"></i>
                                </div>
                                <p class="mt-4 font-semibold text-slate-900">My QR ID</p>
                                <p class="mt-2 text-sm text-slate-500">View your QR code.</p>
                            </button>
                        </div>
                    </div>

                    <div class="bg-white rounded-[32px] shadow p-6">
                        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h3 class="text-xl font-semibold text-slate-900">Subscription & Payments</h3>
                                <p class="text-sm text-slate-500">Manage your payment status and invoices.</p>
                            </div>
                        </div>
                        <?php if ($subscription): ?>
                        <div class="rounded-3xl bg-slate-50 p-5">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <p class="text-sm text-slate-500">Monthly fee</p>
                                    <p class="mt-2 text-2xl font-semibold text-slate-900">₱<?php echo number_format($subscription['amount'], 2); ?></p>
                                </div>
                                <span class="rounded-full px-3 py-1 text-sm font-semibold <?php echo $subscription['status'] == 'paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'; ?>">
                                    <?php echo ucfirst($subscription['status']); ?>
                                </span>
                            </div>
                            <p class="mt-4 text-sm text-slate-500">Due date: <?php echo date('F j, Y', strtotime($subscription['due_date'])); ?></p>
                            <?php if ($subscription['status'] != 'paid'): ?>
                            <button class="mt-5 w-full rounded-2xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-blue-700">Pay Now</button>
                            <?php endif; ?>
                        </div>
                        <?php else: ?>
                        <p class="text-slate-500 py-6 text-center">No subscription record found.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div class="bg-white rounded-[32px] shadow p-6">
                <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-xl font-semibold text-slate-900">Upcoming Appointments</h3>
                        <p class="text-sm text-slate-500">Your next scheduled visits with the barangay.</p>
                    </div>
                    <a href="appointments.php" class="text-blue-600 text-sm font-semibold hover:text-blue-700">View All</a>
                </div>
                <?php if (count($appointments) > 0): ?>
                    <div class="space-y-3">
                        <?php foreach ($appointments as $appointment): ?>
                        <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="font-semibold text-slate-900"><?php echo labelize($appointment['appointment_type'] ?? 'Appointment'); ?></p>
                                    <p class="text-sm text-slate-500"><?php echo date('M d, Y', strtotime($appointment['preferred_date'])); ?> at <?php echo date('g:i A', strtotime($appointment['preferred_time'])); ?></p>
                                </div>
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold <?php echo statusBadge($appointment['status'] ?? 'pending'); ?>">
                                    <?php echo labelize($appointment['status'] ?? 'pending'); ?>
                                </span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-center text-slate-500 py-8">No upcoming appointments.</p>
                <?php endif; ?>
            </div>

            <div class="bg-white rounded-[32px] shadow p-6">
                <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-xl font-semibold text-slate-900">Latest Announcements</h3>
                        <p class="text-sm text-slate-500">Stay updated with community news.</p>
                    </div>
                </div>
                <?php if (count($announcements) > 0): ?>
                    <div class="space-y-4">
                        <?php foreach ($announcements as $announcement): ?>
                        <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                            <button type="button" onclick="openAnnouncement(this)" class="w-full text-left">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <p class="font-semibold text-slate-900"><?php echo e($announcement['title']); ?></p>
                                        <p class="text-sm text-slate-500"><?php echo date('M d, Y', strtotime($announcement['published_date'])); ?></p>
                                    </div>
                                    <span class="rounded-full bg-slate-200 px-3 py-1 text-xs font-semibold text-slate-700"><?php echo ucfirst($announcement['priority']); ?></span>
                                </div>
                                <p class="mt-3 text-sm text-slate-600"><?php echo e(substr($announcement['content'], 0, 150)); ?>...</p>
                            </button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-center text-slate-500 py-8">No announcements available.</p>
                <?php endif; ?>
            </div>
        </section>

        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            function openAnnouncement(el) {
                var title = el.dataset.title || '';
                var content = el.dataset.content || '';
                var date = el.dataset.date || '';
                var priority = (el.dataset.priority || '').toLowerCase();
                var badge = '';
                if (priority === 'urgent') {
                    badge = '<span class="inline-block mb-3 rounded-full bg-red-500 px-3 py-1 text-xs font-bold text-white">URGENT</span>';
                } else if (priority === 'high') {
                    badge = '<span class="inline-block mb-3 rounded-full bg-orange-500 px-3 py-1 text-xs font-bold text-white">HIGH PRIORITY</span>';
                }

                var html = badge + '<div class="text-sm text-gray-500 mb-3">' + date + '</div>' +
                    '<div style="text-align:left; white-space:pre-line; line-height:1.65; color:#374151;">' +
                    content.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;') +
                    '</div>';

                if (window.Swal) {
                    Swal.fire({
                        title: title,
                        html: html,
                        width: 760,
                        confirmButtonText: 'Close',
                        showCloseButton: true,
                        customClass: {
                            popup: 'rounded-2xl',
                            confirmButton: 'bg-blue-600 px-4 py-2 rounded-lg'
                        }
                    });
                } else {
                    alert(title + '\n\n' + content);
                }
            }
        </script>
        <!-- Announcement popup removed -->
<?php residentFooter(); ?>
