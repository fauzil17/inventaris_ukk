<?php
session_start();

// Cek apakah user sudah login
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Redirect ke login jika belum login
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['error'] = 'Silakan login terlebih dahulu!';
        header('Location: /sistem-inventaris/auth/login.php');
        exit();
    }
}

// Cek role user (default semua admin karena tidak ada kolom role)
function isAdmin() {
    return true; // Karena tidak ada kolom role, semua user dianggap admin
}

// Require admin
function requireAdmin() {
    requireLogin();
    // Selalu true karena semua user admin
    return true;
}
?>