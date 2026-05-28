<?php
require_once '_resident_common.php';

$userId = $_SESSION['user_id'];
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    insertSubset('appointments', [
        'user_id' => $userId,
        'appointment_type' => sanitize($_POST['appointment_type']),
        'purpose' => sanitize($_POST['purpose']),
        'preferred_date' => $_POST['preferred_date'],
        'preferred_time' => $_POST['preferred_time'],
        'status' => 'pending',
        'reference_number' => generateReferenceNumber('APT'),
        'created_at' => date('Y-m-d H:i:s'),
    ]);
    logActivity($userId, 'Booked appointment', 'appointments');
    $message = 'Your appointment request has been submitted.';
}

$stmt = $pdo->prepare("SELECT * FROM appointments WHERE user_id = ? ORDER BY preferred_date DESC, preferred_time DESC");
$stmt->execute([$userId]);
$appointments = $stmt->fetchAll();

residentHeader('Appointments', 'appointments');
?>
<?php if ($message): ?><div class="bg-green-100 border-l-4 border-green-500 text-green-700 px-4 py-3 rounded mb-6"><?php echo e($message); ?></div><?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <form method="POST" class="bg-white rounded-xl shadow p-6 space-y-4">
        <h2 class="text-xl font-semibold text-gray-800">Book Appointment</h2>
        <select name="appointment_type" required class="w-full border rounded-lg px-3 py-2">
            <option value="">Select type</option>
            <option value="barangay_captain">Barangay Captain</option>
            <option value="secretary">Barangay Secretary</option>
            <option value="treasurer">Treasurer</option>
            <option value="mediation">Mediation</option>
            <option value="document_pickup">Document Pickup</option>
        </select>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <input type="date" name="preferred_date" required min="<?php echo date('Y-m-d'); ?>" class="border rounded-lg px-3 py-2 w-full">
            <input type="time" name="preferred_time" required class="border rounded-lg px-3 py-2 w-full">
        </div>
        <textarea name="purpose" required rows="5" placeholder="Purpose of appointment" class="w-full border rounded-lg px-3 py-2"></textarea>
        <button class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 w-full">Book Appointment</button>
    </form>

    <div class="lg:col-span-2 bg-white rounded-xl shadow overflow-hidden">
        <div class="p-6 border-b"><h2 class="text-xl font-semibold text-gray-800">Appointment History</h2></div>
        <div class="divide-y">
            <?php foreach ($appointments as $appointment): ?>
            <div class="p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h3 class="font-semibold text-gray-900"><?php echo e(labelize($appointment['appointment_type'] ?? 'Appointment')); ?></h3>
                    <p class="text-sm text-gray-600 mt-1"><?php echo e($appointment['purpose'] ?? ''); ?></p>
                    <p class="text-xs text-gray-400 mt-2"><?php echo !empty($appointment['preferred_date']) ? date('M d, Y', strtotime($appointment['preferred_date'])) : 'N/A'; ?> <?php echo !empty($appointment['preferred_time']) ? date('g:i A', strtotime($appointment['preferred_time'])) : ''; ?></p>
                </div>
                <div class="flex items-start sm:items-center">
                    <span class="h-fit px-2 py-1 text-xs rounded-full <?php echo statusBadge($appointment['status'] ?? 'pending'); ?>"><?php echo e(labelize($appointment['status'] ?? 'pending')); ?></span>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if (empty($appointments)): ?><div class="p-8 text-center text-gray-500">No appointments yet.</div><?php endif; ?>
        </div>
    </div>
</div>
<?php residentFooter(); ?>
