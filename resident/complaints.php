<?php
require_once '_resident_common.php';

$userId = $_SESSION['user_id'];
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    insertSubset('complaints', [
        'complainant_id' => $userId,
        'complaint_type' => sanitize($_POST['complaint_type']),
        'subject' => sanitize($_POST['subject']),
        'description' => sanitize($_POST['description']),
        'incident_date' => $_POST['incident_date'] ?: null,
        'location' => sanitize($_POST['location'] ?? ''),
        'status' => 'submitted',
        'reference_number' => generateReferenceNumber('CMP'),
        'created_at' => date('Y-m-d H:i:s'),
    ]);
    logActivity($userId, 'Filed complaint', 'complaints');
    $message = 'Your complaint has been filed.';
}

$stmt = $pdo->prepare("SELECT * FROM complaints WHERE complainant_id = ? ORDER BY created_at DESC");
$stmt->execute([$userId]);
$complaints = $stmt->fetchAll();

residentHeader('Complaints', 'complaints');
?>
<?php if ($message): ?><div class="bg-green-100 border-l-4 border-green-500 text-green-700 px-4 py-3 rounded mb-6"><?php echo e($message); ?></div><?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <form method="POST" class="bg-white rounded-xl shadow p-6 space-y-4">
        <h2 class="text-xl font-semibold text-gray-800">File Complaint</h2>
        <select name="complaint_type" required class="w-full border rounded-lg px-3 py-2">
            <option value="">Select type</option>
            <option value="noise">Noise</option>
            <option value="dispute">Neighbor Dispute</option>
            <option value="safety">Safety Concern</option>
            <option value="sanitation">Sanitation</option>
            <option value="other">Other</option>
        </select>
        <input type="text" name="subject" required placeholder="Subject" class="w-full border rounded-lg px-3 py-2">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <input type="text" name="location" placeholder="Location" class="w-full border rounded-lg px-3 py-2">
            <input type="date" name="incident_date" class="w-full border rounded-lg px-3 py-2">
        </div>
        <textarea name="description" required rows="6" placeholder="Describe the concern" class="w-full border rounded-lg px-3 py-2"></textarea>
        <button class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 w-full">File Complaint</button>
    </form>

    <div class="lg:col-span-2 bg-white rounded-xl shadow overflow-hidden">
        <div class="p-6 border-b"><h2 class="text-xl font-semibold text-gray-800">Complaint History</h2></div>
        <div class="divide-y">
            <?php foreach ($complaints as $complaint): ?>
            <div class="p-5">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
                    <div>
                        <h3 class="font-semibold text-gray-900"><?php echo e($complaint['subject'] ?? labelize($complaint['complaint_type'] ?? 'Complaint')); ?></h3>
                        <p class="text-sm text-gray-600 mt-1"><?php echo e($complaint['description'] ?? ''); ?></p>
                        <p class="text-xs text-gray-400 mt-2">Filed <?php echo !empty($complaint['created_at']) ? date('M d, Y', strtotime($complaint['created_at'])) : 'N/A'; ?></p>
                    </div>
                    <div class="mt-2 sm:mt-0">
                        <span class="h-fit px-2 py-1 text-xs rounded-full <?php echo statusBadge($complaint['status'] ?? 'submitted'); ?>"><?php echo e(labelize($complaint['status'] ?? 'submitted')); ?></span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if (empty($complaints)): ?><div class="p-8 text-center text-gray-500">No complaints filed yet.</div><?php endif; ?>
        </div>
    </div>
</div>
<?php residentFooter(); ?>
