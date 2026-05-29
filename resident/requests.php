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
<?php if ($message): ?><div class="bg-green-100 border-l-4 border-green-500 text-green-700 px-4 py-3 rounded mb-6"><?php echo e($message); ?></div><?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <form method="POST" class="bg-white rounded-xl shadow p-6 space-y-4">
        <h2 class="text-xl font-semibold text-gray-800">Request Document</h2>
        <select name="document_type" required class="w-full border rounded-lg px-3 py-2">
            <option value="">Select document</option>
            <option value="barangay_clearance">Barangay Clearance - PHP 150.00</option>
            <option value="certificate_of_residency">Certificate of Residency - PHP 150.00</option>
            <option value="certificate_of_indigency">Certificate of Indigency - PHP 100.00</option>
            <option value="business_permit">Business Clearance - PHP 200.00</option>
            <option value="cedula">Sedula - PHP 100.00</option>
        </select>
        <textarea name="purpose" required rows="5" placeholder="Purpose of request" class="w-full border rounded-lg px-3 py-2"></textarea>
        <div class="rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700">
            <p class="font-semibold text-slate-900">Fixed document fees</p>
            <p class="mt-1">Barangay Clearance - PHP 150.00</p>
            <p>Certificate of Residency - PHP 150.00</p>
            <p>Certificate of Indigency - PHP 100.00</p>
            <p>Business Clearance - PHP 200.00</p>
            <p>Sedula - PHP 100.00</p>
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Preferred Payment Method</label>
            <select name="payment_method" required class="w-full border rounded-lg px-3 py-2">
                <option value="cash">Cash</option>
                <option value="gcash">GCash</option>
            </select>
            <p class="mt-2 text-xs text-gray-500">If you choose GCash, you can upload a screenshot of the payment after approval.</p>
        </div>
        <div class="rounded-lg border border-blue-100 bg-blue-50 p-4 text-sm text-blue-800">
            <p class="font-semibold text-blue-900">GCash payment details</p>
            <?php if (!empty($treasurerGcashPhone)): ?>
                <p class="mt-1">Send your GCash payment to: <span class="font-semibold"><?php echo e($treasurerGcashPhone); ?></span></p>
            <?php else: ?>
                <p class="mt-1">The Treasurer has not added a GCash number yet. Please wait for the Treasurer to post it in Settings.</p>
            <?php endif; ?>
            <p class="mt-1 text-xs text-blue-700">The amount is fixed based on the document type you selected.</p>
        </div>
        <div id="cashAvailabilityBox" class="rounded-lg border <?php echo $treasurerAvailability === 'in_office' ? 'border-green-100 bg-green-50 text-green-800' : 'border-amber-100 bg-amber-50 text-amber-800'; ?> p-4 text-sm">
            <?php if ($treasurerAvailability === 'in_office'): ?>
                Treasurer is currently in office, so cash payments can be recorded quickly.
            <?php else: ?>
                Treasurer is currently away, so cash payments may take longer. GCash may be easier right now.
            <?php endif; ?>
        </div>
        <button class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 w-full">Submit Request</button>
    </form>

    <div class="lg:col-span-2 bg-white rounded-xl shadow overflow-hidden">
        <div class="p-6 border-b"><h2 class="text-xl font-semibold text-gray-800">Request History</h2></div>
        <div class="divide-y">
            <?php foreach ($requests as $request): ?>
            <div class="p-5 flex flex-col sm:flex-row sm:justify-between gap-4">
                <div>
                    <h3 class="font-semibold text-gray-900"><?php echo e(documentTypeLabel($request['document_type'] ?? 'Document')); ?></h3>
                    <p class="text-sm text-gray-600 mt-1"><?php echo e($request['purpose'] ?? ''); ?></p>
                    <p class="text-xs text-gray-400 mt-2">Requested <?php echo !empty($request['requested_at']) ? date('M d, Y g:i A', strtotime($request['requested_at'])) : 'N/A'; ?></p>
                    <p class="text-xs mt-2"><span class="font-semibold">Payment:</span> <?php echo e(labelize($request['payment_method'] ?? 'cash')); ?></p>
                </div>
                <div class="flex items-start sm:items-center">
                    <span class="h-fit px-2 py-1 text-xs rounded-full <?php echo statusBadge($request['status'] ?? 'pending'); ?>"><?php echo e(labelize($request['status'] ?? 'pending')); ?></span>
                </div>
            </div>
            <?php if (($request['status'] ?? '') === 'approved' && ($request['payment_status'] ?? 'unpaid') === 'unpaid'): ?>
                <div class="px-5 pb-5">
                    <?php if (($request['payment_method'] ?? 'cash') === 'gcash'): ?>
                        <div class="rounded-lg border border-blue-100 bg-blue-50 p-4">
                            <p class="text-sm font-semibold text-blue-900">Upload your GCash proof of payment</p>
                            <?php if (!empty($treasurerGcashPhone)): ?>
                                <p class="mt-1 text-sm text-blue-800">Send GCash to: <span class="font-semibold"><?php echo e($treasurerGcashPhone); ?></span></p>
                            <?php endif; ?>
                            <form method="POST" enctype="multipart/form-data" class="mt-3 space-y-3">
                                <input type="hidden" name="upload_proof_request_id" value="<?php echo (int)$request['request_id']; ?>">
                                <input type="file" name="payment_proof" accept="image/*" required class="block w-full text-sm text-gray-700">
                                <button class="w-full rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">Upload Proof</button>
                            </form>
                            <p class="mt-2 text-xs text-blue-700">Accepted formats: JPG, PNG, WEBP. Max size: 5MB.</p>
                        </div>
                    <?php else: ?>
                        <div class="rounded-lg border border-amber-100 bg-amber-50 p-4 text-sm text-amber-800">
                            Cash payment selected. <?php echo $treasurerAvailability === 'in_office' ? 'Treasurer is available to receive payment.' : 'Treasurer is currently away, so it may take longer to record your payment.'; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php elseif (($request['payment_proof_status'] ?? 'none') === 'submitted'): ?>
                <div class="px-5 pb-5">
                    <div class="rounded-lg border border-emerald-100 bg-emerald-50 p-4 text-sm text-emerald-800">
                        Your GCash proof has been submitted and is awaiting treasurer verification.
                    </div>
                </div>
            <?php endif; ?>
            <?php endforeach; ?>
            <?php if (empty($requests)): ?><div class="p-8 text-center text-gray-500">No document requests yet.</div><?php endif; ?>
        </div>
    </div>
</div>
<?php residentFooter(); ?>
