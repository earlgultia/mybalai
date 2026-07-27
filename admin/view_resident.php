<?php
require_once '_admin_common.php';

$allowedRoles = ['super_admin','barangay_captain','barangay_secretary','barangay_treasurer','barangay_kagawad','admin_staff','health_worker','tanod'];
if (!hasRole($allowedRoles)) {
    redirect('dashboard.php');
}

$userId = (int)($_GET['id'] ?? 0);
if ($userId <= 0) {
    adminHeader('Resident Profile', 'residents');
    echo '<div class="bg-white p-6 rounded shadow">Invalid resident specified.</div>';
    adminFooter();
    exit;
}

// fetch resident + profile
$stmt = $pdo->prepare("SELECT u.user_id, u.first_name, u.last_name, u.email, u.phone_number, u.is_active, rp.qr_code, rp.house_number, rp.street_address, rp.barangay
    FROM users u
    LEFT JOIN resident_profiles rp ON rp.user_id = u.user_id
    WHERE u.user_id = ? LIMIT 1");
$stmt->execute([$userId]);
$resident = $stmt->fetch();

if (!$resident) {
    adminHeader('Resident Profile', 'residents');
    echo '<div class="bg-white p-6 rounded shadow">Resident not found.</div>';
    adminFooter();
    exit;
}

// total scans and recent logs
$qrToken = $resident['qr_code'] ?? '';
$totalScans = 0;
$recentScans = [];
if ($qrToken) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM qr_logs WHERE qr_code = ?");
    $stmt->execute([$qrToken]);
    $totalScans = (int)$stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT ql.qr_log_id, ql.qr_code, ql.scan_location, ql.scan_timestamp, u.user_id as scanner_id, u.first_name as scanner_first, u.last_name as scanner_last
        FROM qr_logs ql
        LEFT JOIN users u ON u.user_id = ql.scanned_by
        WHERE ql.qr_code = ?
        ORDER BY ql.scan_timestamp DESC
        LIMIT 20");
    $stmt->execute([$qrToken]);
    $recentScans = $stmt->fetchAll();
}

adminHeader('Resident Profile', 'residents');
?>

