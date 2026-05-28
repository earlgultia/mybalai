<?php
require_once '_resident_common.php';

$userId = $_SESSION['user_id'];
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    insertSubset('document_requests', [
        'user_id' => $userId,
        'document_type' => sanitize($_POST['document_type']),
        'purpose' => sanitize($_POST['purpose']),
        'status' => 'pending',
        'reference_number' => generateReferenceNumber('DOC'),
        'requested_at' => date('Y-m-d H:i:s'),
        'created_at' => date('Y-m-d H:i:s'),
    ]);
    logActivity($userId, 'Submitted document request', 'document_requests');
    $message = 'Your document request has been submitted.';
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
            <option value="barangay_clearance">Barangay Clearance</option>
            <option value="certificate_of_residency">Certificate of Residency</option>
            <option value="certificate_of_indigency">Certificate of Indigency</option>
            <option value="business_clearance">Business Clearance</option>
            <option value="sedula">Sedula</option>
        </select>
        <textarea name="purpose" required rows="5" placeholder="Purpose of request" class="w-full border rounded-lg px-3 py-2"></textarea>
        <button class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 w-full">Submit Request</button>
    </form>

    <div class="lg:col-span-2 bg-white rounded-xl shadow overflow-hidden">
        <div class="p-6 border-b"><h2 class="text-xl font-semibold text-gray-800">Request History</h2></div>
        <div class="divide-y">
            <?php foreach ($requests as $request): ?>
            <div class="p-5 flex flex-col sm:flex-row sm:justify-between gap-4">
                <div>
                    <h3 class="font-semibold text-gray-900"><?php echo e(labelize($request['document_type'] ?? 'Document')); ?></h3>
                    <p class="text-sm text-gray-600 mt-1"><?php echo e($request['purpose'] ?? ''); ?></p>
                    <p class="text-xs text-gray-400 mt-2">Requested <?php echo !empty($request['requested_at']) ? date('M d, Y g:i A', strtotime($request['requested_at'])) : 'N/A'; ?></p>
                </div>
                <div class="flex items-start sm:items-center">
                    <span class="h-fit px-2 py-1 text-xs rounded-full <?php echo statusBadge($request['status'] ?? 'pending'); ?>"><?php echo e(labelize($request['status'] ?? 'pending')); ?></span>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if (empty($requests)): ?><div class="p-8 text-center text-gray-500">No document requests yet.</div><?php endif; ?>
        </div>
    </div>
</div>
<?php residentFooter(); ?>
