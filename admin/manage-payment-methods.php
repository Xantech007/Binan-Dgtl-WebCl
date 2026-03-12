<?php
// admin/manage-payment-methods.php
require_once __DIR__ . '/inc/header.php';

$message = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'add' || $action === 'edit') {
            $name           = trim($_POST['name'] ?? '');
            $wallet_address = trim($_POST['wallet_address'] ?? '');
            $qr_image       = trim($_POST['qr_image'] ?? '');
            $status         = (int)($_POST['status'] ?? 1);
            $withdrawal_fee = (float)($_POST['withdrawal_fee'] ?? 0.00);

            if (empty($name)) {
                throw new Exception("Payment method name is required.");
            }

            $data = [
                $name,
                $wallet_address,
                $qr_image,
                $status,
                $withdrawal_fee
            ];

            if ($action === 'add') {
                $stmt = $pdo->prepare("
                    INSERT INTO payment_methods 
                    (name, wallet_address, qr_image, status, withdrawal_fee)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute($data);
                $message = "Payment method added successfully.";
            } 
            else if ($action === 'edit') {
                $id = (int)($_POST['id'] ?? 0);
                if ($id <= 0) throw new Exception("Invalid method ID.");

                $data[] = $id;

                $stmt = $pdo->prepare("
                    UPDATE payment_methods SET
                        name            = ?,
                        wallet_address  = ?,
                        qr_image        = ?,
                        status          = ?,
                        withdrawal_fee  = ?
                    WHERE id = ?
                ");
                $stmt->execute($data);
                $message = "Payment method updated successfully.";
            }
        } 
        else if ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) throw new Exception("Invalid method ID.");

            // Optional: prevent deletion if used in deposits
            $check = $pdo->prepare("SELECT COUNT(*) FROM deposits WHERE method_id = ?");
            $check->execute([$id]);
            if ($check->fetchColumn() > 0) {
                throw new Exception("Cannot delete: This method is used in some deposits.");
            }

            $stmt = $pdo->prepare("DELETE FROM payment_methods WHERE id = ?");
            $stmt->execute([$id]);
            $message = "Payment method deleted successfully.";
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Load all payment methods
try {
    $stmt = $pdo->query("
        SELECT * FROM payment_methods 
        ORDER BY id DESC
    ");
    $methods = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Failed to load payment methods: " . $e->getMessage();
    $methods = [];
}
?>

<main>
  <h1 style="text-align:center; margin: 2.5rem 0 2rem;">Manage Payment Methods</h1>

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

  <!-- ADD NEW METHOD FORM -->
  <div style="background:var(--card); border:1px solid var(--border); border-radius:12px; padding:2rem; margin-bottom:3rem; max-width:900px; margin-left:auto; margin-right:auto;">
    <h2 style="margin-bottom:1.8rem; text-align:center;">Add New Payment Method</h2>

    <form method="POST">
      <input type="hidden" name="action" value="add">

      <div style="margin-bottom:1.4rem;">
        <label style="display:block; margin-bottom:0.5rem;">Method Name *</label>
        <input type="text" name="name" required placeholder="e.g. USDT TRC20, Bitcoin, Bank Transfer" 
               style="width:100%; padding:0.8rem; border:1px solid var(--border); border-radius:6px; background:#0d1117; color:var(--text);">
      </div>

      <div style="margin-bottom:1.4rem;">
        <label style="display:block; margin-bottom:0.5rem;">Wallet Address / Account Details</label>
        <input type="text" name="wallet_address" placeholder="Wallet address or bank account number"
               style="width:100%; padding:0.8rem; border:1px solid var(--border); border-radius:6px; background:#0d1117; color:var(--text);">
      </div>

      <div style="margin-bottom:1.4rem;">
        <label style="display:block; margin-bottom:0.5rem;">QR Code Image Path (optional)</label>
        <input type="text" name="qr_image" placeholder="assets/images/qr/usdt-qr.png"
               style="width:100%; padding:0.8rem; border:1px solid var(--border); border-radius:6px; background:#0d1117; color:var(--text);">
      </div>

      <div style="margin-bottom:1.4rem;">
        <label style="display:block; margin-bottom:0.5rem;">Withdrawal Fee ($)</label>
        <input type="number" name="withdrawal_fee" step="0.01" min="0" value="0.00"
               style="width:100%; padding:0.8rem; border:1px solid var(--border); border-radius:6px; background:#0d1117; color:var(--text);">
      </div>

      <div style="margin-bottom:2rem;">
        <label style="display:block; margin-bottom:0.5rem;">Status</label>
        <select name="status" style="width:100%; padding:0.8rem; border:1px solid var(--border); border-radius:6px; background:#0d1117; color:var(--text);">
          <option value="1" selected>Active</option>
          <option value="0">Inactive</option>
        </select>
      </div>

      <button type="submit" class="btn" style="width:100%; padding:1rem;">
        <i class="fas fa-plus"></i> Add Payment Method
      </button>
    </form>
  </div>

  <!-- LIST OF METHODS -->
  <h2 style="text-align:center; margin:3rem 0 1.5rem;">Existing Payment Methods</h2>

  <?php if (empty($methods)): ?>
    <p style="text-align:center; color:var(--text-muted);">No payment methods found.</p>
  <?php else: ?>
  <div style="overflow-x:auto;">
    <table style="
      width:100%; 
      max-width:1100px; 
      margin:0 auto 3rem; 
      border-collapse:separate; 
      border-spacing:0 10px;
    ">
      <thead>
        <tr style="background:#1f2937;">
          <th style="padding:1rem; border-top-left-radius:8px;">ID</th>
          <th style="padding:1rem;">Name</th>
          <th style="padding:1rem;">Wallet / Address</th>
          <th style="padding:1rem;">Withdrawal Fee</th>
          <th style="padding:1rem;">Status</th>
          <th style="padding:1rem; border-top-right-radius:8px;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($methods as $m): ?>
        <tr style="background:var(--card);">
          <td style="padding:1.1rem; text-align:center;"><?= $m['id'] ?></td>
          <td style="padding:1.1rem;"><?= htmlspecialchars($m['name']) ?></td>
          <td style="padding:1.1rem; word-break:break-all; font-size:0.92rem;">
            <?= htmlspecialchars($m['wallet_address'] ?: '—') ?>
          </td>
          <td style="padding:1.1rem; text-align:right;">
            $<?= number_format($m['withdrawal_fee'] ?? 0, 2) ?>
          </td>
          <td style="padding:1.1rem; text-align:center;">
            <span style="color: <?= $m['status'] ? '#238636' : '#f85149' ?>; font-weight:600;">
              <?= $m['status'] ? 'Active' : 'Inactive' ?>
            </span>
          </td>
          <td style="padding:1.1rem; text-align:center; white-space:nowrap;">
            <button class="btn" style="padding:0.5rem 1rem; margin-right:0.5rem; font-size:0.9rem;"
                    onclick='openEditModal(<?= json_encode($m, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
              <i class="fas fa-edit"></i> Edit
            </button>

            <form method="POST" style="display:inline;" 
                  onsubmit="return confirm('Delete payment method «<?= htmlspecialchars(addslashes($m['name'])) ?>»?');">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= $m['id'] ?>">
              <button type="submit" class="btn red" style="padding:0.5rem 1rem; font-size:0.9rem;">
                <i class="fas fa-trash"></i> Delete
              </button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>

  <!-- EDIT MODAL -->
  <div id="editModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.75); align-items:center; justify-content:center; z-index:1000;">
    <div style="background:var(--card); border:1px solid var(--border); border-radius:12px; width:90%; max-width:800px; padding:2rem; position:relative;">
      <button onclick="document.getElementById('editModal').style.display='none'" 
              style="position:absolute; top:1rem; right:1.5rem; background:none; border:none; color:var(--text-muted); font-size:2rem; cursor:pointer;">
        ×
      </button>

      <h2 style="margin-bottom:1.8rem; text-align:center;">Edit Payment Method</h2>

      <form method="POST">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="id" id="edit_id">

        <div style="margin-bottom:1.4rem;">
          <label style="display:block; margin-bottom:0.5rem;">Method Name *</label>
          <input type="text" name="name" id="edit_name" required 
                 style="width:100%; padding:0.8rem; border:1px solid var(--border); border-radius:6px; background:#0d1117; color:var(--text);">
        </div>

        <div style="margin-bottom:1.4rem;">
          <label style="display:block; margin-bottom:0.5rem;">Wallet Address / Account</label>
          <input type="text" name="wallet_address" id="edit_wallet_address" 
                 style="width:100%; padding:0.8rem; border:1px solid var(--border); border-radius:6px; background:#0d1117; color:var(--text);">
        </div>

        <div style="margin-bottom:1.4rem;">
          <label style="display:block; margin-bottom:0.5rem;">QR Code Image Path (optional)</label>
          <input type="text" name="qr_image" id="edit_qr_image" placeholder="assets/images/qr/..."
                 style="width:100%; padding:0.8rem; border:1px solid var(--border); border-radius:6px; background:#0d1117; color:var(--text);">
        </div>

        <div style="margin-bottom:1.4rem;">
          <label style="display:block; margin-bottom:0.5rem;">Withdrawal Fee ($)</label>
          <input type="number" name="withdrawal_fee" id="edit_withdrawal_fee" step="0.01" min="0"
                 style="width:100%; padding:0.8rem; border:1px solid var(--border); border-radius:6px; background:#0d1117; color:var(--text);">
        </div>

        <div style="margin-bottom:2rem;">
          <label style="display:block; margin-bottom:0.5rem;">Status</label>
          <select name="status" id="edit_status" style="width:100%; padding:0.8rem; border:1px solid var(--border); border-radius:6px; background:#0d1117; color:var(--text);">
            <option value="1">Active</option>
            <option value="0">Inactive</option>
          </select>
        </div>

        <button type="submit" class="btn" style="width:100%; padding:1rem;">
          <i class="fas fa-save"></i> Save Changes
        </button>
      </form>
    </div>
  </div>

</main>

<script>
function openEditModal(method) {
  document.getElementById('edit_id').value              = method.id;
  document.getElementById('edit_name').value            = method.name;
  document.getElementById('edit_wallet_address').value  = method.wallet_address || '';
  document.getElementById('edit_qr_image').value        = method.qr_image || '';
  document.getElementById('edit_withdrawal_fee').value  = method.withdrawal_fee || '0.00';
  document.getElementById('edit_status').value          = method.status;

  document.getElementById('editModal').style.display = 'flex';
}
</script>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
