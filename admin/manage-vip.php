<?php
// admin/manage-vip.php
require_once __DIR__ . '/inc/header.php';
$message = '';
$error = '';

// --------------------------------------------------
// HANDLE FORM SUBMISSIONS
// --------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'add' || $action === 'edit') {
            $name = trim($_POST['name'] ?? '');
            $daily_tasks = (int)($_POST['daily_tasks'] ?? 0);
            $simple_interest = (float)($_POST['simple_interest'] ?? 0);
            $daily_profit = (float)($_POST['daily_profit'] ?? 0);
            $total_profit = (float)($_POST['total_profit'] ?? 0);
            $activation_fee = (float)($_POST['activation_fee'] ?? 0);
            $duration_days = (int)($_POST['duration_days'] ?? 30);
            $status = (int)($_POST['status'] ?? 1);

            if (empty($name)) {
                throw new Exception("Plan name is required.");
            }

            if ($action === 'add') {
                $stmt = $pdo->prepare("
                    INSERT INTO vip
                    (name, daily_tasks, simple_interest, daily_profit, total_profit,
                     activation_fee, status, duration_days)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $name, $daily_tasks, $simple_interest, $daily_profit,
                    $total_profit, $activation_fee, $status, $duration_days
                ]);
                $message = "New VIP plan added successfully.";
            }
            else if ($action === 'edit') {
                $id = (int)($_POST['id'] ?? 0);
                if ($id <= 0) throw new Exception("Invalid plan ID.");

                $stmt = $pdo->prepare("
                    UPDATE vip SET
                        name = ?,
                        daily_tasks = ?,
                        simple_interest = ?,
                        daily_profit = ?,
                        total_profit = ?,
                        activation_fee = ?,
                        status = ?,
                        duration_days = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $name, $daily_tasks, $simple_interest, $daily_profit,
                    $total_profit, $activation_fee, $status, $duration_days, $id
                ]);
                $message = "VIP plan updated successfully.";
            }
        }
        else if ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) throw new Exception("Invalid plan ID.");

            $stmt = $pdo->prepare("SELECT COUNT(*) FROM user_vip WHERE vip_id = ?");
            $stmt->execute([$id]);
            if ($stmt->fetchColumn() > 0) {
                throw new Exception("Cannot delete: This VIP plan is assigned to one or more users.");
            }

            $stmt = $pdo->prepare("DELETE FROM vip WHERE id = ?");
            $stmt->execute([$id]);
            $message = "VIP plan deleted successfully.";
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// --------------------------------------------------
// LOAD ALL VIP PLANS
// --------------------------------------------------
try {
    $stmt = $pdo->query("SELECT * FROM vip ORDER BY id DESC");
    $plans = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Failed to load VIP plans: " . $e->getMessage();
    $plans = [];
}
?>
<main>
  <h1 style="text-align:center; margin: 2.5rem 0 2rem;">Manage VIP Plans</h1>

  <?php if ($message): ?>
    <div style="background:#238636; color:white; padding:1.2rem; border-radius:8px; margin-bottom:2rem; text-align:center; max-width:1200px; margin-left:auto; margin-right:auto;">
      <?= htmlspecialchars($message) ?>
    </div>
  <?php endif; ?>

  <?php if ($error): ?>
    <div style="background:#f85149; color:white; padding:1.2rem; border-radius:8px; margin-bottom:2rem; text-align:center; max-width:1200px; margin-left:auto; margin-right:auto;">
      <?= htmlspecialchars($error) ?>
    </div>
  <?php endif; ?>

  <!-- ADD NEW PLAN FORM -->
  <div style="background:var(--card); border:1px solid var(--border); border-radius:12px; padding:2rem; margin-bottom:3rem; max-width:1100px; margin-left:auto; margin-right:auto;">
    <h2 style="margin-bottom:1.5rem; text-align:center;">Add New VIP Plan</h2>
 
    <form method="POST" id="addForm">
      <input type="hidden" name="action" value="add">
      <div style="margin-bottom:1.4rem;">
        <label style="display:block; margin-bottom:0.5rem;">Plan Name *</label>
        <input type="text" name="name" id="add_name" required style="width:100%; padding:0.8rem; border:1px solid var(--border); border-radius:6px; background:#0d1117; color:var(--text);">
      </div>

      <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:1.4rem; margin-bottom:1.4rem;">
        <div>
          <label style="display:block; margin-bottom:0.5rem;">Daily Tasks</label>
          <input type="number" name="daily_tasks" id="add_daily_tasks" min="0" value="0" style="width:100%; padding:0.8rem; border:1px solid var(--border); border-radius:6px; background:#0d1117; color:var(--text);">
        </div>
        <div>
          <label style="display:block; margin-bottom:0.5rem;">Activation Fee ($)</label>
          <input type="number" name="activation_fee" id="add_activation_fee" step="0.01" min="0" value="0.00" style="width:100%; padding:0.8rem; border:1px solid var(--border); border-radius:6px; background:#0d1117; color:var(--text);">
        </div>
        <div>
          <label style="display:block; margin-bottom:0.5rem;">Duration (days)</label>
          <input type="number" name="duration_days" id="add_duration_days" min="1" value="30" style="width:100%; padding:0.8rem; border:1px solid var(--border); border-radius:6px; background:#0d1117; color:var(--text);">
        </div>
      </div>

      <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:1.4rem; margin-bottom:2rem;">
        <div>
          <label style="display:block; margin-bottom:0.5rem;">Simple Interest (%)</label>
          <input type="number" name="simple_interest" id="add_simple_interest" step="0.01" readonly style="width:100%; padding:0.8rem; border:1px solid var(--border); border-radius:6px; background:#1f2937; color:var(--text);">
        </div>
        <div>
          <label style="display:block; margin-bottom:0.5rem;">Daily Profit ($)</label>
          <input type="number" name="daily_profit" id="add_daily_profit" step="0.01" min="0" value="0.00" style="width:100%; padding:0.8rem; border:1px solid var(--border); border-radius:6px; background:#0d1117; color:var(--text);">
        </div>
        <div>
          <label style="display:block; margin-bottom:0.5rem;">Total Profit ($)</label>
          <input type="number" name="total_profit" id="add_total_profit" step="0.01" readonly style="width:100%; padding:0.8rem; border:1px solid var(--border); border-radius:6px; background:#1f2937; color:var(--text);">
        </div>
      </div>

      <div style="margin-bottom:2rem;">
        <label style="display:block; margin-bottom:0.5rem;">Status</label>
        <select name="status" style="width:100%; padding:0.8rem; border:1px solid var(--border); border-radius:6px; background:#0d1117; color:var(--text);">
          <option value="1" selected>Active</option>
          <option value="0">Inactive</option>
        </select>
      </div>

      <button type="submit" class="btn" style="width:100%; padding:1rem;">
        <i class="fas fa-plus"></i> Add VIP Plan
      </button>
    </form>
  </div>

  <!-- LIST OF EXISTING PLANS -->
  <h2 style="text-align:center; margin:3rem 0 1.5rem;">Existing VIP Plans</h2>

  <?php if (empty($plans)): ?>
    <p style="text-align:center; color:var(--text-muted);">No VIP plans found.</p>
  <?php else: ?>
  <div style="overflow-x:auto;">
    <table style="width:100%; max-width:1300px; margin:0 auto 3rem; border-collapse:separate; border-spacing:0 10px;">
      <thead>
        <tr style="background:#1f2937;">
          <th style="padding:1rem; border-top-left-radius:8px;">ID</th>
          <th style="padding:1rem;">Name</th>
          <th style="padding:1rem;">Fee</th>
          <th style="padding:1rem;">Duration</th>
          <th style="padding:1rem;">Daily Tasks</th>
          <th style="padding:1rem;">Daily Profit</th>
          <th style="padding:1rem;">Total Profit</th>
          <th style="padding:1rem;">Status</th>
          <th style="padding:1rem; border-top-right-radius:8px;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($plans as $plan): ?>
        <tr style="background:var(--card);">
          <td style="padding:1.1rem; text-align:center;"><?= $plan['id'] ?></td>
          <td style="padding:1.1rem;"><?= htmlspecialchars($plan['name']) ?></td>
          <td style="padding:1.1rem; text-align:right;">$<?= number_format($plan['activation_fee'], 2) ?></td>
          <td style="padding:1.1rem; text-align:center;"><?= $plan['duration_days'] ?> days</td>
          <td style="padding:1.1rem; text-align:center;"><?= $plan['daily_tasks'] ?></td>
          <td style="padding:1.1rem; text-align:right;">$<?= number_format($plan['daily_profit'], 2) ?></td>
          <td style="padding:1.1rem; text-align:right;">$<?= number_format($plan['total_profit'], 2) ?></td>
          <td style="padding:1.1rem; text-align:center;">
            <span style="color: <?= $plan['status'] ? '#238636' : '#f85149' ?>; font-weight:600;">
              <?= $plan['status'] ? 'Active' : 'Inactive' ?>
            </span>
          </td>
          <td style="padding:1.1rem; text-align:center; white-space:nowrap;">
            <button class="btn" style="padding:0.5rem 1rem; margin-right:0.5rem; font-size:0.9rem;"
                    onclick="openEditModal(<?= htmlspecialchars(json_encode($plan)) ?>)">
              <i class="fas fa-edit"></i> Edit
            </button>
            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete VIP plan «<?= htmlspecialchars(addslashes($plan['name'])) ?>»?');">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= $plan['id'] ?>">
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
    <div style="background:var(--card); border:1px solid var(--border); border-radius:12px; width:90%; max-width:1100px; max-height:90vh; overflow-y:auto; padding:2rem; position:relative;">
      <button onclick="document.getElementById('editModal').style.display='none'" 
              style="position:absolute; top:1rem; right:1.5rem; background:none; border:none; color:var(--text-muted); font-size:2rem; cursor:pointer;">
        ×
      </button>
      <h2 style="margin-bottom:1.8rem; text-align:center;">Edit VIP Plan</h2>
      
      <form method="POST" id="editForm">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="id" id="edit_id">
        
        <div style="margin-bottom:1.4rem;">
          <label style="display:block; margin-bottom:0.5rem;">Plan Name *</label>
          <input type="text" name="name" id="edit_name" required style="width:100%; padding:0.8rem; border:1px solid var(--border); border-radius:6px; background:#0d1117; color:var(--text);">
        </div>

        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:1.4rem; margin-bottom:1.4rem;">
          <div>
            <label style="display:block; margin-bottom:0.5rem;">Daily Tasks</label>
            <input type="number" name="daily_tasks" id="edit_daily_tasks" min="0" style="width:100%; padding:0.8rem; border:1px solid var(--border); border-radius:6px; background:#0d1117; color:var(--text);">
          </div>
          <div>
            <label style="display:block; margin-bottom:0.5rem;">Activation Fee ($)</label>
            <input type="number" name="activation_fee" id="edit_activation_fee" step="0.01" min="0" style="width:100%; padding:0.8rem; border:1px solid var(--border); border-radius:6px; background:#0d1117; color:var(--text);">
          </div>
          <div>
            <label style="display:block; margin-bottom:0.5rem;">Duration (days)</label>
            <input type="number" name="duration_days" id="edit_duration_days" min="1" style="width:100%; padding:0.8rem; border:1px solid var(--border); border-radius:6px; background:#0d1117; color:var(--text);">
          </div>
        </div>

        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:1.4rem; margin-bottom:2rem;">
          <div>
            <label style="display:block; margin-bottom:0.5rem;">Simple Interest (%)</label>
            <input type="number" name="simple_interest" id="edit_simple_interest" step="0.01" readonly style="width:100%; padding:0.8rem; border:1px solid var(--border); border-radius:6px; background:#1f2937; color:var(--text);">
          </div>
          <div>
            <label style="display:block; margin-bottom:0.5rem;">Daily Profit ($)</label>
            <input type="number" name="daily_profit" id="edit_daily_profit" step="0.01" min="0" style="width:100%; padding:0.8rem; border:1px solid var(--border); border-radius:6px; background:#0d1117; color:var(--text);">
          </div>
          <div>
            <label style="display:block; margin-bottom:0.5rem;">Total Profit ($)</label>
            <input type="number" name="total_profit" id="edit_total_profit" step="0.01" readonly style="width:100%; padding:0.8rem; border:1px solid var(--border); border-radius:6px; background:#1f2937; color:var(--text);">
          </div>
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
// Real-time Calculation Function
function calculateProfits(formPrefix) {
    const fee = parseFloat(document.getElementById(formPrefix + 'activation_fee').value) || 0;
    const dailyProfit = parseFloat(document.getElementById(formPrefix + 'daily_profit').value) || 0;
    const duration = parseInt(document.getElementById(formPrefix + 'duration_days').value) || 0;

    // Calculate Total Profit
    const totalProfit = dailyProfit * duration;
    document.getElementById(formPrefix + 'total_profit').value = totalProfit.toFixed(2);

    // Calculate Simple Interest (%)
    let simpleInterest = 0;
    if (fee > 0) {
        simpleInterest = (totalProfit / fee) * 100;
    }
    document.getElementById(formPrefix + 'simple_interest').value = simpleInterest.toFixed(2);
}

