<?php
$hash = '$2y$10$rsUcxCkZu/EFEWKxtksnLuY/Jx.3JfLVL4tLHU8SfoCjBweWIBc2O';
$candidates = ['password','Password123','password123','admin123','Admin123','admin','Admin@123','mybalai','mybalai123','12345678','superadmin','superadmin123','P@ssw0rd','Welcome123','Barangay123'];
foreach ($candidates as $c) {
    if (password_verify($c, $hash)) {
        echo "MATCH:$c\n";
    }
}
?>
