<?php
// admin/manage-payment-methods.php
require_once __DIR__ . '/inc/header.php';

$message = '';
$error   = '';

$upload_dir = __DIR__ . '../assets/images/qr/';
$upload_url_prefix = 'assets/images/qr/';   // path saved in DB

// Ensure upload directory exists
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        $name           = trim($_POST['name'] ?? '');
        $wallet_address = trim($_POST['wallet_address'] ?? '');
        $status         = (int)($_POST['status'] ?? 1);
        $withdrawal_fee = (float)($_POST['withdrawal_fee'] ?? 0.00);
        $qr_image_path  = trim($_POST['current_qr_image'] ?? ''); // for edit - keep old if no new upload

        if (empty($name)) {
            throw new Exception("Payment method name is required.");
        }

        // ───────────────────────────────────────────────
        // Handle QR image upload
        // ───────────────────────────────────────────────
        if (!empty($_FILES['qr_image']['name'])) {
            $file = $_FILES['qr_image'];
            $allowed = ['jpg','jpeg','png','webp','gif'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if (!in_array($ext, $allowed)) {
                throw new Exception("Only JPG, JPEG, PNG, WEBP, GIF files are allowed.");
            }
            if ($file['size'] > 2 * 1024 * 1024) { // 2MB
                throw new Exception("File size must be less than 2MB.");
            }

            // Clean and unique filename
            $safe_name = preg_replace('/[^a-z0-9-]/i', '-', pathinfo($file['name'], PATHINFO_FILENAME));
            $new_filename = $safe_name . '-' . date('Ymd-His') . '.' . $ext;
            $target_path = $upload_dir . $new_filename;

            if (!move_uploaded_file($file['tmp_name'], $target_path)) {
                throw new Exception("Failed to upload QR image.");
            }

            $qr_image_path = $upload_url_prefix . $new_filename;
        }

        $data = [
            $name,
            $wallet_address,
            $qr_image_path,
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
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// ───────────────────────────────────────────────
// Delete action
// ───────────────────────────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    try {
        if ($id <= 0) throw new Exception("Invalid ID");

        $check = $pdo->prepare("SELECT COUNT(*) FROM deposits WHERE method_id = ?");
        $check->execute([$id]);
        if ($check->fetchColumn() > 0) {
            throw new Exception("Cannot delete: method is used in deposits.");
        }

        // Optional: delete old QR image file
        $stmt = $pdo->prepare("SELECT qr_image FROM payment_methods WHERE id = ?");
        $stmt->execute([$id]);
        $old_qr = $stmt->fetchColumn();
        if ($old_qr && file_exists(__DIR__ . '/../../' . $old_qr)) {
            @unlink(__DIR__ . '/../../' . $old_qr);
        }

        $stmt = $pdo->prepare("DELETE FROM payment_methods WHERE id = ?");
        $stmt->execute([$id]);

        $message = "Payment method deleted.";
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Load all methods
try {
    $stmt = $pdo->query("SELECT * FROM payment_methods ORDER BY id DESC");
    $methods = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Failed to load methods: " . $e->getMessage();
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

  <!-- ADD FORM -->
  <div style="background:var(--card); border:1px solid var(--border); border-radius:12px; padding:2rem; margin-bottom:3rem; max-width:900px; margin-left:auto; margin-right:auto;">
    <h2 style="margin-bottom:1.8rem; text-align:center;">Add New Payment Method</h2>

    <form method="POST" enctype="multipart/form-data">
      <input type="hidden" name="action" value="add">

      <div style="margin-bottom:1.4rem;">
        <label style="display:block; margin-bottom:0.5rem;">Method Name *</label>
        <input type="text" name="name" required placeholder="USDT TRC20, Bitcoin, ..." 
               style="width:100%; padding:0.8rem; border:1px solid var(--border); border-radius:6px; background:#0d1117; color:var(--text);">
      </div>

      <div style="margin-bottom:1.4rem;">
        <label style="display:block; margin-bottom:0.5rem;">Wallet Address / IBAN</label>
        <input type="text" name="wallet_address" placeholder="T... or bank account"
               style="width:100%; padding:0.8rem; border:1px solid var(--border); border-radius:6px; background:#0d1117; color:var(--text);">
      </div>

      <div style="margin-bottom:1.4rem;">
        <label style="display:block; margin-bottom:0.5rem;">QR Code Image (jpg/png/webp/gif, max 2MB)</label>
        <input type="file" name="qr_image" accept="image/*" 
               style="width:100%; padding:0.6rem; border:1px solid var(--border); border-radius:6px; background:#0d1117; color:var(--text);">
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

  <!-- LIST -->
  <h2 style="text-align:center; margin:3rem 0 1.5rem;">Payment Methods</h2>

  <?php if (empty($methods)): ?>
    <p style="text-align:center; color:var(--text-muted);">No payment methods yet.</p>
  <?php else: ?>
  <div style="overflow-x:auto;">
    <table style="width:100%; max-width:1100px; margin:0 auto 3rem; border-collapse:separate; border-spacing:0 10px;">
      <thead>
        <tr style="background:#1f2937;">
          <th style="padding:1rem; border-top-left-radius:8px;">ID</th>
          <th style="padding:1rem;">Name</th>
          <th style="padding:1rem;">Wallet</th>
          <th style="padding:1rem;">QR</th>
          <th style="padding:1rem;">Fee</th>
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
          <td style="padding:1.1rem; text-align:center;">
            <?php if ($m['qr_image']): ?>
              <img src="../<?= htmlspecialchars($m['qr_image']) ?>" alt="QR" style="max-width:80px; border-radius:6px; border:1px solid var(--border);">
            <?php else: ?>
              —
            <?php endif; ?>
          </td>
          <td style="padding:1.1rem; text-align:right;">$<?= number_format($m['withdrawal_fee'] ?? 0, 2) ?></td>
          <td style="padding:1.1rem; text-align:center;">
            <span style="color:<?= $m['status'] ? '#238636' : '#f85149' ?>; font-weight:600;">
              <?= $m['status'] ? 'Active' : 'Inactive' ?>
            </span>
          </td>
          <td style="padding:1.1rem; text-align:center; white-space:nowrap;">
            <button class="btn" style="padding:0.5rem 1rem; margin-right:0.5rem; font-size:0.9rem;"
                    onclick='openEditModal(<?= json_encode($m, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
              <i class="fas fa-edit"></i> Edit
            </button>

            <form method="POST" style="display:inline;" 
                  onsubmit="return confirm('Delete «<?= htmlspecialchars(addslashes($m['name'])) ?>»?');">
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

      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="id" id="edit_id">
        <input type="hidden" name="current_qr_image" id="edit_current_qr">

        <div style="margin-bottom:1.4rem;">
          <label style="display:block; margin-bottom:0.5rem;">Method Name *</label>
          <input type="text" name="name" id="edit_name" required 
                 style="width:100%; padding:0.8rem; border:1px solid var(--border); border-radius:6px; background:#0d1117; color:var(--text);">
        </div>

        <div style="margin-bottom:1.4rem;">
          <label style="display:block; margin-bottom:0.5rem;">Wallet Address</label>
          <input type="text" name="wallet_address" id="edit_wallet_address" 
                 style="width:100%; padding:0.8rem; border:1px solid var(--border); border-radius:6px; background:#0d1117; color:var(--text);">
        </div>

        <div style="margin-bottom:1.4rem;">
          <label style="display:block; margin-bottom:0.5rem;">Current QR:</label>
          <div id="current_qr_preview" style="margin:0.5rem 0;"></div>
          <label style="display:block; margin-bottom:0.5rem;">New QR Code (leave empty to keep current)</label>
          <input type="file" name="qr_image" accept="image/*" 
                 style="width:100%; padding:0.6rem; border:1px solid var(--border); border-radius:6px; background:#0d1117; color:var(--text);">
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
function openEditModal(m) {
  document.getElementById('edit_id').value              = m.id;
  document.getElementById('edit_name').value            = m.name;
  document.getElementById('edit_wallet_address').value  = m.wallet_address || '';
  document.getElementById('edit_current_qr').value      = m.qr_image || '';
  document.getElementById('edit_withdrawal_fee').value  = m.withdrawal_fee || '0.00';
  document.getElementById('edit_status').value          = m.status;

  // Show current QR preview
  const preview = document.getElementById('current_qr_preview');
  preview.innerHTML = m.qr_image 
    ? `<img src="../${m.qr_image}" alt="Current QR" style="max-width:140px; border-radius:6px; border:1px solid var(--border);">`
    : '<span style="color:var(--text-muted);">No QR image</span>';

  document.getElementById('editModal').style.display = 'flex';
}
</script>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
