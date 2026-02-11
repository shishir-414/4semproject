<?php
session_start();

// Database Connection
require_once __DIR__ . '/../config/database.php';

// Helper: Redirect
function redirect($url)
{
    header("Location: $url");
    exit();
}

// Helper: Sanitize Input (XSS Prevention)
function sanitize($input)
{
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Helper: Check if User is Logged In
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

// Helper: Check if User is Admin
function isAdmin()
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Helper: Require Login
function requireLogin()
{
    if (!isLoggedIn()) {
        redirect('/login.php');
    }
}

// Helper: Require Admin
function requireAdmin()
{
    if (!isAdmin()) {
        redirect('/index.php');
    }
}

// Helper: Flash Messages
function setFlashMessage($key, $message, $type = 'success')
{
    $_SESSION['flash'][$key] = ['message' => $message, 'type' => $type];
}

function getFlashMessage($key)
{
    if (isset($_SESSION['flash'][$key])) {
        $msg = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return "<div class='alert alert-{$msg['type']}'>{$msg['message']}</div>";
    }
    return '';
}
?>