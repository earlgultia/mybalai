<?php
require_once '_admin_common.php';

adminHeader('Preview Admin Dashboard', 'dashboard');
?>

<div class="bg-white rounded-lg shadow p-6">
    <p>This is sample content to reproduce header / sidebar interactions. Resize to mobile width to test.</p>
    <div class="mt-4 bg-gray-50 border border-gray-200 p-4" style="height:1200px;">Long content area</div>
</div>

<?php adminFooter(); ?>
