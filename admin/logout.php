<?php
require __DIR__ . '/../includes/init.php';

// Keluar hanya lewat POST supaya tidak bisa dipicu dari link sembarangan
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('admin/');
}
csrf_cek();
logout();
redirect('admin/login.php?keluar=1');
