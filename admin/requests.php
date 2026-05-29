<?php
require_once '_admin_common.php';

if (!hasRole(['super_admin', 'barangay_captain', 'barangay_secretary'])) {
    redirect('dashboard.php');
}

ensureDocumentRequestPaymentColumns();

$message = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['request_id'])) {
    $requestId = (int)$_POST['request_id'];
    $status = sanitize($_POST['status'] ?? '');
    $notes = sanitize($_POST['notes'] ?? '');

    $validStatuses = ['approved', 'rejected', 'ready_for_pickup', 'claimed'];
    if (!in_array($status, $validStatuses, true)) {
        $error = 'Invalid request status selected.';
    } else {
        $stmt = $pdo->prepare("SELECT request_id, status, payment_status, payment_method, payment_proof_status FROM document_requests WHERE request_id = ? LIMIT 1");
        $stmt->execute([$requestId]);
        $currentRequest = $stmt->fetch();

        if (!$currentRequest) {
            $error = 'Document request not found.';
        } elseif ($currentRequest['status'] === 'claimed' && $status !== 'claimed') {
            $error = 'Claimed requests cannot be changed anymore.';
        } elseif ($currentRequest['status'] === 'rejected' && $status !== 'rejected') {
            $error = 'Rejected requests cannot be changed anymore.';
        } elseif ($currentRequest['status'] === 'pending' && !in_array($status, ['approved', 'rejected', 'pending'], true)) {
            $error = 'Pending requests can only be approved or rejected.';
        } elseif ($status === 'ready_for_pickup' && $currentRequest['payment_status'] !== 'paid') {
            $error = 'Only paid requests can be marked ready for pickup.';
        } elseif ($status === 'claimed' && $currentRequest['status'] !== 'ready_for_pickup') {
            $error = 'Only ready-for-pickup requests can be marked claimed.';
        } elseif ($status === 'claimed' && $currentRequest['payment_status'] !== 'paid') {
            $error = 'Only paid requests can be marked claimed.';
        } else {
            $updateData = [
                'status' => $status,
                'notes' => $notes,
                'processed_by' => $_SESSION['user_id'],
                'processed_at' => date('Y-m-d H:i:s'),
            ];

            if ($status === 'approved' && $currentRequest['status'] === 'pending') {
                $updateData['approved_by'] = $_SESSION['user_id'];
                $updateData['approved_at'] = date('Y-m-d H:i:s');
            }

            if ($status === 'rejected') {
                $updateData['rejection_reason'] = $notes;
            }

            if (in_array($status, ['ready_for_pickup', 'claimed'], true)) {
                $updateData['pickup_date'] = date('Y-m-d');
            }

            updateSubset('document_requests', $updateData, 'request_id', $requestId);
            logActivity($_SESSION['user_id'], 'Updated document request', 'document_requests', $requestId);
            $message = 'Document request updated successfully.';
        }
    }
}

