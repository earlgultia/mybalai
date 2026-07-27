<?php
require_once '_resident_common.php';

$userId = $_SESSION['user_id'];
$message = '';
if (!empty($_SESSION['flash_message'])) {
    $message = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
}
ensureDocumentRequestPaymentColumns();
$treasurerAvailability = getSystemSetting('treasurer_availability', 'in_office');
$treasurerGcashPhone = getSystemSetting('treasurer_gcash_phone', getSystemSetting('contact_phone', ''));
$documentFees = [
    'barangay_clearance' => 150,
    'certificate_of_residency' => 150,
    'certificate_of_indigency' => 100,
    'business_permit' => 200,
    'cedula' => 100,
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_proof_request_id'])) {
    $requestId = (int)$_POST['upload_proof_request_id'];
    $stmt = $pdo->prepare("SELECT request_id, user_id, status, payment_status, payment_method, payment_proof_status FROM document_requests WHERE request_id = ? AND user_id = ? LIMIT 1");
    $stmt->execute([$requestId, $userId]);
    $request = $stmt->fetch();

    if (!$request) {
        $message = 'Document request not found.';
    } elseif ($request['status'] !== 'approved') {
        $message = 'You can only upload proof after the request is approved.';
    } elseif (($request['payment_method'] ?? 'cash') !== 'gcash') {
        $message = 'Proof upload is only available for GCash payments.';
    } elseif (!isset($_FILES['payment_proof']) || (int)$_FILES['payment_proof']['error'] !== UPLOAD_ERR_OK) {
        $message = 'Please choose a valid proof image to upload.';
    } else {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
        $fileType = $_FILES['payment_proof']['type'] ?? '';
        $fileSize = (int)($_FILES['payment_proof']['size'] ?? 0);
        if (!in_array($fileType, $allowedTypes, true)) {
            $message = 'Only JPG, PNG, or WEBP images are allowed.';
        } elseif ($fileSize > 5 * 1024 * 1024) {
            $message = 'Proof image must be 5MB or smaller.';
        } else {
            $uploadDir = __DIR__ . '/../uploads/payment_proofs';
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0777, true);
            }
            $extension = pathinfo($_FILES['payment_proof']['name'] ?? '', PATHINFO_EXTENSION);
            $safeName = 'proof_' . $requestId . '_' . time() . '_' . bin2hex(random_bytes(4)) . ($extension ? '.' . strtolower($extension) : '.jpg');
            $targetPath = $uploadDir . '/' . $safeName;
            if (move_uploaded_file($_FILES['payment_proof']['tmp_name'], $targetPath)) {
                $relativePath = 'uploads/payment_proofs/' . $safeName;
                $update = $pdo->prepare("UPDATE document_requests SET payment_proof = ?, payment_proof_status = 'submitted', payment_proof_submitted_at = NOW(), payment_method = 'gcash' WHERE request_id = ? AND user_id = ?");
                $update->execute([$relativePath, $requestId, $userId]);
                logActivity($userId, 'Submitted payment proof', 'document_requests', $requestId, $relativePath);
                $_SESSION['flash_message'] = 'Payment proof uploaded successfully. The treasurer will review it soon.';
                header('Location: requests.php');
                exit;
            } else {
                $message = 'Unable to upload proof image. Please try again.';
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['document_type'])) {
    $documentType = sanitize($_POST['document_type']);
    if (!array_key_exists($documentType, $documentFees)) {
        $message = 'Please select a valid document type.';
    } else {
    $paymentMethod = sanitize($_POST['payment_method'] ?? 'cash');
    if (!in_array($paymentMethod, ['cash', 'gcash'], true)) {
        $paymentMethod = 'cash';
    }
    insertSubset('document_requests', [
        'user_id' => $userId,
        'document_type' => $documentType,
        'purpose' => sanitize($_POST['purpose']),
        'status' => 'pending',
        'reference_number' => generateReferenceNumber('DOC'),
        'requested_at' => date('Y-m-d H:i:s'),
        'created_at' => date('Y-m-d H:i:s'),
        'amount' => getDocumentRequestFee($documentType),
        'payment_method' => $paymentMethod,
        'payment_proof_status' => 'none',
    ]);
    logActivity($userId, 'Submitted document request', 'document_requests');
    $_SESSION['flash_message'] = 'Your document request has been submitted.';
    header('Location: requests.php');
    exit;
    }
}

