<?php
require_once '_resident_common.php';

$userId = (int)$_SESSION['user_id'];
$user = getUserData($userId) ?: [];

$stmt = $pdo->prepare("SELECT * FROM resident_profiles WHERE user_id = ? LIMIT 1");
$stmt->execute([$userId]);
$profile = $stmt->fetch() ?: [];

function generateResidentQrToken($userId) {
    try {
        return 'MBL-RID-' . $userId . '-' . strtoupper(bin2hex(random_bytes(8)));
    } catch (Throwable $e) {
        return 'MBL-RID-' . $userId . '-' . strtoupper(substr(hash('sha256', uniqid((string)$userId, true)), 0, 16));
    }
}

$qrCreated = false;
if (empty($profile['qr_code'])) {
    $profile['qr_code'] = generateResidentQrToken($userId);
    $qrCreated = true;

    if (!empty($profile['profile_id'])) {
        updateSubset('resident_profiles', ['qr_code' => $profile['qr_code']], 'user_id', $userId);
    } else {
        insertSubset('resident_profiles', [
            'user_id' => $userId,
            'qr_code' => $profile['qr_code'],
        ]);
    }

    logActivity($userId, 'Generated resident QR ID', 'resident_profiles', $profile['profile_id'] ?? $userId);
}

$fullName = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
if ($fullName === '') {
    $fullName = 'Resident';
}

$addressParts = [];
foreach (['house_number', 'street_address', 'barangay', 'city', 'city_municipality', 'province', 'zip_code'] as $field) {
    if (!empty($profile[$field])) {
        $addressParts[] = trim((string)$profile[$field]);
    }
}
$addressLine = $addressParts ? implode(', ', $addressParts) : 'Address not available';
$statusLabel = !empty($user['is_active']) ? 'Active account' : 'Inactive account';
$qrCode = (string)$profile['qr_code'];
$qrCodeJson = json_encode($qrCode, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
$downloadBaseName = 'mybalai-qr-' . $userId;

$scanSummary = [
    'total_scans' => 0,
    'last_scan' => null,
];
$recentScans = [];

if ($qrCode !== '') {
    $stmt = $pdo->prepare("SELECT COUNT(*) AS total_scans, MAX(scan_timestamp) AS last_scan FROM qr_logs WHERE qr_code = ?");
    $stmt->execute([$qrCode]);
    $scanSummary = $stmt->fetch() ?: $scanSummary;

    $stmt = $pdo->prepare("
        SELECT ql.scan_timestamp, ql.scan_location,
               COALESCE(NULLIF(TRIM(CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, ''))), ''), u.email, 'Unknown') AS scanner_name
        FROM qr_logs ql
        LEFT JOIN users u ON u.user_id = ql.scanned_by
        WHERE ql.qr_code = ?
        ORDER BY ql.scan_timestamp DESC
        LIMIT 5
    ");
    $stmt->execute([$qrCode]);
    $recentScans = $stmt->fetchAll();
}

residentHeader('My QR ID', 'qr');
?>

<style>
@media print {
    .no-print {
        display: none !important;
    }
}

.qr-page-shell {
    padding-left: 1rem;
    padding-right: 1rem;
}

@media (min-width: 768px) {
    .qr-page-shell {
        padding-left: 0;
        padding-right: 0;
    }
}

.qr-hero-card {
    background: linear-gradient(135deg, #0f172a 0%, #0f172a 45%, #0f375e 100%);
}

.qr-metric-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

@media (max-width: 768px) {
    #qrCodeCanvas {
        min-height: 220px !important;
    }
    .qr-summary-grid {
        grid-template-columns: 1fr !important;
    }
    .qr-page-shell .no-print {
        width: 100%;
    }
    .no-print .inline-flex {
        width: 100% !important;
        justify-content: center;
    }
    .qr-page-shell .max-w-sm {
        max-width: 100% !important;
    }
    table.min-w-full td,
    table.min-w-full th {
        white-space: normal;
    }
}