$stmt = $pdo->query("
    SELECT dr.*, u.first_name, u.last_name, u.email
    FROM document_requests dr
    JOIN users u ON dr.user_id = u.user_id
    ORDER BY dr.requested_at DESC
");
$requests = $stmt->fetchAll();

adminHeader('Document Requests', 'requests');
?>
<?php if ($message): ?>
<div class="bg-green-100 border-l-4 border-green-500 text-green-700 px-4 py-3 rounded mb-4"><?php echo e($message); ?></div>
<?php endif; ?>
<?php if ($error): ?>
<div class="bg-red-100 border-l-4 border-red-500 text-red-700 px-4 py-3 rounded mb-4"><?php echo e($error); ?></div>
<?php endif; ?>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="p-4 border-b">
        <h3 class="text-lg font-semibold text-gray-800">Manage Resident Document Requests</h3>
    </div>
    <div class="overflow-x-auto hidden md:block">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Resident</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Document</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Purpose</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Payment</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Method</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Proof</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Requested</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Update</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php foreach ($requests as $request): ?>
                <?php
                    $currentStatus = (string)($request['status'] ?? 'pending');
                    $currentPaymentStatus = (string)($request['payment_status'] ?? 'unpaid');
                    if ($currentStatus === 'claimed' || $currentStatus === 'rejected') {
                        $statusOptions = [$currentStatus];
                    } elseif ($currentStatus === 'ready_for_pickup') {
                        $statusOptions = ['claimed'];
                    } elseif ($currentPaymentStatus === 'paid') {
                        $statusOptions = ['ready_for_pickup'];
                    } else {
                        $statusOptions = ['approved', 'rejected'];
                    }
                ?>
                <tr>
                    <td class="px-6 py-4">
                        <div class="font-medium text-gray-900"><?php echo e($request['first_name'] . ' ' . $request['last_name']); ?></div>
                        <div class="text-sm text-gray-500"><?php echo e($request['email']); ?></div>
                    </td>
                    <td class="px-6 py-4"><?php echo e(documentTypeLabel($request['document_type'] ?? 'Document')); ?></td>
                    <td class="px-6 py-4 text-sm text-gray-600 max-w-xs"><?php echo e($request['purpose'] ?? 'N/A'); ?></td>
                    <td class="px-6 py-4 font-semibold">₱<?php echo number_format((float)($request['amount'] ?? 0), 2); ?></td>
                    <td class="px-6 py-4"><span class="px-2 py-1 text-xs rounded-full <?php echo statusBadge($request['payment_status'] ?? 'unpaid'); ?>"><?php echo e(labelize($request['payment_status'] ?? 'unpaid')); ?></span></td>
                    <td class="px-6 py-4"><span class="px-2 py-1 text-xs rounded-full <?php echo (($request['payment_method'] ?? 'cash') === 'gcash') ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800'; ?>"><?php echo e(labelize($request['payment_method'] ?? 'cash')); ?></span></td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs rounded-full <?php echo (($request['payment_proof_status'] ?? 'none') === 'submitted') ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800'; ?>"><?php echo e(labelize($request['payment_proof_status'] ?? 'none')); ?></span>
                        <?php if (!empty($request['payment_proof'])): ?>
                            <div class="mt-2"><a href="../<?php echo e($request['payment_proof']); ?>" target="_blank" class="text-xs text-blue-600 hover:underline">View proof</a></div>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 text-sm"><?php echo !empty($request['requested_at']) ? date('M d, Y', strtotime($request['requested_at'])) : 'N/A'; ?></td>
                    <td class="px-6 py-4"><span class="px-2 py-1 text-xs rounded-full <?php echo statusBadge($request['status'] ?? 'pending'); ?>"><?php echo e(labelize($request['status'] ?? 'pending')); ?></span></td>
                    <td class="px-6 py-4">
                        <form method="POST" class="flex flex-wrap gap-2 items-center">
                            <input type="hidden" name="request_id" value="<?php echo (int)$request['request_id']; ?>">
                            <select name="status" class="border rounded px-2 py-1 text-sm">
                                <?php foreach ($statusOptions as $status): ?>
                                <option value="<?php echo $status; ?>" <?php echo ($request['status'] ?? '') == $status ? 'selected' : ''; ?>><?php echo labelize($status); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input type="text" name="notes" placeholder="Remarks" class="border rounded px-2 py-1 text-sm" value="<?php echo e($request['notes'] ?? ''); ?>">
                            <button class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">Save</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($requests)): ?>
                <tr><td colspan="10" class="text-center py-8 text-gray-500">No document requests found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Mobile stacked cards -->
    <div class="md:hidden p-4 space-y-3">
        <?php if (empty($requests)): ?>
            <div class="text-center py-8 text-gray-500">No document requests found.</div>
        <?php endif; ?>
        <?php foreach ($requests as $request): ?>
        <?php
            $currentStatus = (string)($request['status'] ?? 'pending');
            $currentPaymentStatus = (string)($request['payment_status'] ?? 'unpaid');
            if ($currentStatus === 'claimed' || $currentStatus === 'rejected') {
                $statusOptions = [$currentStatus];
            } elseif ($currentStatus === 'ready_for_pickup') {
                $statusOptions = ['claimed'];
            } elseif ($currentPaymentStatus === 'paid') {
                $statusOptions = ['ready_for_pickup'];
            } else {
                $statusOptions = ['approved', 'rejected'];
            }
        ?>
        <div class="bg-white rounded-lg shadow p-3">
            <div class="flex items-start justify-between">
                <div>
                    <div class="font-medium text-gray-900"><?php echo e($request['first_name'] . ' ' . $request['last_name']); ?></div>
                    <div class="text-sm text-gray-500 mb-2"><?php echo e($request['email']); ?></div>
                    <div class="text-sm text-gray-700"><strong>Document:</strong> <?php echo e(documentTypeLabel($request['document_type'] ?? 'Document')); ?></div>
                    <div class="text-sm text-gray-700"><strong>Purpose:</strong> <?php echo e($request['purpose'] ?? 'N/A'); ?></div>
                    <div class="text-sm text-gray-700"><strong>Amount:</strong> ₱<?php echo number_format((float)($request['amount'] ?? 0), 2); ?></div>
                    <div class="text-sm text-gray-700"><strong>Method:</strong> <?php echo e(labelize($request['payment_method'] ?? 'cash')); ?></div>
                    <div class="text-sm text-gray-700"><strong>Proof:</strong> <?php echo e(labelize($request['payment_proof_status'] ?? 'none')); ?></div>
                </div>
                <div class="text-right">
                    <div class="mb-2"><span class="px-2 py-1 text-xs rounded-full <?php echo statusBadge($request['payment_status'] ?? 'unpaid'); ?>"><?php echo e(labelize($request['payment_status'] ?? 'unpaid')); ?></span></div>
                    <div><span class="px-2 py-1 text-xs rounded-full <?php echo statusBadge($request['status'] ?? 'pending'); ?>"><?php echo e(labelize($request['status'] ?? 'pending')); ?></span></div>
                </div>
            </div>
            <div class="mt-3 text-sm text-gray-500">Requested: <?php echo !empty($request['requested_at']) ? date('M d, Y', strtotime($request['requested_at'])) : 'N/A'; ?></div>
            <form method="POST" class="mt-3 space-y-2">
                <input type="hidden" name="request_id" value="<?php echo (int)$request['request_id']; ?>">
                <div>
                    <select name="status" class="w-full border rounded px-2 py-2 text-sm">
                        <?php foreach ($statusOptions as $status): ?>
                        <option value="<?php echo $status; ?>" <?php echo ($request['status'] ?? '') == $status ? 'selected' : ''; ?>><?php echo labelize($status); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <input type="text" name="notes" placeholder="Remarks" class="w-full border rounded px-2 py-2 text-sm" value="<?php echo e($request['notes'] ?? ''); ?>">
                </div>
                <div class="flex">
                    <button class="flex-1 bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700">Save</button>
                </div>
            </form>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php adminFooter(); ?>
