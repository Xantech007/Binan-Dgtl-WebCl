<?php
// admin/inc/header.php
session_start();

// Simple protection - redirect if not logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/../../config/database.php'; // adjust path if needed

// Determine current page to hide "Back to Dashboard" on dashboard itself
$current_page = basename($_SERVER['PHP_SELF']);
$is_dashboard = ($current_page === 'dashboard.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>BINANCE DIGITAL - Admin</title>
  
  <!-- Font Awesome 6 -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" 
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" 
        crossorigin="anonymous" referrerpolicy="no-referrer" />

  <style>
    :root {
      --bg: #0d1117;
      --card: #161b22;
      --text: #e6edf3;
      --text-muted: #8b949e;
      --primary: #1e90ff;
      --primary-dark: #1565c0;
      --green: #238636;
      --red: #f85149;
      --border: #30363d;
    }
    * { margin:0; padding:0; box-sizing:border-box; }
    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      background: var(--bg);
      color: var(--text);
      min-height: 100vh;
      padding: 20px;
    }
    .container {
      max-width: 1200px;
      margin: 0 auto;
    }

    /* Header with buttons */
    .admin-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 2.5rem;
      padding-bottom: 1.5rem;
      border-bottom: 1px solid var(--border);
    }

    .logo-area {
      display: flex;
      align-items: center;
      gap: 1rem;
    }

    .logo img {
      width: 70px;
      height: auto;
      border-radius: 10px;
    }

    .header-title {
      font-size: 1.6rem;
      font-weight: 600;
    }

    .header-buttons {
      display: flex;
      gap: 1rem;
    }

    .btn-header {
      display: inline-flex;
      align-items: center;
      gap: 0.6rem;
      padding: 0.7rem 1.3rem;
      border-radius: 8px;
      font-size: 0.95rem;
      font-weight: 500;
      text-decoration: none;
      transition: all 0.2s;
      cursor: pointer;
    }

    .btn-back {
      background: #444c56;
      color: var(--text);
    }
    .btn-back:hover {
      background: #586069;
    }

    .btn-logout {
      background: var(--red);
      color: white;
    }
    .btn-logout:hover {
      background: #d32f2f;
    }

    @media (max-width: 768px) {
      .admin-header {
        flex-direction: column;
        gap: 1.2rem;
        text-align: center;
      }
      .header-buttons {
        width: 100%;
        justify-content: center;
      }
      .btn-header {
        flex: 1;
        justify-content: center;
      }
    }
  </style>
</head>
<body>

<div class="container">

  <header class="admin-header">
    <div class="logo-area">
      <div class="logo">
        <img src="../assets/images/vip.jpg" alt="BINANCE DIGITAL">
      </div>
      <div class="header-title">Admin Panel</div>
    </div>

    <div class="header-buttons">
      <?php if (!$is_dashboard): ?>
        <a href="dashboard.php" class="btn-header btn-back">
          <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
      <?php endif; ?>
      
      <a href="logout.php" class="btn-header btn-logout" onclick="return confirm('Are you sure you want to log out?');">
        <i class="fas fa-sign-out-alt"></i> Logout
      </a>
    </div>
  </header>
