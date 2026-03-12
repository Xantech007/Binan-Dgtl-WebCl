<?php
// admin/manage-deposits.php
require_once __DIR__ . '/inc/header.php';

$message = '';
$error   = '';

// Handle approve / reject actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $deposit_id = (int)($_POST['deposit_id'] ?? 0);
    $action     = $_POST['action'] ?? '';

    if ($deposit_id <= 0 || !in_array($action, ['approve', 'reject'])) {
        $error = "Invalid request.";
    } else {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("SELECT user_id, amount, status FROM deposits WHERE id = ? AND status = 0");
            $stmt->execute([$deposit_id]);
            $deposit = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$deposit) {
                throw new Exception("Deposit not found or already processed.");
            }

            if ($action === 'approve') {
                $stmt = $pdo->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
                $stmt->execute([$deposit['amount'], $deposit['user_id']]);

                $stmt = $pdo->prepare("UPDATE deposits SET status = 1, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$deposit_id]);

                $message = "Deposit #{$deposit_id} approved. Amount added to user balance.";
            } else {
                $stmt = $pdo->prepare("UPDATE deposits SET status = 2, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$deposit_id]);

                $message = "Deposit #{$deposit_id} rejected.";
            }

            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Operation failed: " . $e->getMessage();
        }
    }
}