// Add listeners for Add Form
const addFields = ['activation_fee', 'daily_profit', 'duration_days'];
addFields.forEach(field => {
    const el = document.getElementById('add_' + field);
    if (el) {
        el.addEventListener('input', () => calculateProfits('add_'));
    }
});

// Add listeners for Edit Modal
const editFields = ['activation_fee', 'daily_profit', 'duration_days'];
editFields.forEach(field => {
    const el = document.getElementById('edit_' + field);
    if (el) {
        el.addEventListener('input', () => calculateProfits('edit_'));
    }
});

// Open Edit Modal + Trigger Calculation
function openEditModal(plan) {
  document.getElementById('edit_id').value = plan.id;
  document.getElementById('edit_name').value = plan.name;
  document.getElementById('edit_daily_tasks').value = plan.daily_tasks;
  document.getElementById('edit_activation_fee').value = plan.activation_fee;
  document.getElementById('edit_duration_days').value = plan.duration_days;
  document.getElementById('edit_daily_profit').value = plan.daily_profit;
  document.getElementById('edit_status').value = plan.status;

  // Set calculated fields
  document.getElementById('edit_simple_interest').value = parseFloat(plan.simple_interest).toFixed(2);
  document.getElementById('edit_total_profit').value = parseFloat(plan.total_profit).toFixed(2);

  document.getElementById('editModal').style.display = 'flex';

  // Trigger calculation in case values change
  setTimeout(() => calculateProfits('edit_'), 100);
}
</script>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
