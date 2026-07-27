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
<?php if ($message): ?>
    <div class="rounded-3xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 shadow-sm mb-6">
        <?php echo e($message); ?>
    </div>
<?php endif; ?>

<div class="space-y-6">
    <section class="overflow-hidden rounded-[32px] bg-gradient-to-r from-emerald-700 via-slate-900 to-slate-800 p-6 text-white shadow-[0_30px_80px_rgba(15,23,42,0.2)]">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <div class="max-w-2xl">
                <p class="text-sm uppercase tracking-[0.3em] text-emerald-200/80">Appointments</p>
                <h1 class="mt-3 text-3xl font-semibold sm:text-4xl">Book a barangay visit</h1>
                <p class="mt-4 text-sm leading-6 text-emerald-100/85">Choose a service, pick a convenient date, and submit the details. Your appointment history is right beside the form.</p>
            </div>
            <div class="grid w-full gap-3 sm:grid-cols-2 lg:w-auto">
                <div class="rounded-3xl bg-white/10 p-5 text-slate-100 ring-1 ring-white/10">
                    <p class="text-xs uppercase tracking-[0.25em] text-emerald-200/80">Total bookings</p>
                    <p class="mt-3 text-3xl font-semibold"><?php echo count($appointments); ?></p>
                </div>
                <div class="rounded-3xl bg-white/10 p-5 text-slate-100 ring-1 ring-white/10">
                    <p class="text-xs uppercase tracking-[0.25em] text-emerald-200/80">Next slot</p>
                    <p class="mt-3 text-3xl font-semibold"><?php echo !empty($appointments) ? date('M d, Y', strtotime($appointments[0]['preferred_date'])) : '—'; ?></p>
                </div>
            </div>
        </div>
    </section>

    <div class="grid gap-6 lg:grid-cols-[minmax(320px,420px)_minmax(0,1fr)]">
        <aside class="space-y-6 lg:sticky lg:top-24">
            <section class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.28em] text-slate-500">Book Appointment</p>
                    <h2 class="mt-3 text-2xl font-semibold text-slate-900">Schedule your visit</h2>
                    <p class="mt-3 text-sm text-slate-500">Submit your request, then watch for approval updates in your appointment history.</p>
                </div>

                <form method="POST" class="mt-6 space-y-5">
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Appointment Type</label>
                        <select name="appointment_type" required class="w-full rounded-3xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
                            <option value="">Select type</option>
                            <option value="barangay_captain">Barangay Captain</option>
                            <option value="secretary">Barangay Secretary</option>
                            <option value="treasurer">Treasurer</option>
                            <option value="mediation">Mediation</option>
                            <option value="document_pickup">Document Pickup</option>
                        </select>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700">Preferred Date</label>
                            <input type="date" name="preferred_date" required min="<?php echo date('Y-m-d'); ?>" class="w-full rounded-3xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100" />
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700">Preferred Time</label>
                            <input type="time" name="preferred_time" required class="w-full rounded-3xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100" />
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Purpose</label>
                        <textarea name="purpose" required rows="5" placeholder="Purpose of appointment" class="w-full rounded-3xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100"></textarea>
                    </div>

                    <button class="inline-flex w-full items-center justify-center rounded-3xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-500/20 transition hover:bg-emerald-700">Book Appointment</button>
                </form>
            </section>

            <section class="rounded-[32px] border border-slate-200 bg-slate-950/95 p-6 text-slate-100 shadow-2xl shadow-slate-900/20">
                <p class="text-sm uppercase tracking-[0.28em] text-slate-400">Need support?</p>
                <h3 class="mt-3 text-2xl font-semibold text-white">We’re here to help</h3>
                <p class="mt-3 text-sm leading-6 text-slate-300">If you need assistance with scheduling, include clear details so the barangay can respond quickly.</p>
            </section>
        </aside>

        <section class="space-y-6">
            <div class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-xl font-semibold text-slate-900">Appointment History</h2>
                        <p class="text-sm text-slate-500">View your past and upcoming appointment requests.</p>
                    </div>
                    <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-sm font-semibold text-slate-700">Total <?php echo count($appointments); ?></span>
                </div>
            </div>

            <?php if (empty($appointments)): ?>
                <div class="rounded-[32px] border border-dashed border-slate-300 bg-slate-50 p-10 text-center text-slate-500">
                    <p class="text-lg font-semibold text-slate-900">No appointments yet.</p>
                    <p class="mt-2 text-sm">Book your first appointment using the form.</p>
                </div>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($appointments as $appointment): ?>
                        <article class="overflow-hidden rounded-[28px] border border-slate-200 bg-slate-50 shadow-sm transition hover:shadow-md">
                            <div class="px-5 py-5 sm:px-6">
                                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                    <div class="min-w-0">
                                        <p class="text-base font-semibold text-slate-900"><?php echo e(labelize($appointment['appointment_type'] ?? 'Appointment')); ?></p>
                                        <p class="mt-2 text-sm text-slate-600"><?php echo e($appointment['purpose'] ?? ''); ?></p>
                                    </div>
                                    <div class="flex flex-col items-start gap-2 sm:items-end">
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold <?php echo statusBadge($appointment['status'] ?? 'pending'); ?>"><?php echo e(labelize($appointment['status'] ?? 'pending')); ?></span>
                                        <p class="text-xs text-slate-400"><?php echo !empty($appointment['preferred_date']) ? date('M d, Y', strtotime($appointment['preferred_date'])) : 'N/A'; ?> <?php echo !empty($appointment['preferred_time']) ? date('g:i A', strtotime($appointment['preferred_time'])) : ''; ?></p>
                                    </div>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
</div>
<?php residentFooter(); ?>
