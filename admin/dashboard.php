<?php
// admin/dashboard.php
require_once __DIR__ . '/inc/header.php';

/* UPDATE WHATSAPP NUMBER */
$success = '';

if(isset($_POST['update_whatsapp'])){

    $whatsapp = trim($_POST['whatsapp']);

    try{

        $stmt = $pdo->prepare("UPDATE admins SET whatsapp=? LIMIT 1");
        $stmt->execute([$whatsapp]);

        $success = "WhatsApp number updated successfully.";

    } catch(PDOException $e){

        $success = "Error: " . $e->getMessage();
    }
}

/* FETCH CURRENT WHATSAPP */
$stmt = $pdo->query("SELECT whatsapp FROM admins LIMIT 1");
$currentAdmin = $stmt->fetch(PDO::FETCH_ASSOC);

$currentWhatsapp = $currentAdmin['whatsapp'] ?? '';


// Fetch statistics using your REAL table structure
try {

    // 1. Total users
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $total_users = (int) $stmt->fetchColumn();

    // 2. Total deposits
    $stmt = $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM deposits WHERE status = 1");
    $total_deposits = number_format((float) $stmt->fetchColumn(), 2);

    // 3. Total withdrawals
    $stmt = $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM withdrawals WHERE status = 1");
    $total_withdrawals = number_format((float) $stmt->fetchColumn(), 2);

    // 4. Active VIP plans
    $stmt = $pdo->query("SELECT COUNT(*) FROM vip WHERE status = 1");
    $total_active_vip_plans = (int) $stmt->fetchColumn();

} catch (PDOException $e) {

    echo '<div style="background:#f85149; color:white; padding:1.5rem; border-radius:8px; margin:2rem 0; text-align:center; font-family:monospace;">';
    echo '<strong>Database Query Error:</strong><br>' . htmlspecialchars($e->getMessage()) . '<br>';
    echo '</div>';

    $total_users = $total_active_vip_plans = 0;
    $total_deposits = $total_withdrawals = "0.00";
}
?>

<main style="margin-top: 1.5rem;">

  <h1 style="text-align:center; margin-bottom:2.5rem; font-size:2.1rem;">
    Dashboard Overview
  </h1>

  <!-- WHATSAPP SETTINGS -->
  <div style="
    max-width:500px;
    margin:0 auto 2rem auto;
    background:#161b22;
    padding:1.5rem;
    border-radius:12px;
    border:1px solid #30363d;
  ">

    <h2 style="margin-bottom:1rem; text-align:center;">
      WhatsApp Support Number
    </h2>

    <?php if(!empty($success)): ?>
      <div style="
        background:#238636;
        color:white;
        padding:12px;
        border-radius:8px;
        margin-bottom:1rem;
        text-align:center;
      ">
        <?= htmlspecialchars($success) ?>
      </div>
    <?php endif; ?>

    <form method="POST">

      <input
        type="text"
        name="whatsapp"
        value="<?= htmlspecialchars($currentWhatsapp) ?>"
        placeholder="Enter WhatsApp Number"
        required
        style="
          width:100%;
          padding:14px;
          border-radius:8px;
          border:1px solid #30363d;
          background:#0d1117;
          color:white;
          margin-bottom:1rem;
          font-size:15px;
        "
      >

      <button
        type="submit"
        name="update_whatsapp"
        style="
          width:100%;
          padding:14px;
          border:none;
          border-radius:8px;
          background:#25D366;
          color:white;
          font-size:15px;
          cursor:pointer;
          font-weight:bold;
        "
      >
        Update WhatsApp
      </button>

    </form>

  </div>

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
    <a href="manage-users.php" class="btn"><i class="fas fa-user-friends"></i> Manage Users</a>

    <a href="manage-deposits.php" class="btn green">
      <i class="fas fa-wallet"></i> Manage Deposits
    </a>

    <a href="manage-withdrawals.php" class="btn red">
      <i class="fas fa-money-bill-wave"></i> Manage Withdrawals
    </a>

    <a href="manage-payment-methods.php" class="btn">
      <i class="fas fa-credit-card"></i> Payment Methods
    </a>

    <a href="manage-vip.php" class="btn">
      <i class="fas fa-crown"></i> Manage VIP
    </a>

    <a href="manage-links.php" class="btn">
      <i class="fas fa-link"></i> Manage Links
    </a>

    <a href="manage-news.php" class="btn">
      <i class="fas fa-newspaper"></i> Manage News
    </a>

    <a href="task-reset.php" class="btn">
      <i class="fas fa-clock"></i> Task Reset
    </a>
  </div>

</main>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
