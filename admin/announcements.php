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
adminHeader('Announcements', 'announcements');
?>
<?php if ($message): ?><div class="bg-green-100 border-l-4 border-green-500 text-green-700 px-4 py-3 rounded mb-4"><?php echo e($message); ?></div><?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <form method="POST" class="bg-white rounded-lg shadow p-6 space-y-4">
        <h3 class="text-lg font-semibold">New Announcement</h3>
        <input type="text" name="title" required placeholder="Title" class="w-full border rounded-lg px-3 py-2">
        <textarea name="content" required rows="6" placeholder="Announcement content" class="w-full border rounded-lg px-3 py-2"></textarea>
        <select name="priority" class="w-full border rounded-lg px-3 py-2"><option value="normal">Normal</option><option value="high">High</option><option value="urgent">Urgent</option></select>
        <select name="target_audience" class="w-full border rounded-lg px-3 py-2"><option value="all">All</option><option value="residents_only">Residents Only</option><option value="admins_only">Admins Only</option></select>
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" checked> Active</label>
        <button class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Publish</button>
    </form>

    <div class="lg:col-span-2 bg-white rounded-lg shadow">
        <div class="p-4 border-b font-semibold">Published Announcements</div>
        <div class="divide-y">
            <?php foreach ($announcements as $announcement): ?>
            <div class="p-4">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4">
                    <div class="flex-1">
                        <div class="flex items-start justify-between gap-2 mb-2">
                            <div class="flex-1">
                                <h4 class="font-semibold text-gray-900"><?php echo e($announcement['title']); ?></h4>
                                <div class="text-xs text-gray-400 mt-1"><?php echo !empty($announcement['published_date']) ? date('M d, Y g:i A', strtotime($announcement['published_date'])) : 'N/A'; ?> | <?php echo e(labelize($announcement['target_audience'] ?? 'all')); ?></div>
                            </div>
                            <div class="ml-2 flex-shrink-0">
                                <span class="text-xs px-2 py-1 rounded-full <?php echo ($announcement['priority'] ?? '') == 'urgent' ? 'bg-red-100 text-red-800' : (($announcement['priority'] ?? '') == 'high' ? 'bg-orange-100 text-orange-800' : 'bg-gray-100 text-gray-800'); ?>"><?php echo e(labelize($announcement['priority'] ?? 'normal')); ?></span>
                            </div>
                        </div>
                        <?php $fullContent = $announcement['content'] ?? ''; $preview = mb_strlen($fullContent) > 160 ? mb_substr($fullContent, 0, 160) . '...' : $fullContent; ?>
                        <p class="text-sm text-gray-600" id="preview-<?php echo (int)$announcement['announcement_id']; ?>"><?php echo e($preview); ?></p>
                        <?php if (mb_strlen($fullContent) > 160): ?>
                            <p class="text-sm text-gray-600 hidden" id="full-<?php echo (int)$announcement['announcement_id']; ?>"><?php echo e($fullContent); ?></p>
                            <button type="button" class="mt-2 text-blue-600 text-sm announcement-toggle" data-target="<?php echo (int)$announcement['announcement_id']; ?>">Read more</button>
                        <?php endif; ?>
                    </div>
                    <div class="sm:ml-4 mt-3 sm:mt-0 flex-shrink-0">
                        <div class="flex flex-col sm:flex-row gap-2">
                            <a href="announcements.php?toggle=<?php echo (int)$announcement['announcement_id']; ?>" class="block sm:inline-block w-full sm:w-auto text-center px-3 py-2 rounded-lg <?php echo !empty($announcement['is_active']) ? 'border border-red-600 text-red-600' : 'border border-green-600 text-green-600'; ?>"><?php echo !empty($announcement['is_active']) ? 'Deactivate' : 'Activate'; ?></a>
                            <a href="announcements.php?delete=<?php echo (int)$announcement['announcement_id']; ?>" onclick="return confirm('Delete this announcement? This will hide it from residents.');" class="block sm:inline-block w-full sm:w-auto text-center px-3 py-2 rounded-lg border border-red-700 text-red-700 hover:bg-red-50">Delete</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if (empty($announcements)): ?><div class="p-8 text-center text-gray-500">No announcements yet.</div><?php endif; ?>
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
