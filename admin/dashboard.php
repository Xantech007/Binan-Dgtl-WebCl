<?php
// admin/dashboard.php
require_once __DIR__ . '/inc/header.php';

// Fetch statistics using your REAL table structure
try {
    // 1. Total users
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $total_users = (int) $stmt->fetchColumn();

    // 2. Total deposits – only approved (status = 1 = approved/success)
    $stmt = $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM deposits WHERE status = 1");
    $total_deposits = number_format((float) $stmt->fetchColumn(), 2);

    // 3. Total withdrawals – only approved (status = 1 = approved/paid)
    $stmt = $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM withdrawals WHERE status = 1");
    $total_withdrawals = number_format((float) $stmt->fetchColumn(), 2);

    // 4. Number of ACTIVE VIP plans (from vip table)
    // Assuming status = 1 means active/visible/enabled in your system
    $stmt = $pdo->query("SELECT COUNT(*) FROM vip WHERE status = 1");
    $total_active_vip_plans = (int) $stmt->fetchColumn();

} catch (PDOException $e) {
    // ─── DEBUG OUTPUT ─── (remove or comment out in production after testing)
    echo '<div style="background:#f85149; color:white; padding:1.5rem; border-radius:8px; margin:2rem 0; text-align:center; font-family:monospace;">';
    echo '<strong>Database Query Error:</strong><br>' . htmlspecialchars($e->getMessage()) . '<br>';
    echo '</div>';

    // Fallback values so the page doesn't break
    $total_users = $total_active_vip_plans = 0;
    $total_deposits = $total_withdrawals = "0.00";
}
?>

<main style="margin-top: 1.5rem;">
  <h1 style="text-align:center; margin-bottom:2.5rem; font-size:2.1rem;">
    Dashboard Overview
  </h1>

  <div class="stats-grid">
    <div class="card">
      <div class="card-icon" style="color:#58a6ff;">
        <i class="fas fa-users"></i>
      </div>
      <div class="card-value"><?= number_format($total_users) ?></div>
      <div class="card-label">Total Users</div>
    </div>

    <div class="card">
      <div class="card-icon" style="color:#238636;">
        <i class="fas fa-arrow-down"></i>
      </div>
      <div class="card-value">$<?= htmlspecialchars($total_deposits) ?></div>
      <div class="card-label">Total Deposits</div>
    </div>

    <div class="card">
      <div class="card-icon" style="color:#f85149;">
        <i class="fas fa-arrow-up"></i>
      </div>
      <div class="card-value">$<?= htmlspecialchars($total_withdrawals) ?></div>
      <div class="card-label">Total Withdrawals</div>
    </div>

    <div class="card">
      <div class="card-icon" style="color:#d29922;">
        <i class="fas fa-crown"></i>
      </div>
      <div class="card-value"><?= number_format($total_active_vip_plans) ?></div>
      <div class="card-label">Active VIP</div>
    </div>
  </div>

  <h2 style="text-align:center; margin:3rem 0 1.8rem; font-size:1.7rem;">
    Management Sections
  </h2>

  <div class="actions-grid">
    <a href="manage-users.php"       class="btn"><i class="fas fa-user-friends"></i> Manage Users</a>
    <a href="manage-deposits.php"    class="btn green"><i class="fas fa-wallet"></i> Manage Deposits</a>
    <a href="manage-withdrawals.php" class="btn red"><i class="fas fa-money-bill-wave"></i> Manage Withdrawals</a>
    <a href="manage-payment-methods.php" class="btn"><i class="fas fa-credit-card"></i> Payment Methods</a>
    <a href="region-settings.php"    class="btn"><i class="fas fa-globe"></i> Region Settings</a>
    <a href="manage-vip.php"         class="btn"><i class="fas fa-crown"></i> Manage VIP</a>
    <a href="manage-news.php"        class="btn"><i class="fas fa-newspaper"></i> Manage News</a>
  </div>
</main>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
