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

<div class="bg-white rounded-lg shadow p-6">
    <div class="flex items-start justify-between">
        <div>
            <h2 class="text-2xl font-semibold mb-1"><?php echo e($resident['first_name'] . ' ' . $resident['last_name']); ?></h2>
            <div class="text-sm text-gray-600">Email: <?php echo e($resident['email'] ?: 'N/A'); ?></div>
            <div class="text-sm text-gray-600">Phone: <?php echo e($resident['phone_number'] ?: 'N/A'); ?></div>
            <div class="text-sm text-gray-600 mt-2">Address: <?php echo e(($resident['house_number'] ? $resident['house_number'] . ' ' : '') . ($resident['street_address'] ?? '')); ?></div>
            <div class="mt-3">
                <span class="px-2 py-1 rounded-full text-xs <?php echo $resident['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>"><?php echo $resident['is_active'] ? 'Active' : 'Inactive'; ?></span>
            </div>
        </div>

        <div class="w-48 text-center">
            <div id="qrcode" class="mx-auto mb-2"></div>
            <?php if ($qrToken): ?>
                <div class="text-xs text-gray-600 mb-2">Token: <span id="qrTokenText" class="break-all"><?php echo e($qrToken); ?></span></div>
                <div class="flex gap-2 justify-center">
                    <button id="copyBtn" class="px-3 py-1 bg-slate-100 rounded">Copy</button>
                    <a href="scan_qr.php" class="px-3 py-1 bg-blue-600 text-white rounded">Back to Scanner</a>
                </div>
            <?php else: ?>
                <div class="text-sm text-gray-500">No QR token assigned for this resident.</div>
            <?php endif; ?>
        </div>
    </div>

    <hr class="my-4" />

    <div>
        <h3 class="font-semibold mb-2">QR Scan History (<?php echo $totalScans; ?>)</h3>
        <?php if (empty($recentScans)): ?>
            <div class="text-sm text-gray-500">No scans recorded yet.</div>
        <?php else: ?>
            <div class="space-y-2">
                <?php foreach ($recentScans as $s): ?>
                    <div class="p-3 border rounded flex justify-between items-center">
                        <div>
                            <div class="text-sm font-medium"><?php echo e($s['scan_location'] ?: 'Unknown location'); ?></div>
                            <div class="text-xs text-gray-500"><?php echo e(date('M d, Y H:i', strtotime($s['scan_timestamp']))); ?></div>
                        </div>
                        <div class="text-xs text-gray-600 text-right">
                            <?php if ($s['scanner_id']): ?>
                                <?php echo e($s['scanner_first'] . ' ' . $s['scanner_last']); ?>
                            <?php else: ?>
                                System
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
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