// Fetch pending deposits + payment method name
try {
    $stmt = $pdo->query("
        SELECT 
            d.id, d.user_id, d.amount, d.method_id, d.proof, d.created_at,
            u.email, u.phone,
            COALESCE(pm.name, 'Unknown Method') AS method_name
        FROM deposits d
        LEFT JOIN users u ON d.user_id = u.id
        LEFT JOIN payment_methods pm ON d.method_id = pm.id
        WHERE d.status = 0
        ORDER BY d.created_at DESC
    ");
    $deposits = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Failed to load deposits: " . $e->getMessage();
    $deposits = [];
}
?>

<main>
  <h1 style="text-align:center; margin: 2.5rem 0 2rem;">Manage Pending Deposits</h1>

  <?php if ($message): ?>
    <div style="background:#238636; color:white; padding:1.2rem; border-radius:8px; margin-bottom:2rem; text-align:center; max-width:900px; margin-left:auto; margin-right:auto;">
      <?= htmlspecialchars($message) ?>
    </div>
  <?php endif; ?>

  <?php if ($error): ?>
    <div style="background:#f85149; color:white; padding:1.2rem; border-radius:8px; margin-bottom:2rem; text-align:center; max-width:900px; margin-left:auto; margin-right:auto;">
      <?= htmlspecialchars($error) ?>
    </div>
  <?php endif; ?>

  <?php if (empty($deposits)): ?>
    <p style="text-align:center; color:var(--text-muted); font-size:1.1rem; padding:3rem 1rem;">
      No pending deposits at the moment.
    </p>
  <?php else: ?>

  <div style="overflow-x:auto; margin: 0 auto; max-width: 100%;">
    <table style="
      width:100%; 
      max-width: 1200px; 
      margin: 0 auto 3rem; 
      border-collapse: separate; 
      border-spacing: 0 12px; 
      background: transparent;
    ">
      <thead>
        <tr style="background:#1f2937; color:#e6edf3;">
          <th style="padding:1.2rem 1rem; font-weight:600; border-top-left-radius:8px;">ID</th>
          <th style="padding:1.2rem 1rem; font-weight:600;">User</th>
          <th style="padding:1.2rem 1rem; font-weight:600;">Amount</th>
          <th style="padding:1.2rem 1rem; font-weight:600;">Method</th>
          <th style="padding:1.2rem 1rem; font-weight:600;">Proof</th>
          <th style="padding:1.2rem 1rem; font-weight:600;">Date</th>
          <th style="padding:1.2rem 1rem; font-weight:600; border-top-right-radius:8px;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($deposits as $dep): ?>
        <tr style="background:var(--card); box-shadow: 0 2px 8px rgba(0,0,0,0.3);">
          <td style="padding:1.3rem 1rem; border-radius:0 0 0 8px; text-align:center;">
            <?= htmlspecialchars($dep['id']) ?>
          </td>
          <td style="padding:1.3rem 1rem;">
            <?= htmlspecialchars($dep['email'] ?? '—') ?><br>
            <small style="color:var(--text-muted);"><?= htmlspecialchars($dep['phone'] ?? '—') ?></small>
          </td>
          <td style="padding:1.3rem 1rem; text-align:right; font-weight:600;">
            $<?= number_format($dep['amount'], 2) ?>
          </td>
          <td style="padding:1.3rem 1rem; text-align:center; font-weight:500;">
            <?= htmlspecialchars($dep['method_name']) ?>
          </td>
          <td style="padding:1.3rem 1rem; text-align:center;">
            <?php if (!empty($dep['proof'])): 
                // Using ../ because you confirmed this works from manage-deposits.php
                $proof_path = '../' . htmlspecialchars($dep['proof']);
            ?>
              <div style="position: relative; display: inline-block; cursor: pointer;" 
                   onclick="openPreview('<?= $proof_path ?>')">
                <img 
                  src="<?= $proof_path ?>" 
                  alt="Proof thumbnail" 
                  style="width: 80px; height: 80px; object-fit: cover; border-radius: 6px; border: 1px solid var(--border); box-shadow: 0 2px 6px rgba(0,0,0,0.4); transition: transform 0.15s;"
                  onmouseover="this.style.transform='scale(1.05)'"
                  onmouseout="this.style.transform='scale(1)'"
                >
                <div style="position: absolute; bottom: 4px; right: 4px; background: rgba(0,0,0,0.6); color: white; font-size: 11px; padding: 2px 6px; border-radius: 4px;">
                  Click to enlarge
                </div>
              </div>
            <?php else: ?>
              <span style="color:var(--text-muted);">No proof</span>
            <?php endif; ?>
          </td>
          <td style="padding:1.3rem 1rem; text-align:center;">
            <?= date('Y-m-d H:i', strtotime($dep['created_at'])) ?>
          </td>
          <td style="padding:1.3rem 1rem; border-radius:0 0 8px 0; text-align:center; white-space:nowrap;">
            <form method="POST" style="display:inline;">
              <input type="hidden" name="deposit_id" value="<?= $dep['id'] ?>">
              <input type="hidden" name="action" value="approve">
              <button type="submit" class="btn green" style="padding:0.6rem 1.2rem; font-size:0.95rem; margin-right:0.5rem;"
                onclick="return confirm('Approve this deposit and add $<?= number_format($dep['amount'], 2) ?> to user balance?');">
                <i class="fas fa-check"></i> Approve
              </button>
            </form>

            <form method="POST" style="display:inline;">
              <input type="hidden" name="deposit_id" value="<?= $dep['id'] ?>">
              <input type="hidden" name="action" value="reject">
              <button type="submit" class="btn red" style="padding:0.6rem 1.2rem; font-size:0.95rem;"
                onclick="return confirm('Reject this deposit? This action cannot be undone.');">
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

  <!-- Fullscreen Image Preview Modal -->
  <div id="previewModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.94); z-index:2000; align-items:center; justify-content:center; backdrop-filter:blur(6px);">
    <div style="position:relative; max-width:96%; max-height:96vh;">
      <button onclick="closePreview()" style="position:absolute; top:16px; right:16px; background:rgba(0,0,0,0.7); border:none; color:white; font-size:2.8rem; width:56px; height:56px; border-radius:50%; cursor:pointer; z-index:10; line-height:56px; text-align:center;">
        ×
      </button>
      <img id="previewImage" src="" alt="Proof Image" style="max-width:100%; max-height:92vh; border-radius:12px; box-shadow:0 0 60px rgba(0,0,0,0.9); object-fit:contain; background:#000;">
    </div>
  </div>
</main>

<script>
function openPreview(src) {
  document.getElementById('previewImage').src = src;
  document.getElementById('previewModal').style.display = 'flex';
}

function closePreview() {
  document.getElementById('previewModal').style.display = 'none';
  // optional: clear src to save memory
  document.getElementById('previewImage').src = '';
}

document.getElementById('previewModal').addEventListener('click', function(e) {
  if (e.target === this) closePreview();
});
</script>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
