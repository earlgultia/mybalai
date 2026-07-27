<?php
require_once '_admin_common.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    insertSubset('announcements', [
        'title' => sanitize($_POST['title']),
        'content' => sanitize($_POST['content']),
        'priority' => sanitize($_POST['priority']),
        'target_audience' => sanitize($_POST['target_audience']),
        'published_date' => date('Y-m-d H:i:s'),
        'is_active' => isset($_POST['is_active']) ? 1 : 0,
        'created_by' => $_SESSION['user_id'],
        'created_at' => date('Y-m-d H:i:s'),
    ]);
    $message = 'Announcement published.';
}

if (isset($_GET['toggle'])) {
    $announcementId = (int)$_GET['toggle'];
    $stmt = $pdo->prepare("SELECT is_active FROM announcements WHERE announcement_id = ?");
    $stmt->execute([$announcementId]);
    $current = $stmt->fetch();
    if ($current) {
        updateSubset('announcements', ['is_active' => $current['is_active'] ? 0 : 1, 'updated_at' => date('Y-m-d H:i:s')], 'announcement_id', $announcementId);
    }
    redirect('announcements.php');
}

if (isset($_GET['delete'])) {
    $announcementId = (int)$_GET['delete'];
    $stmt = $pdo->prepare("SELECT announcement_id, title FROM announcements WHERE announcement_id = ?");
    $stmt->execute([$announcementId]);
    $announcement = $stmt->fetch();
    if ($announcement) {
        $deleteStmt = $pdo->prepare("DELETE FROM announcements WHERE announcement_id = ?");
        $deleteStmt->execute([$announcementId]);
        logActivity($_SESSION['user_id'], 'Announcement deleted', 'announcement', $announcementId, $announcement['title']);
    }
    redirect('announcements.php');
}

