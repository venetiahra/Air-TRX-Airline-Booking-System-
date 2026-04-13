<?php
function e(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
function redirect_to(string $url): void { header('Location: ' . $url); exit; }
function flash_set(string $type, string $message): void { $_SESSION['flash'] = ['type'=>$type,'message'=>$message]; }
function flash_get(): ?array { if (!isset($_SESSION['flash'])) return null; $f=$_SESSION['flash']; unset($_SESSION['flash']); return $f; }
function format_money($amount): string { return '₱' . number_format((float)$amount, 2); }
function booking_status_badge(string $status): string { return match (strtolower($status)) { 'confirmed' => 'badge-confirmed', 'cancelled' => 'badge-cancelled', default => 'badge-neutral', }; }
?>