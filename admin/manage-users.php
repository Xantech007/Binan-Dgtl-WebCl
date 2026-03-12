<?php
// admin/manage-users.php
require_once __DIR__ . '/inc/header.php';

// Handle form submission (update user)
$message = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $user_id = (int)($_POST['user_id'] ?? 0);
    if ($user_id <= 0) {
        $error = "Invalid user ID.";
    } else {
        $email             = trim($_POST['email'] ?? '');
        $phone             = trim($_POST['phone'] ?? '');
        $vip_level         = (int)($_POST['vip_level'] ?? 0);
        $balance           = (float)($_POST['balance'] ?? 0);
        $withdrawal_balance = (float)($_POST['withdrawal_balance'] ?? 0);

        // Password is optional — only update if provided
        $password_update = '';
        if (!empty($_POST['password'])) {
            $new_pass = password_hash($_POST['password'], PASSWORD_BCRYPT, ['cost' => 12]);
            $password_update = ", password = :password";
        }

        try {
            $sql = "
                UPDATE users 
                SET 
                    email              = :email,
                    phone              = :phone,
                    vip_level          = :vip_level,
                    balance            = :balance,
                    withdrawal_balance = :withdrawal_balance
                    $password_update
                WHERE id = :id
            ";

            $stmt = $pdo->prepare($sql);
            $params = [
                ':email'              => $email,
                ':phone'              => $phone,
                ':vip_level'          => $vip_level,
                ':balance'            => $balance,
                ':withdrawal_balance' => $withdrawal_balance,
                ':id'                 => $user_id
            ];

            if ($password_update) {
                $params[':password'] = $new_pass;
            }

            $stmt->execute($params);

            $message = "User #$user_id updated successfully.";
        } catch (PDOException $e) {
            $error = "Update failed: " . $e->getMessage();
        }
    }
}

