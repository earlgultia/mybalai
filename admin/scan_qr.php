<?php
require_once '_admin_common.php';

// Expand allowed scanner roles to include common staff who may scan
$allowedRoles = ['super_admin','barangay_captain','barangay_secretary','barangay_treasurer','barangay_kagawad','admin_staff','health_worker','tanod'];
if (!hasRole($allowedRoles)) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Forbidden']);
        exit;
    }
    redirect('dashboard.php');
}

// JSON POST handler: accept { qr_code, scan_location }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?: [];
    header('Content-Type: application/json');

    $qrCode = isset($input['qr_code']) ? sanitize($input['qr_code']) : '';
    $scanLocation = isset($input['scan_location']) ? sanitize($input['scan_location']) : '';

    if ($qrCode === '') {
        echo json_encode(['success' => false, 'message' => 'Missing qr_code']);
        exit;
    }

    // find resident by qr_code
    $stmt = $pdo->prepare("SELECT u.user_id, u.first_name, u.last_name, rp.qr_code, u.is_active
        FROM users u
        LEFT JOIN resident_profiles rp ON rp.user_id = u.user_id
        WHERE rp.qr_code = ?
        LIMIT 1");
    $stmt->execute([$qrCode]);
    $resident = $stmt->fetch();

    if (!$resident) {
        echo json_encode(['success' => false, 'message' => 'QR code not recognized']);
        exit;
    }

    // record scan
    $stmt = $pdo->prepare("INSERT INTO qr_logs (qr_code, scanned_by, scan_location) VALUES (?, ?, ?)");
    $stmt->execute([$qrCode, $_SESSION['user_id'] ?? null, $scanLocation]);

    // get total scans
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM qr_logs WHERE qr_code = ?");
    $stmt->execute([$qrCode]);
    $total = (int)$stmt->fetchColumn();

    logActivity($_SESSION['user_id'], 'Scanned resident QR', 'qr_logs', $resident['user_id']);

    echo json_encode([
        'success' => true,
        'resident' => [
            'user_id' => (int)$resident['user_id'],
            'name' => trim(($resident['first_name'] ?? '') . ' ' . ($resident['last_name'] ?? '')),
            'is_active' => !empty($resident['is_active']),
        ],
        'total_scans' => $total,
    ]);
    exit;
}

adminHeader('QR Scanner', 'residents');
?>

<div class="bg-white rounded-lg shadow p-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold">Admin QR Scanner</h2>
        <p class="text-sm text-gray-500">Use your device camera to scan resident QR IDs. Scans are recorded in the system.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <div id="qr-reader" class="rounded-lg border border-dashed p-4" style="min-height:320px"></div>
            <div class="mt-3">
                <div id="qr-result" class="text-sm text-gray-700"></div>
            </div>
        </div>
        <div>
            <div class="bg-slate-50 rounded-lg p-4">
                <h3 class="font-semibold mb-2">Manual entry</h3>
                <p class="text-sm text-gray-500 mb-3">If camera is unavailable, paste the QR token below and press Submit.</p>
                <div class="flex gap-2">
                    <input id="manualInput" class="flex-1 border rounded px-3 py-2" placeholder="Paste QR token here">
                    <button id="manualSubmit" class="bg-blue-600 text-white px-4 py-2 rounded">Submit</button>
                </div>
                <div class="mt-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Scan location (optional)</label>
                    <input id="scanLocation" class="w-full border rounded px-3 py-2" placeholder="e.g. Front Desk, Records, Admin Scanner" value="Admin Scanner">
                </div>
                <div class="mt-4 text-xs text-gray-500">Scans will be attributed to your admin account.</div>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode@2.3.7/minified/html5-qrcode.min.js"></script>
<script>
(function () {
    const resultEl = document.getElementById('qr-result');

    function showMessage(html, isError) {
        resultEl.innerHTML = html;
        if (isError) resultEl.classList.add('text-red-600'); else resultEl.classList.remove('text-red-600');
    }

    function postScan(token, location) {
        showMessage('Processing scan...');
        fetch('scan_qr.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ qr_code: token, scan_location: location || (document.getElementById('scanLocation') ? document.getElementById('scanLocation').value : 'Admin Scanner') })
        }).then(r => r.json()).then(data => {
            if (data && data.success) {
                showMessage('<div class="font-semibold text-green-700">' + data.resident.name + ' (ID #' + data.resident.user_id + ') — Scans: ' + data.total_scans + '</div>');
                // Ask for confirmation before opening the profile
                setTimeout(function(){
                    var msg = 'Open profile for ' + data.resident.name + ' (ID #' + data.resident.user_id + ')?';
                    if (confirm(msg)) {
                        window.location.href = 'view_resident.php?id=' + data.resident.user_id;
                    } else {
                        // resume camera scanning if available
                        try { if (window.html5QrcodeScanner && typeof window.html5QrcodeScanner.render === 'function') { window.html5QrcodeScanner.render(onScanSuccess, onScanError); } } catch(e) {}
                    }
                }, 700);
            } else {
                showMessage('<div class="font-semibold">' + (data?.message || 'Not found') + '</div>', true);
            }
        }).catch(err => {
            showMessage('Error sending scan: ' + err.message, true);
        });
    }

    // initialize scanner with named callbacks and global reference so we can resume
    try {
        window.html5QrcodeScanner = new Html5QrcodeScanner('qr-reader', { fps: 10, qrbox: 250 });
        function onScanSuccess(decodedText, decodedResult) {
            // avoid duplicate rapid scans
            if (window._lastScan === decodedText) return;
            window._lastScan = decodedText;
            setTimeout(() => { window._lastScan = null; }, 1500);
            postScan(decodedText, document.getElementById('scanLocation') ? document.getElementById('scanLocation').value : 'Admin Scanner');
            // stop scanning briefly to avoid multiple rapid hits
            try { window.html5QrcodeScanner.clear(); } catch(e) {}
            // restart after 1.5s
            setTimeout(() => {
                try { window.html5QrcodeScanner.render(onScanSuccess, onScanError); } catch(e) {}
            }, 1500);
        }
        function onScanError(error) {
            // ignore scan failures
        }
        window.html5QrcodeScanner.render(onScanSuccess, onScanError);
    } catch (e) {
        showMessage('Camera scanner not available in this browser.', true);
    }

    document.getElementById('manualSubmit').addEventListener('click', function (e) {
        e.preventDefault();
        const v = document.getElementById('manualInput').value.trim();
        if (!v) return showMessage('Please paste a QR token.', true);
        postScan(v, 'Manual Entry');
    });
})();
</script>

<?php adminFooter(); ?>