@media (max-width: 420px) {
    .qr-page-shell {
        padding-left: 0.5rem;
        padding-right: 0.5rem;
    }
    .qr-page-shell section {
        border-radius: 20px;
    }
    .qr-page-shell .no-print {
        flex-direction: column;
    }
    .qr-page-shell .no-print a,
    .qr-page-shell .no-print button,
    .qr-page-shell #copyTokenButton {
        width: 100% !important;
    }
    #qrCodeCanvas {
        min-height: 200px !important;
    }
}
</style>

<?php if ($qrCreated): ?>
<div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
    Your resident QR ID was created and saved successfully.
</div>
<?php endif; ?>

<div class="qr-page-shell mx-auto max-w-6xl space-y-6 sm:space-y-8">
    <section class="relative overflow-hidden rounded-[28px] qr-hero-card px-5 py-6 text-white shadow-xl sm:px-8 sm:py-8">
        <div class="absolute -right-10 -top-10 h-32 w-32 rounded-full bg-white/10 sm:-right-12 sm:-top-12 sm:h-40 sm:w-40"></div>
        <div class="absolute bottom-0 left-8 h-24 w-24 rounded-full bg-cyan-300/15 sm:left-12 sm:h-28 sm:w-28"></div>
        <div class="relative grid gap-6 lg:grid-cols-[1.4fr_0.8fr] lg:items-end">
            <div>
                <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1.5 text-xs font-semibold text-cyan-100 sm:px-4 sm:py-2 sm:text-sm">
                    <i class="fas fa-qrcode"></i>
                    Resident QR ID
                </span>
                <h1 class="mt-4 text-3xl font-bold tracking-tight sm:text-4xl md:text-5xl"><?php echo e($fullName); ?></h1>
                <p class="mt-4 max-w-2xl text-sm leading-6 text-slate-200 sm:text-base">
                    Present your QR ID to authorized barangay staff to confirm your resident profile, simplify processing, and stay verified.
                </p>
            </div>
            <div class="rounded-[24px] border border-white/10 bg-white/10 p-5 shadow-inner backdrop-blur-sm">
                <p class="text-sm uppercase tracking-[0.3em] text-cyan-100">Account status</p>
                <p class="mt-3 text-2xl font-semibold text-white"><?php echo e($statusLabel); ?></p>
                <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-end">
                    <button type="button" onclick="window.print()" class="inline-flex w-full items-center justify-center rounded-xl bg-cyan-500 px-4 py-3 text-sm font-semibold text-white transition hover:bg-cyan-400 sm:w-auto">
                        <i class="fas fa-print mr-2"></i>
                        Print
                    </button>
                </div>
            </div>
        </div>
    </section>

    <div class="grid gap-6 xl:grid-cols-[1.3fr_0.95fr]">
        <section class="rounded-[24px] bg-white p-5 shadow-md sm:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-slate-900">Your QR Card</h2>
                    <p class="mt-1 text-sm text-slate-500">Use this code for barangay verification and secure access.</p>
                </div>
                <span class="inline-flex items-center justify-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700"><?php echo e($statusLabel); ?></span>
            </div>

            <div class="mt-6 flex flex-col items-center text-center">
                <div class="w-full max-w-md rounded-[26px] border border-slate-200 bg-slate-50 p-4 shadow-inner sm:p-6">
                    <div id="qrCodeCanvas" class="flex min-h-[280px] items-center justify-center rounded-[24px] bg-white p-4 sm:min-h-[340px]">
                        <div class="text-sm text-slate-400">QR code loading...</div>
                    </div>
                </div>

                <div class="no-print mt-5 grid w-full gap-3 sm:grid-cols-2">
                    <a href="#" id="downloadQrImage" target="_blank" rel="noopener" class="inline-flex w-full items-center justify-center rounded-xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-700">
                        <i class="fas fa-up-right-from-square mr-2"></i>
                        Open Image
                    </a>
                    <button type="button" id="copyQrButton" class="inline-flex w-full items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        <i class="fas fa-copy mr-2"></i>
                        Copy Code
                    </button>
                </div>

                <p class="mt-4 max-w-xl text-xs leading-5 text-slate-500 sm:text-sm">
                    Keep this QR code ready when visiting the barangay office or during verification checks.
                </p>
            </div>
        </section>

        <div class="space-y-6">
            <section class="rounded-[24px] bg-white p-5 shadow-md sm:p-6">
                <h2 class="text-xl font-semibold text-slate-900">Resident Details</h2>
                <dl class="mt-5 space-y-4 text-sm text-slate-700">
                    <div class="flex flex-col gap-2 rounded-3xl bg-slate-50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                        <dt class="text-slate-500">Resident Name</dt>
                        <dd class="font-semibold"><?php echo e($fullName); ?></dd>
                    </div>
                    <div class="flex flex-col gap-2 rounded-3xl bg-slate-50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                        <dt class="text-slate-500">Resident ID</dt>
                        <dd class="font-semibold">#<?php echo (int)$userId; ?></dd>
                    </div>
                    <div class="flex flex-col gap-2 rounded-3xl bg-slate-50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                        <dt class="text-slate-500">Email</dt>
                        <dd class="break-words font-semibold"><?php echo e($user['email'] ?? 'Not provided'); ?></dd>
                    </div>
                    <div class="flex flex-col gap-2 rounded-3xl bg-slate-50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                        <dt class="text-slate-500">Phone</dt>
                        <dd class="font-semibold"><?php echo e($user['phone_number'] ?? 'Not provided'); ?></dd>
                    </div>
                    <div class="flex flex-col gap-2 rounded-3xl bg-slate-50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                        <dt class="text-slate-500">Address</dt>
                        <dd class="font-semibold"><?php echo e($addressLine); ?></dd>
                    </div>
                </dl>
            </section>

            <section class="rounded-[24px] bg-white p-5 shadow-md sm:p-6">
                <h2 class="text-xl font-semibold text-slate-900">QR Token</h2>
                <p class="mt-2 text-sm text-slate-500">This token is stored securely in your resident profile.</p>
                <div class="mt-4 flex flex-col gap-3 sm:flex-row">
                    <input id="qrCodeValue" type="text" readonly value="<?php echo e($qrCode); ?>" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 font-mono text-sm text-slate-700">
                    <button type="button" id="copyTokenButton" class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-blue-700">
                        Copy
                    </button>
                </div>
            </section>
        </div>
    </div>

    <section class="rounded-[24px] bg-white p-5 shadow-md sm:p-6">
        <div class="flex flex-col gap-3 border-b border-slate-100 pb-4 md:flex-row md:items-end md:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-slate-900">Scan Activity</h2>
                <p class="mt-1 text-sm text-slate-500">Review the latest QR scans recorded by barangay staff.</p>
            </div>
            <div class="text-sm text-slate-600">
                Total scans: <span class="font-semibold text-slate-900"><?php echo (int)($scanSummary['total_scans'] ?? 0); ?></span>
            </div>
        </div>

        <div class="qr-summary-grid mt-5 grid gap-4 md:grid-cols-2">
            <div class="rounded-[20px] bg-slate-50 p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Last Scan</p>
                <p class="mt-2 text-sm font-semibold text-slate-900">
                    <?php echo !empty($scanSummary['last_scan']) ? e(date('F j, Y g:i A', strtotime($scanSummary['last_scan']))) : 'No scan recorded yet'; ?>
                </p>
            </div>
            <div class="rounded-[20px] bg-slate-50 p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Current Status</p>
                <p class="mt-2 text-sm font-semibold text-slate-900"><?php echo e($statusLabel); ?></p>
            </div>
        </div>

        <?php if (!empty($recentScans)): ?>
        <div class="mt-6 overflow-x-auto rounded-[24px] border border-slate-200">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3 font-semibold">Date & Time</th>
                        <th class="px-4 py-3 font-semibold">Location</th>
                        <th class="px-4 py-3 font-semibold">Scanned By</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    <?php foreach ($recentScans as $scan): ?>
                    <tr>
                        <td class="px-4 py-3 text-slate-700"><?php echo e(date('M d, Y g:i A', strtotime($scan['scan_timestamp']))); ?></td>
                        <td class="px-4 py-3 text-slate-700"><?php echo e($scan['scan_location'] ?: 'Not specified'); ?></td>
                        <td class="px-4 py-3 text-slate-700"><?php echo e($scan['scanner_name']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="mt-6 rounded-[24px] border border-dashed border-slate-200 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">
            No scan history has been recorded for this QR ID yet.
        </div>
        <?php endif; ?>
    </section>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
(function () {
    const qrCodeValue = document.getElementById('qrCodeValue');
    const copyQrButton = document.getElementById('copyQrButton');
    const copyTokenButton = document.getElementById('copyTokenButton');
    const qrHolder = document.getElementById('qrCodeCanvas');
    const downloadQrImage = document.getElementById('downloadQrImage');
    const qrCode = <?php echo $qrCodeJson ?: '""'; ?>;

    function flashButton(button, label) {
        if (!button) {
            return;
        }

        if (!button.dataset.originalHtml) {
            button.dataset.originalHtml = button.innerHTML;
        }

        button.innerHTML = '<i class="fas fa-check mr-2"></i>' + label;
        setTimeout(() => {
            button.innerHTML = button.dataset.originalHtml;
        }, 1500);
    }

    async function copyText() {
        if (!qrCodeValue) {
            return;
        }

        try {
            await navigator.clipboard.writeText(qrCodeValue.value);
        } catch (error) {
            qrCodeValue.select();
            document.execCommand('copy');
        }

        flashButton(copyQrButton, 'Copied');
        flashButton(copyTokenButton, 'Copied');
    }

    if (copyQrButton) {
        copyQrButton.addEventListener('click', copyText);
    }

    if (copyTokenButton) {
        copyTokenButton.addEventListener('click', copyText);
    }

    if (qrHolder && window.QRCode) {
        qrHolder.innerHTML = '';
        // compute size based on container width for responsive QR
        var container = qrHolder.parentElement || qrHolder;
        var maxSize = 340;
        var padding = 32; // account for inner padding
        var available = Math.max(160, Math.min(maxSize, Math.floor(container.clientWidth - padding)));
        function renderQr(size) {
            qrHolder.innerHTML = '';
            try {
            new QRCode(qrHolder, {
                text: qrCode,
                width: size,
                height: size,
                colorDark: '#0f172a',
                colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.H
            });
            } catch (e) {
            // fallback to fixed size
            new QRCode(qrHolder, { text: qrCode, width: 240, height: 240 });
            }
        }

        renderQr(available);

        setTimeout(() => {
            const canvas = qrHolder.querySelector('canvas');
            const image = qrHolder.querySelector('img');
            if (canvas && downloadQrImage) {
                try { downloadQrImage.href = canvas.toDataURL('image/png'); } catch(e) { downloadQrImage.removeAttribute('href'); }
            } else if (image && downloadQrImage) {
                downloadQrImage.href = image.src;
            } else if (downloadQrImage) {
                downloadQrImage.href = 'data:text/plain;charset=utf-8,' + encodeURIComponent(qrCode);
            }
            // also set download filename
            if (downloadQrImage) downloadQrImage.download = '<?php echo $downloadBaseName; ?>.png';
        }, 50);

        window.addEventListener('resize', function () {
            var nextAvailable = Math.max(160, Math.min(maxSize, Math.floor(container.clientWidth - padding)));
            if (nextAvailable !== available) {
                available = nextAvailable;
                renderQr(available);
            }
        });
    } else if (qrHolder) {
        qrHolder.innerHTML = '<div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-6 text-sm text-slate-500">QR preview unavailable.</div>';
    }
})();
</script>

<?php residentFooter(); ?>