$stmt = $pdo->prepare("SELECT * FROM document_requests WHERE user_id = ? ORDER BY requested_at DESC");
$stmt->execute([$userId]);
$requests = $stmt->fetchAll();

residentHeader('Document Requests', 'requests');
?>
<?php if ($message): ?>
    <div class="rounded-3xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 shadow-sm mb-6">
        <?php echo e($message); ?>
    </div>
<?php endif; ?>

<div class="space-y-6">
    <section class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <div class="max-w-2xl">
                <p class="text-sm font-semibold uppercase tracking-[0.3em] text-slate-500">Document Requests</p>
                <h1 class="mt-3 text-3xl font-semibold text-slate-900 sm:text-4xl">Submit a new request</h1>
                <p class="mt-3 text-sm leading-6 text-slate-600">Choose a document type, explain your purpose, and select how you want to pay. You can track all requests in the history panel below.</p>
            </div>
            <div class="grid w-full gap-4 sm:grid-cols-2 lg:w-auto">
                <div class="rounded-3xl bg-slate-950 px-5 py-4 text-white shadow-lg shadow-slate-200/10">
                    <p class="text-sm text-slate-300">Requests filed</p>
                    <p class="mt-3 text-3xl font-semibold"><?php echo count($requests); ?></p>
                </div>
                <div class="rounded-3xl bg-slate-950 px-5 py-4 text-white shadow-lg shadow-slate-200/10">
                    <p class="text-sm text-slate-300">Pending approvals</p>
                    <p class="mt-3 text-3xl font-semibold text-amber-300"><?php echo array_filter($requests, fn($item) => ($item['status'] ?? '') === 'pending') ? count(array_filter($requests, fn($item) => ($item['status'] ?? '') === 'pending')) : 0; ?></p>
                </div>
            </div>
        </div>
    </section>

    <div class="grid gap-6 xl:grid-cols-[minmax(320px,380px)_minmax(0,1fr)]">
        <aside class="space-y-6 lg:sticky lg:top-24">
            <section class="rounded-[32px] border border-slate-200 bg-slate-950/95 p-6 shadow-2xl shadow-slate-900/10">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.28em] text-slate-400">Request Document</p>
                    <h2 class="mt-3 text-2xl font-semibold text-white">New request details</h2>
                    <p class="mt-3 text-sm leading-6 text-slate-400">Submit your document request and choose the payment method that works best for you.</p>
                </div>

                <div class="mt-6 space-y-5">
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-100">Document Type</label>
                        <select name="document_type" required class="w-full rounded-3xl border border-slate-700 bg-slate-900 px-4 py-3 text-slate-100 outline-none transition focus:border-blue-400 focus:ring-2 focus:ring-blue-200/50">
                            <option value="" class="text-slate-400">Select document</option>
                            <option value="barangay_clearance">Barangay Clearance - PHP 150.00</option>
                            <option value="certificate_of_residency">Certificate of Residency - PHP 150.00</option>
                            <option value="certificate_of_indigency">Certificate of Indigency - PHP 100.00</option>
                            <option value="business_permit">Business Clearance - PHP 200.00</option>
                            <option value="cedula">Sedula - PHP 100.00</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-100">Purpose of request</label>
                        <textarea name="purpose" required rows="5" placeholder="Write why you need this document" class="w-full rounded-3xl border border-slate-700 bg-slate-900 px-4 py-3 text-slate-100 outline-none transition focus:border-blue-400 focus:ring-2 focus:ring-blue-200/50"></textarea>
                    </div>

                    <div class="rounded-3xl border border-slate-800 bg-slate-900/90 p-4 text-sm text-slate-300">
                        <p class="font-semibold text-slate-100">Fixed document fees</p>
                        <ul class="mt-3 space-y-1 text-slate-300">
                            <li>Barangay Clearance - PHP 150.00</li>
                            <li>Certificate of Residency - PHP 150.00</li>
                            <li>Certificate of Indigency - PHP 100.00</li>
                            <li>Business Clearance - PHP 200.00</li>
                            <li>Sedula - PHP 100.00</li>
                        </ul>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-100">Preferred Payment Method</label>
                        <select name="payment_method" required class="w-full rounded-3xl border border-slate-700 bg-slate-900 px-4 py-3 text-slate-100 outline-none transition focus:border-blue-400 focus:ring-2 focus:ring-blue-200/50">
                            <option value="cash">Cash</option>
                            <option value="gcash">GCash</option>
                        </select>
                        <p class="mt-2 text-xs text-slate-400">If you choose GCash, you can upload a proof image once your request is approved.</p>
                    </div>

                    <div class="rounded-3xl border border-blue-100 bg-blue-50 p-4 text-sm text-blue-800">
                        <p class="font-semibold text-blue-900">GCash payment details</p>
                        <?php if (!empty($treasurerGcashPhone)): ?>
                            <p class="mt-2">Send your GCash payment to <span class="font-semibold"><?php echo e($treasurerGcashPhone); ?></span>.</p>
                        <?php else: ?>
                            <p class="mt-2">The Treasurer has not added a GCash number yet. Please wait for the Treasurer to post it in Settings.</p>
                        <?php endif; ?>
                        <p class="mt-2 text-xs text-blue-700">The amount is fixed based on the document type you selected.</p>
                    </div>

                    <div id="cashAvailabilityBox" class="rounded-3xl border p-4 text-sm <?php echo $treasurerAvailability === 'in_office' ? 'border-green-100 bg-green-50 text-green-800' : 'border-amber-100 bg-amber-50 text-amber-800'; ?>">
                        <?php if ($treasurerAvailability === 'in_office'): ?>
                            Treasurer is currently in office, so cash payments can be recorded quickly.
                        <?php else: ?>
                            Treasurer is currently away, so cash payments may take longer. GCash may be easier right now.
                        <?php endif; ?>
                    </div>

                    <button class="w-full rounded-3xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-500/20 transition hover:bg-blue-700">Submit Request</button>
                </div>
            </section>

            <section class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.28em] text-slate-500">Quick summary</p>
                        <h3 class="mt-2 text-xl font-semibold text-slate-900">Your request overview</h3>
                    </div>
                    <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-sm font-semibold text-slate-700">Total <?php echo count($requests); ?></span>
                </div>
                <div class="mt-5 grid gap-3 sm:grid-cols-2">
                    <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Pending</p>
                        <p class="mt-3 text-3xl font-semibold text-slate-900"><?php echo count(array_filter($requests, fn($item) => ($item['status'] ?? '') === 'pending')); ?></p>
                    </div>
                    <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Approved</p>
                        <p class="mt-3 text-3xl font-semibold text-slate-900"><?php echo count(array_filter($requests, fn($item) => ($item['status'] ?? '') === 'approved')); ?></p>
                    </div>
                </div>
            </section>
        </aside>

        <section class="space-y-6">
            <div class="rounded-[32px] border border-slate-200 bg-white shadow p-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-xl font-semibold text-slate-900">Request History</h2>
                        <p class="text-sm text-slate-500">Review all submitted requests and payment updates.</p>
                    </div>
                    <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-sm font-semibold text-slate-700">Last updated <?php echo date('M d, Y'); ?></span>
                </div>
            </div>

            <?php if (empty($requests)): ?>
                <div class="rounded-[32px] border border-dashed border-slate-300 bg-slate-50 p-8 text-center text-slate-500">
                    <p class="text-lg font-semibold text-slate-900">No document requests yet.</p>
                    <p class="mt-2 text-sm">Submit a new request using the form to the left.</p>
                </div>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($requests as $request): ?>
                        <article class="overflow-hidden rounded-[28px] border border-slate-200 bg-slate-50 shadow-sm">
                            <div class="px-5 py-5 sm:px-6">
                                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                    <div class="min-w-0">
                                        <p class="text-base font-semibold text-slate-900"><?php echo e(documentTypeLabel($request['document_type'] ?? 'Document')); ?></p>
                                        <p class="mt-1 text-sm text-slate-600"><?php echo e($request['purpose'] ?? ''); ?></p>
                                    </div>
                                    <div class="flex flex-col items-start gap-2 sm:items-end">
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold <?php echo statusBadge($request['status'] ?? 'pending'); ?>"><?php echo e(labelize($request['status'] ?? 'pending')); ?></span>
                                        <span class="text-xs text-slate-500">Payment: <?php echo e(labelize($request['payment_method'] ?? 'cash')); ?></span>
                                    </div>
                                </div>
                                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                    <div class="rounded-3xl bg-white/80 p-4 text-sm text-slate-600">
                                        <p class="text-xs uppercase tracking-[0.28em] text-slate-400">Requested</p>
                                        <p class="mt-2 text-sm text-slate-700"><?php echo !empty($request['requested_at']) ? date('M d, Y g:i A', strtotime($request['requested_at'])) : 'N/A'; ?></p>
                                    </div>
                                    <div class="rounded-3xl bg-white/80 p-4 text-sm text-slate-600">
                                        <p class="text-xs uppercase tracking-[0.28em] text-slate-400">Fee</p>
                                        <p class="mt-2 text-sm text-slate-700"><?php echo peso($request['amount'] ?? 0); ?></p>
                                    </div>
                                </div>

                                <?php if (($request['status'] ?? '') === 'approved' && ($request['payment_status'] ?? 'unpaid') === 'unpaid'): ?>
                                    <div class="mt-5 rounded-[24px] border border-blue-100 bg-blue-50 p-5 text-sm text-blue-800">
                                        <?php if (($request['payment_method'] ?? 'cash') === 'gcash'): ?>
                                            <div class="font-semibold">Upload your GCash proof of payment</div>
                                            <?php if (!empty($treasurerGcashPhone)): ?>
                                                <p class="mt-2">Send payment to <span class="font-semibold"><?php echo e($treasurerGcashPhone); ?></span>.</p>
                                            <?php endif; ?>
                                            <form method="POST" enctype="multipart/form-data" class="mt-3 grid gap-3 sm:grid-cols-[1fr_auto]">
                                                <input type="hidden" name="upload_proof_request_id" value="<?php echo (int)$request['request_id']; ?>">
                                                <input type="file" name="payment_proof" accept="image/*" required class="block w-full rounded-3xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700" />
                                                <button class="rounded-3xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-blue-700">Upload</button>
                                            </form>
                                            <p class="mt-2 text-xs text-blue-700">Accepted JPG, PNG, WEBP. Max 5MB.</p>
                                        <?php else: ?>
                                            <p><?php echo $treasurerAvailability === 'in_office' ? 'Treasurer is available to receive cash payment.' : 'Treasurer is currently away, so cash payments may take longer to record.'; ?></p>
                                        <?php endif; ?>
                                    </div>
                                <?php elseif (($request['payment_proof_status'] ?? 'none') === 'submitted'): ?>
                                    <div class="mt-5 rounded-[24px] border border-emerald-100 bg-emerald-50 p-5 text-sm text-emerald-800">
                                        Your proof has been submitted and is awaiting treasurer verification.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
</div>
<?php residentFooter(); ?>
