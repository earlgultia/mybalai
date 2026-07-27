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

<div class="space-y-4">
    <div class="md:hidden rounded-[28px] bg-slate-950 text-white p-5 shadow-lg border border-slate-800">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-xs uppercase tracking-[0.24em] text-slate-400">QR Scanner</p>
                <h2 class="mt-2 text-2xl font-semibold">Scan resident IDs</h2>
                <p class="mt-2 text-sm text-slate-300">Use the camera or enter a code manually when the device camera is unavailable.</p>
            </div>
            <div class="inline-flex items-center rounded-full bg-slate-800 px-3 py-1 text-xs font-semibold text-slate-200">Ready</div>
        </div>
        <div class="mt-5 grid gap-3 sm:grid-cols-2">
            <div class="rounded-3xl bg-slate-900 p-4">
                <p class="text-xs uppercase tracking-wide text-slate-500">Quick tip</p>
                <p class="mt-3 text-sm text-slate-300">Keep the QR code centered in the frame and ensure there is enough light.</p>
            </div>
            <div class="rounded-3xl bg-slate-900 p-4">
                <p class="text-xs uppercase tracking-wide text-slate-500">Scan mode</p>
                <p class="mt-3 text-sm text-slate-300">Camera scanning is preferred. Manual input is available below.</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-6">
            <div class="hidden md:flex items-center justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-xl font-semibold text-slate-900">Admin QR Scanner</h2>
                    <p class="mt-1 text-sm text-slate-500">Use your device camera to scan resident QR IDs. Scans are recorded in the system.</p>
                </div>
                <div class="rounded-full bg-sky-50 px-4 py-2 text-sm font-semibold text-sky-700">Scanner ready</div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <div class="bg-slate-50 rounded-3xl border border-dashed border-slate-200 p-4">
                        <div id="qr-reader" class="rounded-xl overflow-hidden" style="min-height:320px"></div>
                        <div class="mt-3 text-sm text-slate-600">
                            <div id="qr-result" class="min-h-[36px]"></div>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="bg-slate-50 rounded-3xl p-4 border border-slate-200">
                        <h3 class="font-semibold text-slate-900 mb-2">Manual entry</h3>
                        <p class="text-sm text-slate-500 mb-3">If camera is unavailable, paste the QR token below and press Submit.</p>
                        <div class="flex gap-2 flex-col sm:flex-row">
                            <input id="manualInput" class="flex-1 border rounded-xl px-3 py-2" placeholder="Paste QR token here">
                            <button id="manualSubmit" class="bg-blue-600 text-white px-4 py-2 rounded-xl hover:bg-blue-700">Submit</button>
                        </div>
                        <div class="mt-3">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Scan location (optional)</label>
                            <input id="scanLocation" class="w-full border rounded-xl px-3 py-2" placeholder="e.g. Front Desk, Records, Admin Scanner" value="Admin Scanner">
                        </div>
                        <div class="mt-4 text-xs text-slate-500">Scans will be attributed to your admin account.</div>
                    </div>
                </div>
            </div>

            <div class="md:hidden rounded-3xl bg-slate-50 border border-slate-200 p-4 mt-4">
                <h3 class="font-semibold text-slate-900">Need help?</h3>
                <p class="mt-2 text-sm text-slate-600">Point the camera at the QR code until the scanner recognizes it. Use manual entry for damaged QR codes.</p>
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
