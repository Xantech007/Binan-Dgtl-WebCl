<?php
session_start();

if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php");
    exit;
}

require_once "../config/database.php";

$user_id = $_SESSION['user_id'];

/* ================= REFERRAL CODE ================= */

$stmt = $pdo->prepare("
SELECT referral_code
FROM users
WHERE id=?
");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$my_code = $user['referral_code'];

/* ================= LEVEL 1 USERS ================= */

$stmt = $pdo->prepare("
SELECT id, email, phone, vip_level, balance, created_at
FROM users
WHERE referred_by=?
ORDER BY id DESC
");
$stmt->execute([$my_code]);

$members = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ================= ENRICH DATA ================= */

$rows = [];

$total_users = count($members);
$total_balance = 0;
$total_withdrawals = 0;
$total_deposits = 0;

foreach($members as $m){

    $user_id_row = $m['id'];

    /* DEPOSITS */
    $stmt = $pdo->prepare("
        SELECT SUM(amount)
        FROM deposits
        WHERE user_id=? AND status=1
    ");
    $stmt->execute([$user_id_row]);
    $deposits = $stmt->fetchColumn() ?? 0;

    /* WITHDRAWALS */
    $stmt = $pdo->prepare("
        SELECT SUM(amount)
        FROM withdrawals
        WHERE user_id=? AND status=1
    ");
    $stmt->execute([$user_id_row]);
    $withdrawals = $stmt->fetchColumn() ?? 0;

    /* TOTAL GENERATED */
    $generated = $deposits - $withdrawals;

    $total_balance += $m['balance'];
    $total_deposits += $deposits;
    $total_withdrawals += $withdrawals;

    $rows[] = [
        'user' => $m,
        'deposits' => $deposits,
        'withdrawals' => $withdrawals,
        'generated' => $generated
    ];
}

include "../inc/header.php";
?>

<style>
.team-wrap{
    padding:20px;
    color:#fff;
}

.summary{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:10px;
    margin-bottom:20px;
}

.summary div{
    background:#1b1d24;
    padding:12px;
    border-radius:10px;
    text-align:center;
}

table{
    width:100%;
    border-collapse:collapse;
    background:#14161c;
    border-radius:10px;
    overflow:hidden;
}

th,td{
    padding:12px;
    border-bottom:1px solid #222;
    text-align:left;
    font-size:14px;
}

th{
    background:#1f222b;
    color:#f0b24b;
}

.badge{
    padding:4px 8px;
    background:#2d2f3a;
    border-radius:6px;
    font-size:12px;
}
</style>

<div class="team-wrap">

<!-- HEADER -->
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:15px;">
    <a href="../team.php" style="color:#fff;text-decoration:none;">← Back</a>
    <h3>Level 1 Team</h3>
</div>

<!-- SUMMARY -->
<div class="summary">

    <div>
        <h4><?php echo $total_users; ?></h4>
        <p>Total Users</p>
    </div>

    <div>
        <h4>$<?php echo number_format($total_deposits,2); ?></h4>
        <p>Total Deposits</p>
    </div>

    <div>
        <h4>$<?php echo number_format($total_withdrawals,2); ?></h4>
        <p>Total Withdrawals</p>
    </div>

    <div>
        <h4>$<?php echo number_format($total_balance,2); ?></h4>
        <p>Total Balance</p>
    </div>

</div>

<!-- TABLE -->
<table>

<tr>
    <th>User</th>
    <th>VIP</th>
    <th>Deposits</th>
    <th>Withdrawals</th>
    <th>Net Generated</th>
    <th>Balance</th>
    <th>Joined</th>
</tr>

<?php if(!$rows): ?>

<tr>
    <td colspan="7" style="text-align:center;">No referrals yet</td>
</tr>

<?php else: ?>

<?php foreach($rows as $r): 
    $u = $r['user'];
?>

<tr>

    <td>
        <?php echo htmlspecialchars($u['email'] ?: $u['phone']); ?>
    </td>

    <td>
        <span class="badge">
            VIP<?php echo (int)$u['vip_level']; ?>
        </span>
    </td>

    <td>
        $<?php echo number_format($r['deposits'],2); ?>
    </td>

    <td>
        $<?php echo number_format($r['withdrawals'],2); ?>
    </td>

    <td>
        $<?php echo number_format($r['generated'],2); ?>
    </td>

    <td>
        $<?php echo number_format($u['balance'],2); ?>
    </td>

    <td>
        <?php echo date("d M Y",strtotime($u['created_at'])); ?>
    </td>

</tr>

<?php endforeach; ?>

<?php endif; ?>

</table>

</div>

<?php include "../inc/footer.php"; ?>