// Fetch all users
try {
    $stmt = $pdo->query("
        SELECT 
            id, email, phone, password, invite_code, 
            referral_code, referred_by, vip_level, 
            balance, withdrawal_balance, created_at
        FROM users 
        ORDER BY id DESC
    ");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Failed to load users: " . $e->getMessage();
    $users = [];
}
?>

<main>
  <h1 style="text-align:center; margin-bottom:2rem;">Manage Users</h1>

  <?php if ($message): ?>
    <div style="background:#238636; color:white; padding:1rem; border-radius:8px; margin-bottom:1.5rem; text-align:center;">
      <?= htmlspecialchars($message) ?>
    </div>
  <?php endif; ?>

  <?php if ($error): ?>
    <div style="background:#f85149; color:white; padding:1rem; border-radius:8px; margin-bottom:1.5rem; text-align:center;">
      <?= htmlspecialchars($error) ?>
    </div>
  <?php endif; ?>

  <?php if (empty($users)): ?>
    <p style="text-align:center; color:var(--text-muted);">No users found in the database.</p>
  <?php else: ?>

  <div style="overflow-x:auto;">
    <table style="width:100%; border-collapse:collapse; background:var(--card); border:1px solid var(--border); border-radius:8px; overflow:hidden;">
      <thead>
        <tr style="background:#1f2937;">
          <th>ID</th>
          <th>Email</th>
          <th>Phone</th>
          <th>VIP Level</th>
          <th>Balance</th>
          <th>Withdrawal Bal.</th>
          <th>Referred By</th>
          <th>Created</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $user): ?>
        <tr>
          <td><?= htmlspecialchars($user['id']) ?></td>
          <td><?= htmlspecialchars($user['email'] ?? '-') ?></td>
          <td><?= htmlspecialchars($user['phone'] ?? '-') ?></td>
          <td><?= htmlspecialchars($user['vip_level'] ?? '0') ?></td>
          <td>$<?= number_format($user['balance'] ?? 0, 2) ?></td>
          <td>$<?= number_format($user['withdrawal_balance'] ?? 0, 2) ?></td>
          <td><?= htmlspecialchars($user['referred_by'] ?? '-') ?></td>
          <td><?= date('Y-m-d H:i', strtotime($user['created_at'])) ?></td>
          <td>
            <button 
              class="btn" 
              style="padding:0.5rem 1rem; font-size:0.9rem;"
              onclick="openEditModal(<?= $user['id'] ?>, '<?= addslashes($user['email'] ?? '') ?>', '<?= addslashes($user['phone'] ?? '') ?>', <?= $user['vip_level'] ?? 0 ?>, <?= $user['balance'] ?? 0 ?>, <?= $user['withdrawal_balance'] ?? 0 ?>)"
            >
              <i class="fas fa-edit"></i> Edit
            </button>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php endif; ?>

  <!-- Edit Modal -->
  <div id="editModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.7); align-items:center; justify-content:center; z-index:1000;">
    <div style="background:var(--card); border:1px solid var(--border); border-radius:12px; width:90%; max-width:500px; padding:2rem; position:relative;">
      <button onclick="closeEditModal()" style="position:absolute; top:1rem; right:1rem; background:none; border:none; color:var(--text-muted); font-size:1.5rem; cursor:pointer;">×</button>
      
      <h2 style="margin-bottom:1.5rem; text-align:center;">Edit User</h2>
      
      <form method="POST">
        <input type="hidden" name="update_user" value="1">
        <input type="hidden" id="edit_user_id" name="user_id">

        <div class="form-group" style="margin-bottom:1.2rem;">
          <label>Email</label>
          <input type="email" id="edit_email" name="email" required style="width:100%; padding:0.7rem; border:1px solid var(--border); border-radius:6px; background:#0d1117; color:var(--text);">
        </div>

        <div class="form-group" style="margin-bottom:1.2rem;">
          <label>Phone</label>
          <input type="text" id="edit_phone" name="phone" style="width:100%; padding:0.7rem; border:1px solid var(--border); border-radius:6px; background:#0d1117; color:var(--text);">
        </div>

        <div class="form-group" style="margin-bottom:1.2rem;">
          <label>New Password <small>(leave blank to keep current)</small></label>
          <input type="password" name="password" placeholder="••••••••" style="width:100%; padding:0.7rem; border:1px solid var(--border); border-radius:6px; background:#0d1117; color:var(--text);">
        </div>

        <div class="form-group" style="margin-bottom:1.2rem;">
          <label>VIP Level</label>
          <input type="number" id="edit_vip_level" name="vip_level" min="0" style="width:100%; padding:0.7rem; border:1px solid var(--border); border-radius:6px; background:#0d1117; color:var(--text);">
        </div>

        <div class="form-group" style="margin-bottom:1.2rem;">
          <label>Balance ($)</label>
          <input type="number" id="edit_balance" name="balance" step="0.01" style="width:100%; padding:0.7rem; border:1px solid var(--border); border-radius:6px; background:#0d1117; color:var(--text);">
        </div>

        <div class="form-group" style="margin-bottom:1.8rem;">
          <label>Withdrawal Balance ($)</label>
          <input type="number" id="edit_withdrawal_balance" name="withdrawal_balance" step="0.01" style="width:100%; padding:0.7rem; border:1px solid var(--border); border-radius:6px; background:#0d1117; color:var(--text);">
        </div>

        <button type="submit" class="btn" style="width:100%; padding:0.9rem;">
          <i class="fas fa-save"></i> Save Changes
        </button>
      </form>
    </div>
  </div>
</main>

<script>
function openEditModal(id, email, phone, vip_level, balance, withdrawal_balance) {
  document.getElementById('edit_user_id').value          = id;
  document.getElementById('edit_email').value            = email;
  document.getElementById('edit_phone').value            = phone;
  document.getElementById('edit_vip_level').value        = vip_level;
  document.getElementById('edit_balance').value          = balance;
  document.getElementById('edit_withdrawal_balance').value = withdrawal_balance;
  document.getElementById('editModal').style.display     = 'flex';
}

function closeEditModal() {
  document.getElementById('editModal').style.display = 'none';
}
</script>


<?php require_once __DIR__ . '/inc/footer.php'; ?>