<div class="space-y-6">
    <div class="md:hidden rounded-[28px] bg-slate-950 text-white p-5 shadow-lg border border-slate-800">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-xs uppercase tracking-[0.24em] text-slate-400">Resident profile</p>
                <h1 class="mt-2 text-2xl font-semibold">Resident details</h1>
                <p class="mt-2 text-sm text-slate-300">Quick mobile view of this resident's profile, address, and scan activity.</p>
            </div>
            <span class="inline-flex items-center rounded-full bg-slate-800 px-3 py-1 text-xs font-semibold text-slate-200"><?php echo $totalScans; ?> scans</span>
        </div>
        <div class="mt-5 grid grid-cols-2 gap-3">
            <div class="rounded-3xl bg-slate-900 p-4">
                <p class="text-xs uppercase tracking-wide text-slate-500">Status</p>
                <p class="mt-3 text-xl font-semibold text-white"><?php echo $resident['is_active'] ? 'Active' : 'Inactive'; ?></p>
            </div>
            <div class="rounded-3xl bg-slate-900 p-4">
                <p class="text-xs uppercase tracking-wide text-slate-500">Location</p>
                <p class="mt-3 text-sm font-semibold text-white"><?php echo e(trim(($resident['house_number'] ? $resident['house_number'] . ' ' : '') . ($resident['street_address'] ?? '')) ?: 'N/A'); ?></p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-[28px] border border-slate-200 shadow-sm overflow-hidden">
        <div class="grid grid-cols-1 gap-6 md:grid-cols-[1.4fr_0.95fr] p-6">
            <section class="space-y-6">
                <div class="space-y-3">
                    <div class="flex flex-col gap-2 md:gap-3">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <h2 class="text-2xl font-semibold text-slate-900"><?php echo e($resident['first_name'] . ' ' . $resident['last_name']); ?></h2>
                                <p class="text-sm text-slate-500">Profile overview and resident contact details.</p>
                            </div>
                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold <?php echo $resident['is_active'] ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'; ?>"><?php echo $resident['is_active'] ? 'Active' : 'Inactive'; ?></span>
                        </div>
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div class="rounded-3xl bg-slate-50 p-4">
                                <p class="text-xs uppercase tracking-wide text-slate-500">Email</p>
                                <p class="mt-2 text-sm font-semibold text-slate-900"><?php echo e($resident['email'] ?: 'N/A'); ?></p>
                            </div>
                            <div class="rounded-3xl bg-slate-50 p-4">
                                <p class="text-xs uppercase tracking-wide text-slate-500">Phone</p>
                                <p class="mt-2 text-sm font-semibold text-slate-900"><?php echo e($resident['phone_number'] ?: 'N/A'); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="rounded-3xl bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-500">Address</p>
                        <p class="mt-2 text-sm font-semibold text-slate-900"><?php echo e((trim(($resident['house_number'] ? $resident['house_number'] . ' ' : '') . ($resident['street_address'] ?? '')) ?: 'N/A')); ?></p>
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-semibold text-slate-900">QR scan history</p>
                        <span class="text-xs text-slate-500"><?php echo $totalScans; ?> total</span>
                    </div>
                    <div class="mt-4 space-y-3">
                        <?php if (empty($recentScans)): ?>
                            <div class="rounded-2xl bg-white px-4 py-4 text-sm text-slate-500">No scans recorded yet.</div>
                        <?php else: ?>
                            <?php foreach ($recentScans as $s): ?>
                                <div class="rounded-2xl bg-white p-4 shadow-sm">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <div class="font-medium text-slate-900"><?php echo e($s['scan_location'] ?: 'Unknown location'); ?></div>
                                            <div class="mt-1 text-xs text-slate-500"><?php echo e(date('M d, Y H:i', strtotime($s['scan_timestamp']))); ?></div>
                                        </div>
                                        <div class="text-xs text-slate-600 text-right">
                                            <?php if ($s['scanner_id']): ?>
                                                <?php echo e($s['scanner_first'] . ' ' . $s['scanner_last']); ?>
                                            <?php else: ?>
                                                System
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

            <aside class="space-y-6">
                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-6 text-center">
                    <div class="mb-4">
                        <p class="text-sm uppercase tracking-[0.24em] text-slate-500">Resident QR</p>
                        <h3 class="mt-2 text-lg font-semibold text-slate-900">Scan token</h3>
                    </div>
                    <div id="qrcode" class="mx-auto mb-4"></div>
                    <?php if ($qrToken): ?>
                        <div class="text-xs text-slate-600 mb-3 break-words">Token: <span id="qrTokenText"><?php echo e($qrToken); ?></span></div>
                        <div class="grid gap-3">
                            <button id="copyBtn" class="w-full rounded-2xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">Copy token</button>
                            <a href="scan_qr.php" class="inline-flex w-full justify-center rounded-2xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Back to Scanner</a>
                        </div>
                    <?php else: ?>
                        <div class="text-sm text-slate-500">No QR token assigned for this resident.</div>
                    <?php endif; ?>
                </div>
                <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
                    <p class="text-sm font-semibold text-slate-900">Resident summary</p>
                    <div class="mt-3 grid gap-3">
                        <div class="rounded-2xl bg-slate-50 p-3">
                            <p class="text-xs uppercase tracking-wide text-slate-500">Scan count</p>
                            <p class="mt-2 text-2xl font-semibold text-slate-900"><?php echo $totalScans; ?></p>
                        </div>
                        <div class="rounded-2xl bg-slate-50 p-3">
                            <p class="text-xs uppercase tracking-wide text-slate-500">Active status</p>
                            <p class="mt-2 text-lg font-semibold text-slate-900"><?php echo $resident['is_active'] ? 'Active' : 'Inactive'; ?></p>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
(function(){
    var token = <?php echo json_encode($qrToken); ?>;
    if (token) {
        new QRCode(document.getElementById('qrcode'), { text: token, width: 140, height: 140 });
        document.getElementById('copyBtn').addEventListener('click', function(){
            navigator.clipboard.writeText(token).then(function(){
                alert('Token copied');
            }).catch(function(){
                alert('Unable to copy');
            });
        });
    }
})();
</script>

<?php adminFooter();
