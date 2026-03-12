<?php
// admin/manage-withdrawals.php
require_once __DIR__ . '/inc/header.php';

$message = '';
$error   = '';

// Handle approve / reject actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $withdrawal_id = (int)($_POST['withdrawal_id'] ?? 0);
    $action        = $_POST['action'] ?? '';

    if ($withdrawal_id <= 0 || !in_array($action, ['approve', 'reject'])) {
        $error = "Invalid request.";
    } else {
        try {
            $pdo->beginTransaction();

            // Get withdrawal info — only pending ones
            $stmt = $pdo->prepare("
                SELECT user_id, amount, status 
                FROM withdrawals 
                WHERE id = ? AND status = 0
                LIMIT 1
            ");
            $stmt->execute([$withdrawal_id]);
            $wd = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$wd) {
                throw new Exception("Withdrawal not found or already processed.");
            }

            if ($action === 'approve') {
                // Subtract from user's withdrawal_balance
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET withdrawal_balance = withdrawal_balance - ? 
                    WHERE id = ? AND withdrawal_balance >= ?
                ");
                $success = $stmt->execute([$wd['amount'], $wd['user_id'], $wd['amount']]);

                if (!$success || $stmt->rowCount() === 0) {
                    throw new Exception("Insufficient withdrawal balance or user not found.");
                }

                // Mark as approved
                $stmt = $pdo->prepare("
                    UPDATE withdrawals 
                    SET status = 1 
                    WHERE id = ?
                ");
                $stmt->execute([$withdrawal_id]);

                $message = "Withdrawal #{$withdrawal_id} approved successfully.";
            } 
            else { // reject / delete
                // Mark as rejected (status = 2)
                $stmt = $pdo->prepare("
                    UPDATE withdrawals 
                    SET status = 2 
                    WHERE id = ?
                ");
                $stmt->execute([$withdrawal_id]);

                $message = "Withdrawal #{$withdrawal_id} rejected.";
            }

            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Operation failed: " . $e->getMessage();
        }
    }
}

// Fetch pending withdrawals
try {
    $stmt = $pdo->query("
        SELECT 
            w.id, w.user_id, w.amount, w.method, w.address, w.fee, w.received,
            w.created_at, w.status,
            u.email, u.phone
        FROM withdrawals w
        LEFT JOIN users u ON w.user_id = u.id
        WHERE w.status = 0
        ORDER BY w.created_at DESC
    ");
    $withdrawals = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Failed to load withdrawals: " . $e->getMessage();
    $withdrawals = [];
}
?>

<main>
  <h1 style="text-align:center; margin: 2.5rem 0 2rem;">Manage Pending Withdrawals</h1>

  <?php if ($message): ?>
    <div style="background:#238636; color:white; padding:1.2rem; border-radius:8px; margin-bottom:2rem; text-align:center; max-width:1100px; margin-left:auto; margin-right:auto;">
      <?= htmlspecialchars($message) ?>
    </div>
  <?php endif; ?>

  <?php if ($error): ?>
    <div style="background:#f85149; color:white; padding:1.2rem; border-radius:8px; margin-bottom:2rem; text-align:center; max-width:1100px; margin-left:auto; margin-right:auto;">
      <?= htmlspecialchars($error) ?>
    </div>
  <?php endif; ?>

  <?php if (empty($withdrawals)): ?>
    <p style="text-align:center; color:var(--text-muted); font-size:1.15rem; padding:4rem 1rem;">
      No pending withdrawal requests at the moment.
    </p>
  <?php else: ?>

  <div style="overflow-x:auto; margin: 0 auto; max-width: 100%;">
    <table style="
      width:100%; 
      max-width: 1200px; 
      margin: 0 auto 3rem; 
      border-collapse: separate; 
      border-spacing: 0 12px;
    ">
      <thead>
        <tr style="background:#1f2937; color:#e6edf3;">
          <th style="padding:1.1rem 0.9rem; border-top-left-radius:8px;">ID</th>
          <th style="padding:1.1rem 0.9rem;">User</th>
          <th style="padding:1.1rem 0.9rem;">Amount</th>
          <th style="padding:1.1rem 0.9rem;">Method</th>
          <th style="padding:1.1rem 0.9rem;">Address / Wallet</th>
          <th style="padding:1.1rem 0.9rem;">Fee</th>
          <th style="padding:1.1rem 0.9rem;">Received</th>
          <th style="padding:1.1rem 0.9rem;">Requested</th>
          <th style="padding:1.1rem 0.9rem; border-top-right-radius:8px;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($withdrawals as $wd): ?>
        <tr style="background:var(--card); box-shadow: 0 2px 8px rgba(0,0,0,0.35);">
          <td style="padding:1.3rem 0.9rem; text-align:center; border-radius:0 0 0 8px;">
            <?= $wd['id'] ?>
          </td>
          <td style="padding:1.3rem 0.9rem;">
            <?= htmlspecialchars($wd['email'] ?? '—') ?><br>
            <small style="color:var(--text-muted);"><?= htmlspecialchars($wd['phone'] ?? '—') ?></small>
          </td>
          <td style="padding:1.3rem 0.9rem; text-align:right; font-weight:600; color:#f97316;">
            $<?= number_format($wd['amount'], 2) ?>
          </td>
          <td style="padding:1.3rem 0.9rem; text-align:center;">
            <?= htmlspecialchars($wd['method'] ?: '—') ?>
          </td>
          <td style="padding:1.3rem 0.9rem; word-break:break-all; font-size:0.92rem;">
            <?= htmlspecialchars($wd['address'] ?: '—') ?>
          </td>
          <td style="padding:1.3rem 0.9rem; text-align:right; color:#f85149;">
            $<?= number_format($wd['fee'] ?? 0, 2) ?>
          </td>
          <td style="padding:1.3rem 0.9rem; text-align:right; color:#10b981;">
            $<?= number_format($wd['received'] ?? 0, 2) ?>
          </td>
          <td style="padding:1.3rem 0.9rem; text-align:center; color:var(--text-muted);">
            <?= date('Y-m-d H:i', strtotime($wd['created_at'])) ?>
          </td>
          <td style="padding:1.3rem 0.9rem; border-radius:0 0 8px 0; text-align:center; white-space:nowrap;">
            <form method="POST" style="display:inline-block; margin-right:0.6rem;">
              <input type="hidden" name="withdrawal_id" value="<?= $wd['id'] ?>">
              <input type="hidden" name="action" value="approve">
              <button type="submit" class="btn green" style="padding:0.6rem 1.3rem; font-size:0.95rem;"
                onclick="return confirm('Approve withdrawal #<?= $wd['id'] ?> for $<?= number_format($wd['amount'], 2) ?>?\nThis will deduct from user withdrawal balance.');">
                <i class="fas fa-check"></i> Approve
              </button>
            </form>

            <form method="POST" style="display:inline-block;">
              <input type="hidden" name="withdrawal_id" value="<?= $wd['id'] ?>">
              <input type="hidden" name="action" value="reject">
              <button type="submit" class="btn red" style="padding:0.6rem 1.3rem; font-size:0.95rem;"
                onclick="return confirm('Reject / Delete withdrawal #<?= $wd['id'] ?>?\nThis action cannot be undone.');">
                <i class="fas fa-times"></i> Reject
              </button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php endif; ?>
</main>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