$announcements = $pdo->query("SELECT * FROM announcements ORDER BY published_date DESC")->fetchAll();
$totalAnnouncements = count($announcements);
$activeAnnouncements = 0;
foreach ($announcements as $announcement) {
    if (!empty($announcement['is_active'])) {
        $activeAnnouncements++;
    }
}
$inactiveAnnouncements = $totalAnnouncements - $activeAnnouncements;
adminHeader('Announcements', 'announcements');
?>
<?php if ($message): ?><div class="bg-emerald-100 border-l-4 border-emerald-500 text-emerald-700 px-4 py-3 rounded mb-4"><?php echo e($message); ?></div><?php endif; ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-6 space-y-6">
        <div class="md:hidden rounded-[28px] bg-slate-950 text-white p-5 shadow-lg border border-slate-800">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-[0.24em] text-slate-400">Announcements</p>
                    <h1 class="mt-2 text-2xl font-semibold text-white">Manage public notices</h1>
                    <p class="mt-2 text-sm text-slate-300">Publish resident notices and updates using dedicated mobile cards and fast actions.</p>
                </div>
                <span class="inline-flex items-center rounded-full bg-slate-800 px-3 py-1 text-xs font-semibold text-slate-200">Admin</span>
            </div>
            <div class="mt-5 grid grid-cols-2 gap-3">
                <div class="rounded-3xl bg-slate-900 p-4">
                    <p class="text-xs uppercase tracking-wide text-slate-500">Total</p>
                    <p class="mt-3 text-3xl font-semibold text-white"><?php echo $totalAnnouncements; ?></p>
                </div>
                <div class="rounded-3xl bg-sky-900 p-4">
                    <p class="text-xs uppercase tracking-wide text-sky-300">Active</p>
                    <p class="mt-3 text-3xl font-semibold text-white"><?php echo $activeAnnouncements; ?></p>
                </div>
                <div class="rounded-3xl bg-slate-900 p-4">
                    <p class="text-xs uppercase tracking-wide text-slate-500">Inactive</p>
                    <p class="mt-3 text-3xl font-semibold text-white"><?php echo $inactiveAnnouncements; ?></p>
                </div>
                <div class="rounded-3xl bg-slate-900/90 p-4">
                    <p class="text-xs uppercase tracking-wide text-slate-500">Latest</p>
                    <p class="mt-3 text-lg font-semibold text-white">Quick updates</p>
                </div>
            </div>
        </div>

        <div class="hidden md:flex md:items-center md:justify-between gap-6">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-sky-600">Admin Announcements</p>
                <h1 class="mt-2 text-3xl font-semibold text-slate-900">Manage public notices and updates</h1>
                <p class="mt-3 max-w-2xl text-sm text-slate-600">Publish announcements for residents and admin staff. Use priority tags to highlight urgent news.</p>
            </div>
            <div class="hidden lg:flex flex-wrap gap-3">
                <div class="rounded-3xl bg-white px-4 py-3 shadow-sm ring-1 ring-slate-200">
                    <p class="text-xs uppercase tracking-[0.24em] text-slate-500">Total announcements</p>
                    <p class="mt-2 text-xl font-semibold text-slate-900"><?php echo $totalAnnouncements; ?></p>
                </div>
                <div class="rounded-3xl bg-sky-50 px-4 py-3 shadow-sm ring-1 ring-sky-200">
                    <p class="text-xs uppercase tracking-[0.24em] text-sky-600">Active</p>
                    <p class="mt-2 text-xl font-semibold text-sky-800"><?php echo $activeAnnouncements; ?></p>
                </div>
                <div class="rounded-3xl bg-slate-50 px-4 py-3 shadow-sm ring-1 ring-slate-200">
                    <p class="text-xs uppercase tracking-[0.24em] text-slate-500">Inactive</p>
                    <p class="mt-2 text-xl font-semibold text-slate-900"><?php echo $inactiveAnnouncements; ?></p>
                </div>
            </div>
        </div>

        <div class="hidden md:grid md:grid-cols-3 gap-4">
            <div class="rounded-3xl bg-slate-50 p-4 shadow-sm border border-slate-200">
                <p class="text-xs uppercase tracking-wide text-slate-500">Total announcements</p>
                <p class="mt-3 text-3xl font-semibold text-slate-900"><?php echo $totalAnnouncements; ?></p>
            </div>
            <div class="rounded-3xl bg-sky-50 p-4 shadow-sm border border-sky-200">
                <p class="text-xs uppercase tracking-wide text-sky-600">Active</p>
                <p class="mt-3 text-3xl font-semibold text-slate-900"><?php echo $activeAnnouncements; ?></p>
            </div>
            <div class="rounded-3xl bg-slate-50 p-4 shadow-sm border border-slate-200">
                <p class="text-xs uppercase tracking-wide text-slate-500">Inactive</p>
                <p class="mt-3 text-3xl font-semibold text-slate-900"><?php echo $inactiveAnnouncements; ?></p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-[1.05fr_0.95fr]">
        <form method="POST" class="bg-white rounded-[28px] border border-slate-200 p-6 shadow-sm lg:sticky lg:top-24 lg:self-start">
        <h3 class="text-lg font-semibold">New Announcement</h3>
        <input type="text" name="title" required placeholder="Title" class="w-full border rounded-lg px-3 py-2">
        <textarea name="content" required rows="6" placeholder="Announcement content" class="w-full border rounded-lg px-3 py-2"></textarea>
        <select name="priority" class="w-full border rounded-lg px-3 py-2"><option value="normal">Normal</option><option value="high">High</option><option value="urgent">Urgent</option></select>
        <select name="target_audience" class="w-full border rounded-lg px-3 py-2"><option value="all">All</option><option value="residents_only">Residents Only</option><option value="admins_only">Admins Only</option></select>
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" checked> Active</label>
        <button class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Publish</button>
    </form>

    <div class="bg-white rounded-[28px] border border-slate-200 shadow-sm overflow-hidden">
        <div class="flex flex-col gap-2 border-b border-slate-200 bg-slate-50 p-5 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-slate-900">Published Announcements</h2>
                <p class="text-sm text-slate-500">Review and manage all active and inactive announcements.</p>
            </div>
            <span class="inline-flex rounded-full bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700">Showing <?php echo $totalAnnouncements; ?> announcements</span>
        </div>

        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50 text-slate-600 text-xs uppercase tracking-wide">
                    <tr>
                        <th class="px-6 py-4 text-left">Title</th>
                        <th class="px-6 py-4 text-left">Audience</th>
                        <th class="px-6 py-4 text-left">Priority</th>
                        <th class="px-6 py-4 text-left">Published</th>
                        <th class="px-6 py-4 text-left">Status</th>
                        <th class="px-6 py-4 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    <?php foreach ($announcements as $announcement): ?>
                    <tr class="hover:bg-slate-50 transition-colors duration-150">
                        <td class="px-6 py-4 text-slate-900 font-medium"><?php echo e($announcement['title']); ?></td>
                        <td class="px-6 py-4 text-slate-700"><?php echo e(labelize($announcement['target_audience'] ?? 'all')); ?></td>
                        <td class="px-6 py-4 text-slate-700">
                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold <?php echo ($announcement['priority'] ?? '') == 'urgent' ? 'bg-red-100 text-red-800' : (($announcement['priority'] ?? '') == 'high' ? 'bg-orange-100 text-orange-800' : 'bg-slate-100 text-slate-800'); ?>"><?php echo e(labelize($announcement['priority'] ?? 'normal')); ?></span>
                        </td>
                        <td class="px-6 py-4 text-slate-700"><?php echo !empty($announcement['published_date']) ? date('M d, Y', strtotime($announcement['published_date'])) : 'N/A'; ?></td>
                        <td class="px-6 py-4">
                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold <?php echo !empty($announcement['is_active']) ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'; ?>"><?php echo !empty($announcement['is_active']) ? 'Active' : 'Inactive'; ?></span>
                        </td>
                        <td class="px-6 py-4 space-y-2">
                            <a href="announcements.php?toggle=<?php echo (int)$announcement['announcement_id']; ?>" class="block rounded-2xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"><?php echo !empty($announcement['is_active']) ? 'Deactivate' : 'Activate'; ?></a>
                            <a href="announcements.php?delete=<?php echo (int)$announcement['announcement_id']; ?>" onclick="return confirm('Delete this announcement? This will hide it from residents.');" class="block rounded-2xl border border-red-700 bg-red-50 px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-100">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($announcements)): ?><tr><td colspan="6" class="py-10 text-center text-slate-500">No announcements yet.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="md:hidden space-y-4 p-5">
            <?php foreach ($announcements as $announcement): ?>
            <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900"><?php echo e($announcement['title']); ?></h3>
                        <p class="mt-1 text-sm text-slate-500"><?php echo !empty($announcement['published_date']) ? date('M d, Y g:i A', strtotime($announcement['published_date'])) : 'N/A'; ?> · <?php echo e(labelize($announcement['target_audience'] ?? 'all')); ?></p>
                    </div>
                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold <?php echo ($announcement['priority'] ?? '') == 'urgent' ? 'bg-red-100 text-red-800' : (($announcement['priority'] ?? '') == 'high' ? 'bg-orange-100 text-orange-800' : 'bg-slate-100 text-slate-800'); ?>"><?php echo e(labelize($announcement['priority'] ?? 'normal')); ?></span>
                </div>
                <p class="mt-4 text-sm leading-6 text-slate-600"><?php echo e(mb_strlen($announcement['content'] ?? '') > 240 ? mb_substr($announcement['content'] ?? '', 0, 240) . '...' : ($announcement['content'] ?? '')); ?></p>
                <div class="mt-4 flex flex-col gap-3">
                    <a href="announcements.php?toggle=<?php echo (int)$announcement['announcement_id']; ?>" class="block rounded-2xl border border-slate-300 px-4 py-2 text-center text-sm font-semibold text-slate-700 hover:bg-slate-50"><?php echo !empty($announcement['is_active']) ? 'Deactivate' : 'Activate'; ?></a>
                    <a href="announcements.php?delete=<?php echo (int)$announcement['announcement_id']; ?>" onclick="return confirm('Delete this announcement? This will hide it from residents.');" class="block rounded-2xl border border-red-700 bg-red-50 px-4 py-2 text-center text-sm font-semibold text-red-700 hover:bg-red-100">Delete</a>
                </div>
            </article>
            <?php endforeach; ?>
            <?php if (empty($announcements)): ?><div class="rounded-3xl border border-slate-200 bg-white p-6 text-center text-slate-500">No announcements yet.</div><?php endif; ?>
        </div>
    </div>

        <script>
            (function () {
                document.addEventListener('click', function (e) {
                    if (!e.target) return;
                    if (e.target.classList && e.target.classList.contains('announcement-toggle')) {
                        var id = e.target.dataset.target;
                        var full = document.getElementById('full-' + id);
                        var preview = document.getElementById('preview-' + id);
                        if (!full || !preview) return;
                        if (full.classList.contains('hidden')) {
                            full.classList.remove('hidden');
                            preview.classList.add('hidden');
                            e.target.textContent = 'Show less';
                        } else {
                            full.classList.add('hidden');
                            preview.classList.remove('hidden');
                            e.target.textContent = 'Read more';
                        }
                    }
                });
            })();
        </script>
    </div>
</div>
<?php adminFooter(); ?>
