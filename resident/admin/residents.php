<?php
require_once '../../config/database.php';

if (!isLoggedIn() || $_SESSION['user_type'] == 'resident') {
    redirect('../index.php');
}

// Handle resident deletion
if (isset($_GET['delete']) && hasPermission('delete_residents')) {
    $user_id = $_GET['delete'];
    $stmt = $pdo->prepare("UPDATE users SET is_active = 0, deleted_at = NOW() WHERE user_id = ?");
    $stmt->execute([$user_id]);
    logActivity($_SESSION['user_id'], 'Deleted resident', 'users', $user_id);
    redirect('residents.php?msg=deleted');
}

// Get all residents with their profiles
$stmt = $pdo->query("
    SELECT u.*, rp.*, 
           (SELECT COUNT(*) FROM document_requests WHERE user_id = u.user_id) as total_requests,
           (SELECT COUNT(*) FROM complaints WHERE complainant_id = u.user_id) as total_complaints
    FROM users u
    LEFT JOIN resident_profiles rp ON u.user_id = rp.user_id
    JOIN user_role_assignments ura ON ura.user_id = u.user_id AND ura.is_active = 1
    JOIN roles r ON r.role_id = ura.role_id
    WHERE r.role_name = 'resident' AND u.deleted_at IS NULL
    ORDER BY u.created_at DESC
");
$residents = $stmt->fetchAll();

$msg = isset($_GET['msg']) ? $_GET['msg'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Residents Management - MyBalai</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../../assets/css/app.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="admin-shell flex h-screen">
        <!-- Sidebar -->
        <div class="admin-sidebar bg-gradient-to-b from-blue-800 to-indigo-900 text-white fixed h-full overflow-y-auto">
            <div class="p-4 flex flex-col" style="min-height:100%;">
                <div class="flex items-center space-x-2 mb-8">
                    <i class="fas fa-home text-2xl"></i>
                    <h1 class="text-xl font-bold">MyBalai</h1>
                </div>
                <div class="text-sm text-blue-200 mb-6">LATROBE, PA</div>
                
                <nav class="space-y-2">
                    <a href="dashboard.php" class="flex items-center space-x-2 px-4 py-2 hover:bg-blue-700 rounded-lg transition">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="residents.php" class="flex items-center space-x-2 px-4 py-2 bg-blue-700 rounded-lg">
                        <i class="fas fa-users"></i>
                        <span>Residents</span>
                    </a>
                    <a href="requests.php" class="flex items-center space-x-2 px-4 py-2 hover:bg-blue-700 rounded-lg transition">
                        <i class="fas fa-file-alt"></i>
                        <span>Document Requests</span>
                    </a>
                    <a href="complaints.php" class="flex items-center space-x-2 px-4 py-2 hover:bg-blue-700 rounded-lg transition">
                        <i class="fas fa-gavel"></i>
                        <span>Complaints/Blotter</span>
                    </a>
                    <a href="appointments.php" class="flex items-center space-x-2 px-4 py-2 hover:bg-blue-700 rounded-lg transition">
                        <i class="fas fa-calendar-check"></i>
                        <span>Appointments</span>
                    </a>
                    <a href="finance.php" class="flex items-center space-x-2 px-4 py-2 hover:bg-blue-700 rounded-lg transition">
                        <i class="fas fa-coins"></i>
                        <span>Finance</span>
                    </a>
                    <a href="announcements.php" class="flex items-center space-x-2 px-4 py-2 hover:bg-blue-700 rounded-lg transition">
                        <i class="fas fa-bullhorn"></i>
                        <span>Announcements</span>
                    </a>
                    <a href="settings.php" class="flex items-center space-x-2 px-4 py-2 hover:bg-blue-700 rounded-lg transition">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                </nav>

                <div class="admin-user w-full p-4 border-t border-blue-700 mt-auto">
                    <div class="flex items-center space-x-2">
                        <i class="fas fa-user-circle text-2xl"></i>
                        <div class="flex-1">
                            <p class="text-sm font-semibold"><?php echo $_SESSION['user_name']; ?></p>
                            <a href="../logout.php" class="text-xs text-blue-300 hover:text-white">Logout</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="flex-1 overflow-auto">
            <div class="topbar bg-white shadow-sm p-4 sticky top-0 z-10">
                <div class="flex justify-between items-center">
                    <h2 class="text-2xl font-bold text-gray-800">Residents Management</h2>
                    <button onclick="openAddModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                        <i class="fas fa-plus mr-2"></i>Add Resident
                    </button>
                </div>
            </div>
            
            <div class="p-6">
                <?php if ($msg == 'deleted'): ?>
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 px-4 py-3 rounded mb-4">
                        Resident deleted successfully!
                    </div>
                <?php endif; ?>
                
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Resident</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Address</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requests</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($residents as $resident): ?>
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <i class="fas fa-user-circle text-2xl text-gray-400"></i>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?php echo $resident['first_name'] . ' ' . $resident['last_name']; ?>
                                            </div>
                                            <div class="text-sm text-gray-500"><?php echo $resident['email']; ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900"><?php echo $resident['phone_number'] ?: 'N/A'; ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900"><?php echo $resident['house_number'] ? $resident['house_number'] . ' ' . $resident['street_address'] : 'N/A'; ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">Documents: <?php echo $resident['total_requests']; ?></div>
                                    <div class="text-sm text-gray-500">Complaints: <?php echo $resident['total_complaints']; ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $resident['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                        <?php echo $resident['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm font-medium">
                                    <button onclick="viewResident(<?php echo $resident['user_id']; ?>)" class="text-blue-600 hover:text-blue-900 mr-3">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button onclick="editResident(<?php echo $resident['user_id']; ?>)" class="text-green-600 hover:text-green-900 mr-3">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="deleteResident(<?php echo $resident['user_id']; ?>)" class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function viewResident(id) {
            window.location.href = 'view_resident.php?id=' + id;
        }
        
        function editResident(id) {
            window.location.href = 'edit_resident.php?id=' + id;
        }
        
        function deleteResident(id) {
            if (confirm('Are you sure you want to delete this resident?')) {
                window.location.href = 'residents.php?delete=' + id;
            }
        }
        
        function openAddModal() {
            window.location.href = 'add_resident.php';
        }
    </script>
</body>
</html>
