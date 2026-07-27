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
<?php if ($message): ?>
    <div class="rounded-3xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 shadow-sm mb-6">
        <?php echo e($message); ?>
    </div>
<?php endif; ?>

<div class="space-y-6">
    <section class="overflow-hidden rounded-[32px] bg-gradient-to-r from-slate-900 via-slate-800 to-slate-700 p-6 text-white shadow-xl">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <div class="max-w-2xl">
                <p class="text-sm uppercase tracking-[0.3em] text-slate-300">Community support</p>
                <h1 class="mt-3 text-3xl font-semibold sm:text-4xl">Report an issue to your barangay</h1>
                <p class="mt-4 text-sm leading-6 text-slate-200">Use this form to submit a complaint and track updates clearly from your resident dashboard.</p>
            </div>
            <div class="grid w-full gap-3 sm:grid-cols-2 lg:w-auto">
                <div class="rounded-3xl bg-white/10 p-5 text-slate-100 ring-1 ring-white/10">
                    <p class="text-xs uppercase tracking-[0.25em] text-slate-300">Total complaints</p>
                    <p class="mt-3 text-3xl font-semibold"><?php echo count($complaints); ?></p>
                </div>
                <div class="rounded-3xl bg-white/10 p-5 text-slate-100 ring-1 ring-white/10">
                    <p class="text-xs uppercase tracking-[0.25em] text-slate-300">Most recent</p>
                    <p class="mt-3 text-3xl font-semibold"><?php echo !empty($complaints) ? date('M d, Y', strtotime($complaints[0]['created_at'])) : '—'; ?></p>
                </div>
            </div>
        </div>
    </section>

    <div class="grid gap-6 lg:grid-cols-[minmax(320px,420px)_minmax(0,1fr)]">
        <aside class="space-y-6 lg:sticky lg:top-24">
            <section class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.28em] text-slate-500">File Complaint</p>
                    <h2 class="mt-3 text-2xl font-semibold text-slate-900">Submit a new concern</h2>
                    <p class="mt-3 text-sm text-slate-500">Help the barangay address concerns faster by giving us the details.</p>
                </div>

                <form method="POST" class="mt-6 space-y-5">
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Complaint Type</label>
                        <select name="complaint_type" required class="w-full rounded-3xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 outline-none transition focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
                            <option value="">Select type</option>
                            <option value="noise">Noise</option>
                            <option value="dispute">Neighbor Dispute</option>
                            <option value="safety">Safety Concern</option>
                            <option value="sanitation">Sanitation</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Subject</label>
                        <input type="text" name="subject" required placeholder="Subject" class="w-full rounded-3xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 outline-none transition focus:border-blue-400 focus:ring-2 focus:ring-blue-100" />
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700">Location</label>
                            <input type="text" name="location" placeholder="Location" class="w-full rounded-3xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 outline-none transition focus:border-blue-400 focus:ring-2 focus:ring-blue-100" />
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700">Incident Date</label>
                            <input type="date" name="incident_date" class="w-full rounded-3xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 outline-none transition focus:border-blue-400 focus:ring-2 focus:ring-blue-100" />
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Description</label>
                        <textarea name="description" required rows="5" placeholder="Describe the concern" class="w-full rounded-3xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 outline-none transition focus:border-blue-400 focus:ring-2 focus:ring-blue-100"></textarea>
                    </div>

                    <button class="inline-flex w-full items-center justify-center rounded-3xl bg-red-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-red-500/20 transition hover:bg-red-700">File Complaint</button>
                </form>
            </section>

            <section class="rounded-[32px] border border-slate-200 bg-slate-950/95 p-6 text-slate-100 shadow-2xl shadow-slate-900/20">
                <p class="text-sm uppercase tracking-[0.28em] text-slate-400">Need help?</p>
                <h3 class="mt-3 text-2xl font-semibold text-white">Report safely and securely</h3>
                <p class="mt-3 text-sm leading-6 text-slate-300">All complaints are sent to barangay staff for review. Include details and location to speed up resolution.</p>
            </section>
        </aside>

        <section class="space-y-6">
            <div class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-xl font-semibold text-slate-900">Complaint History</h2>
                        <p class="text-sm text-slate-500">Track past issues and their current status.</p>
                    </div>
                    <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-sm font-semibold text-slate-700">Total <?php echo count($complaints); ?></span>
                </div>
            </div>

            <?php if (empty($complaints)): ?>
                <div class="rounded-[32px] border border-dashed border-slate-300 bg-slate-50 p-10 text-center text-slate-500">
                    <p class="text-lg font-semibold text-slate-900">No complaints filed yet.</p>
                    <p class="mt-2 text-sm">Use the form to submit your first report.</p>
                </div>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($complaints as $complaint): ?>
                        <article class="overflow-hidden rounded-[28px] border border-slate-200 bg-slate-50 shadow-sm transition hover:shadow-md">
                            <div class="px-5 py-5 sm:px-6">
                                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                    <div class="min-w-0">
                                        <p class="text-base font-semibold text-slate-900"><?php echo e($complaint['subject'] ?? labelize($complaint['complaint_type'] ?? 'Complaint')); ?></p>
                                        <p class="mt-2 text-sm text-slate-600"><?php echo e($complaint['description'] ?? ''); ?></p>
                                    </div>
                                    <div class="flex flex-col items-start gap-2 sm:items-end">
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold <?php echo statusBadge($complaint['status'] ?? 'submitted'); ?>"><?php echo e(labelize($complaint['status'] ?? 'submitted')); ?></span>
                                        <p class="text-xs text-slate-400">Filed <?php echo !empty($complaint['created_at']) ? date('M d, Y', strtotime($complaint['created_at'])) : 'N/A'; ?></p>
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
