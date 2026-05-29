
    <?php
    require_once '_resident_common.php';

    $user_id = $_SESSION['user_id'];

    // Get resident profile
    $stmt = $pdo->prepare("SELECT * FROM resident_profiles WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $profile = $stmt->fetch();

    // Get user data
    $user = getUserData($user_id);

    // Get document requests
    $stmt = $pdo->prepare("SELECT * FROM document_requests WHERE user_id = ? ORDER BY requested_at DESC LIMIT 5");
    $stmt->execute([$user_id]);
    $documentRequests = $stmt->fetchAll();

    // Get complaints
    $stmt = $pdo->prepare("SELECT * FROM complaints WHERE complainant_id = ? ORDER BY created_at DESC LIMIT 5");
    $stmt->execute([$user_id]);
    $complaints = $stmt->fetchAll();

    // Get appointments
    $stmt = $pdo->prepare("SELECT * FROM appointments WHERE user_id = ? ORDER BY preferred_date ASC LIMIT 5");
    $stmt->execute([$user_id]);
    $appointments = $stmt->fetchAll();

    // Get announcements
    $stmt = $pdo->query("SELECT * FROM announcements WHERE is_active = 1 AND (target_audience = 'all' OR target_audience = 'residents_only') ORDER BY published_date DESC LIMIT 5");
    $announcements = $stmt->fetchAll();

    // Get subscription status
    $stmt = $pdo->prepare("SELECT * FROM subscriptions WHERE user_id = ? ORDER BY due_date DESC LIMIT 1");
    $stmt->execute([$user_id]);
    $subscription = $stmt->fetch();

    // Count statistics
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_requests FROM document_requests WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $totalRequests = $stmt->fetch()['total_requests'];

    $stmt = $pdo->prepare("SELECT COUNT(*) as total_complaints FROM complaints WHERE complainant_id = ?");
    $stmt->execute([$user_id]);
    $totalComplaints = $stmt->fetch()['total_complaints'];

    $stmt = $pdo->prepare("SELECT COUNT(*) as pending_requests FROM document_requests WHERE user_id = ? AND status = 'pending'");
    $stmt->execute([$user_id]);
    $pendingRequests = $stmt->fetch()['pending_requests'];

    residentHeader('Dashboard', 'dashboard');
    ?>

        <!-- Welcome Section -->
        <div class="bg-gradient-to-r from-blue-500 to-indigo-600 rounded-2xl p-8 text-white mb-8">
            <h1 class="text-3xl font-bold mb-2">Welcome back, <?php echo $user['first_name']; ?>!</h1>
            <p class="text-blue-100">Your one-stop portal for barangay services. Request documents, file complaints, and stay updated with community announcements.</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8 min-w-0">
            <div class="bg-white rounded-xl shadow p-6 min-w-0">
                <div class="flex items-center justify-between min-w-0">
                    <div>
                        <p class="text-gray-500 text-sm">Total Documents</p>
                        <p class="text-3xl font-bold text-gray-800"><?php echo $totalRequests; ?></p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-file-alt text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Pending Requests</p>
                        <p class="text-3xl font-bold text-yellow-600"><?php echo $pendingRequests; ?></p>
                    </div>
                    <div class="bg-yellow-100 p-3 rounded-full">
                        <i class="fas fa-clock text-yellow-600 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Total Complaints</p>
                        <p class="text-3xl font-bold text-gray-800"><?php echo $totalComplaints; ?></p>
                    </div>
                    <div class="bg-red-100 p-3 rounded-full">
                        <i class="fas fa-gavel text-red-600 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Subscription Status</p>
                        <p class="text-3xl font-bold <?php echo ($subscription && $subscription['status'] == 'paid') ? 'text-green-600' : 'text-red-600'; ?>">
                            <?php echo ($subscription && $subscription['status'] == 'paid') ? 'Paid' : 'Pending'; ?>
                        </p>
                    </div>
                    <div class="<?php echo ($subscription && $subscription['status'] == 'paid') ? 'bg-green-100' : 'bg-red-100'; ?> p-3 rounded-full">
                        <i class="fas fa-credit-card <?php echo ($subscription && $subscription['status'] == 'paid') ? 'text-green-600' : 'text-red-600'; ?> text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 min-w-0">
            <div class="space-y-8 min-w-0">
                <!-- Quick Actions -->
                <div class="bg-white rounded-xl shadow">
                    <div class="p-6 border-b">
                        <h3 class="text-xl font-semibold text-gray-800">Quick Actions</h3>
                    </div>
                    <div class="p-6 quick-actions">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <button onclick="location.href='requests.php'" class="bg-blue-600 text-white p-4 rounded-lg hover:bg-blue-700 transition text-center min-h-[140px] flex flex-col items-center justify-center gap-3">
                                <i class="fas fa-file-alt text-2xl"></i>
                                <span class="font-semibold">Request Document</span>
                            </button>
                            <button onclick="location.href='complaints.php'" class="bg-red-600 text-white p-4 rounded-lg hover:bg-red-700 transition text-center min-h-[140px] flex flex-col items-center justify-center gap-3">
                                <i class="fas fa-gavel text-2xl"></i>
                                <span class="font-semibold">File Complaint</span>
                            </button>
                            <button onclick="location.href='appointments.php'" class="bg-green-600 text-white p-4 rounded-lg hover:bg-green-700 transition text-center min-h-[140px] flex flex-col items-center justify-center gap-3">
                                <i class="fas fa-calendar-check text-2xl"></i>
                                <span class="font-semibold">Book Appointment</span>
                            </button>
                            <button onclick="location.href='view_qr.php'" class="bg-purple-600 text-white p-4 rounded-lg hover:bg-purple-700 transition text-center min-h-[140px] flex flex-col items-center justify-center gap-3">
                                <i class="fas fa-qrcode text-2xl"></i>
                                <span class="font-semibold">My QR ID</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Subscription/Payment Status -->
                <div class="bg-white rounded-xl shadow">
                    <div class="p-6 border-b">
                        <h3 class="text-xl font-semibold text-gray-800">Subscription & Payments</h3>
                    </div>
                    <div class="p-6">
                        <?php if ($subscription): ?>
                        <div class="bg-gray-50 rounded-lg p-4 mb-4">
                            <div class="flex justify-between items-center mb-2">
                                <span class="font-semibold">Monthly Subscription Fee</span>
                                <span class="text-xl font-bold text-blue-600">₱<?php echo number_format($subscription['amount'], 2); ?></span>
                            </div>
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-500">Due Date: <?php echo date('F j, Y', strtotime($subscription['due_date'])); ?></span>
                                <span class="px-2 py-1 rounded-full text-xs <?php echo $subscription['status'] == 'paid' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                    <?php echo ucfirst($subscription['status']); ?>
                                </span>
                            </div>
                            <?php if ($subscription['status'] != 'paid'): ?>
                            <button class="mt-3 w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition">
                                Pay Now
                            </button>
                            <?php endif; ?>
                        </div>
                        <?php else: ?>
                        <p class="text-gray-500 text-center py-4">No subscription record found</p>
                        <?php endif; ?>
                        
                        <div class="text-center text-sm text-gray-500">
                            <i class="fas fa-credit-card mr-1"></i> Accepting Cash and GCash payments
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2 space-y-8 min-w-0">
                <!-- Recent Document Requests -->
                <div class="bg-white rounded-xl shadow">
                    <div class="p-6 border-b flex justify-between items-center">
                        <h3 class="text-xl font-semibold text-gray-800">Recent Document Requests</h3>
                        <a href="requests.php" class="text-blue-600 text-sm hover:underline">View All</a>
                    </div>
                    <div class="p-6">
                        <?php if (count($documentRequests) > 0): ?>
                            <div class="space-y-3">
                                <?php foreach ($documentRequests as $request): ?>
                                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                    <div>
                                        <p class="font-semibold"><?php echo e(documentTypeLabel($request['document_type'] ?? 'Document')); ?></p>
                                        <p class="text-sm text-gray-500">Requested: <?php echo date('M d, Y', strtotime($request['requested_at'])); ?></p>
                                    </div>
                                    <div>
                                        <span class="px-2 py-1 text-xs rounded-full 
                                            <?php echo $request['status'] == 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                                ($request['status'] == 'approved' ? 'bg-green-100 text-green-800' : 
                                                ($request['status'] == 'ready_for_pickup' ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800')); ?>">
                                            <?php echo str_replace('_', ' ', ucfirst($request['status'])); ?>
                                        </span>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-center text-gray-500 py-8">No document requests yet</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Recent Complaints -->
                <div class="bg-white rounded-xl shadow">
                    <div class="p-6 border-b flex justify-between items-center">
                        <h3 class="text-xl font-semibold text-gray-800">Recent Complaints</h3>
                        <a href="complaints.php" class="text-blue-600 text-sm hover:underline">View All</a>
                    </div>
                    <div class="p-6">
                        <?php if (count($complaints) > 0): ?>
                            <div class="space-y-3">
                                <?php foreach ($complaints as $complaint): ?>
                                <div class="p-3 bg-gray-50 rounded-lg">
                                    <div class="flex justify-between items-start mb-2">
                                        <p class="font-semibold"><?php echo ucfirst($complaint['complaint_type']); ?></p>
                                        <span class="px-2 py-1 text-xs rounded-full 
                                            <?php echo $complaint['status'] == 'submitted' ? 'bg-yellow-100 text-yellow-800' : 
                                                ($complaint['status'] == 'resolved' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'); ?>">
                                            <?php echo ucfirst($complaint['status']); ?>
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-600"><?php echo substr($complaint['description'], 0, 100); ?>...</p>
                                    <p class="text-xs text-gray-400 mt-2">Filed: <?php echo date('M d, Y', strtotime($complaint['created_at'])); ?></p>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-center text-gray-500 py-8">No complaints filed yet</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-8 min-w-0">
            <!-- Upcoming Appointments -->
            <div class="bg-white rounded-xl shadow">
                <div class="p-6 border-b flex justify-between items-center">
                    <h3 class="text-xl font-semibold text-gray-800">Upcoming Appointments</h3>
                    <a href="appointments.php" class="text-blue-600 text-sm hover:underline">View All</a>
                </div>
                <div class="p-6">
                    <?php if (count($appointments) > 0): ?>
                        <div class="space-y-3">
                            <?php foreach ($appointments as $appointment): ?>
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                <div>
                                    <p class="font-semibold"><?php echo str_replace('_', ' ', ucfirst($appointment['appointment_type'])); ?></p>
                                    <p class="text-sm text-gray-500"><?php echo date('M d, Y', strtotime($appointment['preferred_date'])); ?> at <?php echo date('g:i A', strtotime($appointment['preferred_time'])); ?></p>
                                </div>
                                <span class="px-2 py-1 text-xs rounded-full 
                                    <?php echo $appointment['status'] == 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                        ($appointment['status'] == 'confirmed' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'); ?>">
                                    <?php echo ucfirst($appointment['status']); ?>
                                </span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-center text-gray-500 py-8">No upcoming appointments</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Announcements -->
            <div class="bg-white rounded-xl shadow">
                <div class="p-6 border-b">
                    <h3 class="text-xl font-semibold text-gray-800">Latest Announcements</h3>
                </div>
                <div class="p-6">
                    <?php if (count($announcements) > 0): ?>
                        <div class="space-y-4">
                            <?php foreach ($announcements as $announcement): ?>
                            <div class="border-b pb-3 last:border-0">
                                <button type="button" onclick="openAnnouncement(this)" class="w-full text-left group" 
                                    data-title="<?php echo e($announcement['title']); ?>" 
                                    data-content="<?php echo e($announcement['content']); ?>" 
                                    data-date="<?php echo date('M d, Y', strtotime($announcement['published_date'])); ?>" 
                                    data-priority="<?php echo e($announcement['priority']); ?>">
                                    <div class="flex items-center justify-between gap-3 mb-2">
                                        <div class="flex items-center space-x-2">
                                            <?php if ($announcement['priority'] == 'urgent'): ?>
                                                <span class="bg-red-500 text-white text-xs px-2 py-1 rounded">URGENT</span>
                                            <?php elseif ($announcement['priority'] == 'high'): ?>
                                                <span class="bg-orange-500 text-white text-xs px-2 py-1 rounded">HIGH PRIORITY</span>
                                            <?php endif; ?>
                                            <span class="text-xs text-gray-500"><?php echo date('M d, Y', strtotime($announcement['published_date'])); ?></span>
                                        </div>
                                        <span class="text-xs font-semibold text-blue-600 opacity-0 transition group-hover:opacity-100">View full</span>
                                    </div>
                                    <h4 class="font-semibold text-gray-800 mb-1"><?php echo e($announcement['title']); ?></h4>
                                    <p class="text-sm text-gray-600"><?php echo e(substr($announcement['content'], 0, 150)); ?>...</p>
                                </button>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-center text-gray-500 py-8">No announcements available</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            function openAnnouncement(el) {
                var title = el.dataset.title || '';
                var content = el.dataset.content || '';
                var date = el.dataset.date || '';
                var priority = (el.dataset.priority || '').toLowerCase();
                var badge = '';
                if (priority === 'urgent') {
                    badge = '<span class="inline-block mb-3 rounded-full bg-red-500 px-3 py-1 text-xs font-bold text-white">URGENT</span>';
                } else if (priority === 'high') {
                    badge = '<span class="inline-block mb-3 rounded-full bg-orange-500 px-3 py-1 text-xs font-bold text-white">HIGH PRIORITY</span>';
                }

                var html = badge + '<div class="text-sm text-gray-500 mb-3">' + date + '</div>' +
                    '<div style="text-align:left; white-space:pre-line; line-height:1.65; color:#374151;">' +
                    content.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;') +
                    '</div>';

                if (window.Swal) {
                    Swal.fire({
                        title: title,
                        html: html,
                        width: 760,
                        confirmButtonText: 'Close',
                        showCloseButton: true,
                        customClass: {
                            popup: 'rounded-2xl',
                            confirmButton: 'bg-blue-600 px-4 py-2 rounded-lg'
                        }
                    });
                } else {
                    alert(title + '\n\n' + content);
                }
            }
        </script>
        <!-- Announcement popup removed -->
<?php residentFooter(); ?>
