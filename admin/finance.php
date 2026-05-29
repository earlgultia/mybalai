<?php
require_once '_admin_common.php';

if (!hasRole(['super_admin', 'barangay_captain', 'barangay_treasurer'])) {
    redirect('dashboard.php');
}

ensureDocumentRequestPaymentColumns();
ensureTransactionDocumentTypeColumn();

$message = '';
$error = '';
$selectedRequestId = (int)($_GET['request_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['proof_request_id'])) {
    $requestId = (int)$_POST['proof_request_id'];
    $proofAction = sanitize($_POST['proof_action'] ?? '');

    $stmt = $pdo->prepare("
        SELECT dr.request_id, dr.user_id, dr.reference_number, dr.document_type, dr.amount, dr.status, dr.payment_status,
               dr.payment_method, dr.payment_proof, dr.payment_proof_status,
               u.first_name, u.last_name
        FROM document_requests dr
        JOIN users u ON u.user_id = dr.user_id
        WHERE dr.request_id = ?
        LIMIT 1
    ");
    $stmt->execute([$requestId]);
    $request = $stmt->fetch();

    if (!$request) {
        $error = 'Document request not found.';
    } elseif (($request['payment_method'] ?? '') !== 'gcash') {
        $error = 'This proof review is only for GCash payments.';
    } elseif (($request['payment_proof_status'] ?? 'none') !== 'submitted') {
        $error = 'No submitted proof is waiting for review.';
    } else {
        try {
            if ($proofAction === 'reject') {
                $update = $pdo->prepare("UPDATE document_requests SET payment_proof_status = 'rejected', payment_proof_reviewed_at = NOW(), payment_proof_reviewed_by = ? WHERE request_id = ?");
                $update->execute([$_SESSION['user_id'], $requestId]);
                logActivity($_SESSION['user_id'], 'Rejected payment proof', 'document_requests', $requestId, $request['reference_number'] ?? 'N/A');
                $message = 'Payment proof rejected.';
            } else {
                $pdo->beginTransaction();

                $orNumber = generateReferenceNumber('OR');
                $documentFee = (float)($request['amount'] ?? 0);
                if ($documentFee <= 0) {
                    $documentFee = getDocumentRequestFee($request['document_type'] ?? '');
                }
                $stmt = $pdo->prepare("INSERT INTO transactions (\n                    user_id, transaction_type, document_type, reference_id, amount, payment_method,\n                    payment_reference, or_number, status, collected_by, notes\n                ) VALUES (?, 'document_fee', ?, ?, ?, 'gcash', ?, ?, 'completed', ?, ?)");
                $stmt->execute([
                    (int)$request['user_id'],
                    $request['document_type'] ?? null,
                    $requestId,
                    $documentFee,
                    ($request['reference_number'] ?? 'GCASH') . ' / ' . basename((string)($request['payment_proof'] ?? '')),
                    $orNumber,
                    $_SESSION['user_id'],
                    'GCash proof verified for ' . ($request['reference_number'] ?? 'document request'),
                ]);

                $update = $pdo->prepare("UPDATE document_requests SET payment_status = 'paid', payment_proof_status = 'verified', payment_proof_reviewed_at = NOW(), payment_proof_reviewed_by = ? WHERE request_id = ?");
                $update->execute([$_SESSION['user_id'], $requestId]);
                $pdo->commit();

                logActivity($_SESSION['user_id'], 'Verified payment proof', 'document_requests', $requestId, $request['reference_number'] ?? 'N/A');
                $message = 'GCash proof verified and payment recorded.';
            }
            $selectedRequestId = $requestId;
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = 'Unable to process payment proof.';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_id'])) {
    $requestId = (int)$_POST['request_id'];
    $documentFee = (float)($_POST['document_fee'] ?? 0);
    $paymentMethod = sanitize($_POST['payment_method'] ?? '');
    $paymentReference = sanitize($_POST['payment_reference'] ?? '');
    $notes = sanitize($_POST['notes'] ?? '');

    if ($documentFee <= 0) {
        $error = 'Enter a valid document fee.';
    } elseif (!in_array($paymentMethod, ['cash', 'gcash', 'bank_transfer'], true)) {
        $error = 'Select a valid payment method.';
    } else {
        $stmt = $pdo->prepare("
            SELECT dr.request_id, dr.user_id, dr.reference_number, dr.document_type, dr.amount, dr.status, dr.payment_status,
                   u.first_name, u.last_name
            FROM document_requests dr
            JOIN users u ON u.user_id = dr.user_id
            WHERE dr.request_id = ?
            LIMIT 1
        ");
        $stmt->execute([$requestId]);
        $request = $stmt->fetch();

        if (!$request) {
            $error = 'Document request not found.';
        } elseif ($request['status'] !== 'approved') {
            $error = 'Only approved requests can be paid.';
        } elseif (($request['payment_status'] ?? 'unpaid') === 'paid') {
            $error = 'This request has already been marked as paid.';
        } else {
            try {
                $pdo->beginTransaction();

                    $stmt = $pdo->prepare("UPDATE document_requests SET amount = ? WHERE request_id = ?");
                    $stmt->execute([$documentFee, $requestId]);

                $orNumber = generateReferenceNumber('OR');
                $stmt = $pdo->prepare("
                    INSERT INTO transactions (
                        user_id, transaction_type, document_type, reference_id, amount, payment_method,
                        payment_reference, or_number, status, collected_by, notes
                    ) VALUES (?, 'document_fee', ?, ?, ?, ?, ?, ?, 'completed', ?, ?)
                ");
                $stmt->execute([
                    (int)$request['user_id'],
                    $request['document_type'] ?? null,
                    $requestId,
                    $documentFee,
                    $paymentMethod,
                    $paymentReference !== '' ? $paymentReference : null,
                    $orNumber,
                    $_SESSION['user_id'],
                    $notes !== '' ? $notes : 'Payment for ' . ($request['reference_number'] ?? 'document request'),
                ]);
                $stmt = $pdo->prepare("UPDATE document_requests SET payment_status = 'paid' WHERE request_id = ?");
                $stmt->execute([$requestId]);

                logActivity(
                    $_SESSION['user_id'],
                    'Recorded document payment',
                    'document_requests',
                    $requestId,
                    ($request['first_name'] ?? '') . ' ' . ($request['last_name'] ?? '') . ' - ' . ($request['reference_number'] ?? 'N/A')
                );

                $pdo->commit();
                $message = 'Document payment recorded successfully.';
                $selectedRequestId = $requestId;
            } catch (Exception $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $error = 'Unable to record payment. Please try again.';
            }
        }
    }
}

$stmt = $pdo->query("
    SELECT dr.request_id, dr.user_id, dr.reference_number, dr.document_type, dr.amount, dr.status, dr.payment_status, dr.approved_at,
           u.first_name, u.last_name, u.email
    FROM document_requests dr
    JOIN users u ON u.user_id = dr.user_id
    WHERE dr.status = 'approved' AND dr.payment_status = 'unpaid'
    ORDER BY dr.approved_at ASC, dr.requested_at ASC
");
$paymentQueue = $stmt->fetchAll();

$selectedRequest = null;
foreach ($paymentQueue as $queueRequest) {
    if ((int)$queueRequest['request_id'] === $selectedRequestId) {
        $selectedRequest = $queueRequest;
        break;
    }
}

if ($selectedRequestId > 0 && !$selectedRequest) {
    $stmt = $pdo->prepare("
        SELECT dr.request_id, dr.user_id, dr.reference_number, dr.document_type, dr.amount, dr.status, dr.payment_status, dr.approved_at,
               u.first_name, u.last_name, u.email
        FROM document_requests dr
        JOIN users u ON u.user_id = dr.user_id
        WHERE dr.request_id = ?
        LIMIT 1
    ");
    $stmt->execute([$selectedRequestId]);
    $selectedRequest = $stmt->fetch() ?: null;
}

$stmt = $pdo->query("
    SELECT COUNT(*)
    FROM document_requests
    WHERE status = 'approved' AND payment_status = 'unpaid'
");
$awaitingPayments = (int)$stmt->fetchColumn();

$stmt = $pdo->query("
    SELECT COUNT(*)
    FROM transactions
    WHERE transaction_type = 'document_fee' AND status = 'completed'
");
$documentPaymentsCount = (int)$stmt->fetchColumn();

$stmt = $pdo->query("
    SELECT COALESCE(SUM(amount), 0)
    FROM transactions
    WHERE transaction_type = 'document_fee' AND status = 'completed'
");
$documentPaymentTotal = (float)$stmt->fetchColumn();

$stmt = $pdo->query("
    SELECT t.*, u.first_name, u.last_name, dr.reference_number,
           COALESCE(NULLIF(t.document_type, ''), dr.document_type) AS document_type
    FROM transactions t
    JOIN users u ON u.user_id = t.user_id
    LEFT JOIN document_requests dr ON dr.request_id = t.reference_id AND t.transaction_type = 'document_fee'
    WHERE t.transaction_type = 'document_fee'
    ORDER BY t.transaction_date DESC
    LIMIT 10
");
$recentPayments = $stmt->fetchAll();

adminHeader('Document Payments', 'finance');
?>
<?php if ($message): ?><div class="bg-green-100 border-l-4 border-green-500 text-green-700 px-4 py-3 rounded mb-4"><?php echo e($message); ?></div><?php endif; ?>
<?php if ($error): ?><div class="bg-red-100 border-l-4 border-red-500 text-red-700 px-4 py-3 rounded mb-4"><?php echo e($error); ?></div><?php endif; ?>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow p-6">
        <p class="text-sm text-gray-500">Awaiting Payment</p>
        <p class="text-3xl font-bold text-amber-600"><?php echo number_format($awaitingPayments); ?></p>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <p class="text-sm text-gray-500">Payments Recorded</p>
        <p class="text-3xl font-bold text-green-600"><?php echo number_format($documentPaymentsCount); ?></p>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <p class="text-sm text-gray-500">Total Document Payments</p>
        <p class="text-3xl font-bold text-blue-600"><?php echo peso($documentPaymentTotal); ?></p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-4 border-b flex justify-between items-center">
            <h3 class="text-lg font-semibold">Approved Requests Waiting for Payment</h3>
            <span class="text-sm text-gray-500"><?php echo number_format($awaitingPayments); ?> queue item<?php echo $awaitingPayments === 1 ? '' : 's'; ?></span>
        </div>
        <div class="overflow-x-auto hidden md:block">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Request</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Resident</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php foreach ($paymentQueue as $queueItem): ?>
                    <tr>
                        <td class="px-4 py-3">
                            <p class="font-semibold"><?php echo e(documentTypeLabel($queueItem['document_type'] ?? 'Document')); ?></p>
                            <p class="text-xs text-gray-400"><?php echo e($queueItem['reference_number'] ?? 'N/A'); ?></p>
                        </td>
                        <td class="px-4 py-3"><?php echo e($queueItem['first_name'] . ' ' . $queueItem['last_name']); ?></td>
                        <td class="px-4 py-3 font-semibold"><?php echo peso($queueItem['amount']); ?></td>
                        <td class="px-4 py-3">
                            <a href="finance.php?request_id=<?php echo (int)$queueItem['request_id']; ?>#record-payment" class="inline-flex items-center rounded-lg bg-blue-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">Record Payment</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($paymentQueue)): ?>
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-gray-500">No approved requests are waiting for payment.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile stacked cards for payment queue -->
        <div class="md:hidden p-4 space-y-3">
            <?php if (empty($paymentQueue)): ?>
                <div class="text-center py-8 text-gray-500">No approved requests are waiting for payment.</div>
            <?php endif; ?>
            <?php foreach ($paymentQueue as $queueItem): ?>
            <div class="bg-white rounded-lg shadow p-3">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="font-semibold text-gray-900"><?php echo e(documentTypeLabel($queueItem['document_type'] ?? 'Document')); ?></div>
                        <div class="text-xs text-gray-400"><?php echo e($queueItem['reference_number'] ?? 'N/A'); ?></div>
                        <div class="text-sm text-gray-700 mt-1"><?php echo e($queueItem['first_name'] . ' ' . $queueItem['last_name']); ?></div>
                        <div class="text-sm font-semibold mt-1"><?php echo peso($queueItem['amount']); ?></div>
                    </div>
                    <div class="text-right">
                        <a href="finance.php?request_id=<?php echo (int)$queueItem['request_id']; ?>#record-payment" class="inline-flex items-center rounded-lg bg-blue-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">Record Payment</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <form id="record-payment" method="POST" class="bg-white rounded-lg shadow p-6 space-y-4">
        <h3 class="text-lg font-semibold">Record Document Payment</h3>
        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Approved Request</label>
            <select id="paymentRequestId" name="request_id" required class="w-full border rounded-lg px-3 py-2">
                <option value="">Select approved request</option>
                <?php foreach ($paymentQueue as $queueItem): ?>
                <option value="<?php echo (int)$queueItem['request_id']; ?>" data-amount="<?php echo e(number_format((float)($queueItem['amount'] ?? 0), 2, '.', '')); ?>" <?php echo $selectedRequestId === (int)$queueItem['request_id'] ? 'selected' : ''; ?>><?php echo e($queueItem['reference_number'] . ' - ' . $queueItem['first_name'] . ' ' . $queueItem['last_name'] . ' - ' . peso($queueItem['amount'])); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php if ($selectedRequest): ?>
        <div class="rounded-lg border border-blue-100 bg-blue-50 p-4 text-sm text-blue-900">
            <p class="font-semibold"><?php echo e(documentTypeLabel($selectedRequest['document_type'] ?? 'Document')); ?></p>
            <p>Resident: <?php echo e($selectedRequest['first_name'] . ' ' . $selectedRequest['last_name']); ?></p>
            <p>Reference: <?php echo e($selectedRequest['reference_number'] ?? 'N/A'); ?></p>
            <p>Amount: <?php echo peso($selectedRequest['amount']); ?></p>
        </div>
        <?php endif; ?>
        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Document Fee</label>
            <input id="documentFee" type="number" min="0.01" step="0.01" name="document_fee" required value="<?php echo isset($selectedRequest) ? e(number_format((float)($selectedRequest['amount'] ?? 0), 2, '.', '')) : ''; ?>" placeholder="0.00" class="w-full border rounded-lg px-3 py-2">
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <select name="payment_method" class="border rounded-lg px-3 py-2" required>
                <option value="">Select payment method</option>
                <option value="cash">Cash</option>
                <option value="gcash">GCash</option>
                <option value="bank_transfer">Bank Transfer</option>
            </select>
            <input type="text" name="payment_reference" placeholder="Payment reference or receipt no." class="border rounded-lg px-3 py-2">
        </div>
        <textarea name="notes" rows="3" placeholder="Notes" class="w-full border rounded-lg px-3 py-2"></textarea>
        <button class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-blue-700">Save Payment</button>
    </form>
</div>

<script>
    (function () {
        const requestSelect = document.getElementById('paymentRequestId');
        const feeInput = document.getElementById('documentFee');

        if (!requestSelect || !feeInput) {
            return;
        }

        const syncFee = () => {
            const selectedOption = requestSelect.options[requestSelect.selectedIndex];
            if (selectedOption && selectedOption.dataset.amount) {
                feeInput.value = selectedOption.dataset.amount;
            }
        };

        requestSelect.addEventListener('change', syncFee);
        syncFee();
    })();
</script>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="p-4 border-b font-semibold">Recent Document Payments</div>
    <div class="overflow-x-auto hidden md:block">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Resident</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Request</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Method</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">OR</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <?php foreach ($recentPayments as $payment): ?>
                <tr>
                    <td class="px-4 py-3"><?php echo !empty($payment['transaction_date']) ? date('M d, Y', strtotime($payment['transaction_date'])) : 'N/A'; ?></td>
                    <td class="px-4 py-3"><?php echo e($payment['first_name'] . ' ' . $payment['last_name']); ?></td>
                    <td class="px-4 py-3">
                        <p class="font-semibold"><?php echo e(documentTypeLabel($payment['document_type'] ?? 'Document')); ?></p>
                        <p class="text-xs text-gray-400"><?php echo e($payment['reference_number'] ?? 'N/A'); ?></p>
                    </td>
                    <td class="px-4 py-3 font-semibold"><?php echo peso($payment['amount']); ?></td>
                    <td class="px-4 py-3"><?php echo e(labelize($payment['payment_method'] ?? '')); ?></td>
                    <td class="px-4 py-3 text-sm text-gray-500"><?php echo e($payment['or_number'] ?? 'N/A'); ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($recentPayments)): ?>
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-gray-500">No document payments recorded yet.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Mobile stacked cards for recent payments -->
    <div class="md:hidden p-4 space-y-3">
        <?php if (empty($recentPayments)): ?>
            <div class="text-center py-8 text-gray-500">No document payments recorded yet.</div>
        <?php endif; ?>
        <?php foreach ($recentPayments as $payment): ?>
        <div class="bg-white rounded-lg shadow p-3">
            <div class="flex items-start justify-between">
                <div>
                    <div class="text-sm text-gray-700"><?php echo !empty($payment['transaction_date']) ? date('M d, Y', strtotime($payment['transaction_date'])) : 'N/A'; ?></div>
                    <div class="font-medium text-gray-900"><?php echo e($payment['first_name'] . ' ' . $payment['last_name']); ?></div>
                    <div class="text-sm text-gray-700"><?php echo e(documentTypeLabel($payment['document_type'] ?? 'Document')); ?> <span class="text-xs text-gray-400"><?php echo e($payment['reference_number'] ?? 'N/A'); ?></span></div>
                </div>
                <div class="text-right">
                    <div class="font-semibold"><?php echo peso($payment['amount']); ?></div>
                    <div class="text-sm text-gray-500"><?php echo e(labelize($payment['payment_method'] ?? '')); ?></div>
                    <div class="text-xs text-gray-500"><?php echo e($payment['or_number'] ?? 'N/A'); ?></div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php adminFooter(); ?>


