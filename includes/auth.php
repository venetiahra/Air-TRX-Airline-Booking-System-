<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
function is_user_logged_in(): bool { return isset($_SESSION['user_id']); }
function require_user_login(): void { if (!is_user_logged_in()) { $redirect=urlencode($_SERVER['REQUEST_URI'] ?? 'index.php'); header('Location: login.php?redirect=' . $redirect); exit; } }
function is_admin_logged_in(): bool { return isset($_SESSION['admin_id']); }
function require_admin_login(): void { if (!is_admin_logged_in()) { header('Location: login.php'); exit; } }
function current_user_name(): string { return $_SESSION['user_name'] ?? 'Guest'; }
function current_admin_name(): string { return $_SESSION['admin_name'] ?? 'Admin'; }
?>