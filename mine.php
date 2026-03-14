<?php
session_start();
require_once "config/database.php";

if(!isset($_SESSION['user_id'])){
header("Location: login.php");
exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT email,balance,vip_level FROM users WHERE id=?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$email = $user['email'];
$balance = $user['balance'];
$vip = "VIP".$user['vip_level'];

/* LAST RECHARGE */

$stmt = $pdo->prepare("
SELECT paid_amount 
FROM deposits 
WHERE user_id=? AND status=1
ORDER BY id DESC
LIMIT 1
");

$stmt->execute([$user_id]);

$recharge = $stmt->fetch(PDO::FETCH_ASSOC);

$recharge_amount = $recharge['paid_amount'] ?? 0;

?>

<?php include "inc/header.php"; ?>

<div class="mine-wrapper">

<div class="mine-top">

<div class="mine-user">

<h3>Hi, <?php echo htmlspecialchars($email); ?></h3>

<span class="vip-badge">
<?php echo $vip; ?>
</span>

</div>

<div class="mine-gif">

<img src="assets/images/usdt-gif.gif">

</div>

</div>


<div class="mine-balance">

<div class="balance-item">

<p>Total balance (USD)</p>

<h2><?php echo number_format($balance,2); ?></h2>

</div>

<div class="balance-item">

<p>Last Recharge amount (USD)</p>

<h2><?php echo number_format($recharge_amount,2); ?></h2>

</div>

</div>

</div>



<div class="mine-menu">

<a href="balance.php" class="menu-item">
<i class="fa-solid fa-wallet"></i>
<span>Account</span>
<i class="fa-solid fa-angle-right"></i>
</a>

<a href="recharge.php" class="menu-item">
<i class="fa-solid fa-coins"></i>
<span>Recharge</span>
<i class="fa-solid fa-angle-right"></i>
</a>

<a href="withdraw.php" class="menu-item">
<i class="fa-solid fa-money-bill-wave"></i>
<span>Withdraw</span>
<i class="fa-solid fa-angle-right"></i>
</a>

<a href="financial.php" class="menu-item">
<i class="fa-solid fa-chart-line"></i>
<span>Financial records</span>
<i class="fa-solid fa-angle-right"></i>
</a>

<a href="transfer.php" class="menu-item">
<i class="fa-solid fa-right-left"></i>
<span>Transfer</span>
<i class="fa-solid fa-angle-right"></i>
</a>

<a href="edit-profile.php" class="menu-item">
<i class="fa-solid fa-user"></i>
<span>Edit Profile</span>
<i class="fa-solid fa-angle-right"></i>
</a>

<a href="logout.php" class="menu-item">
<i class="fa-solid fa-right-from-bracket"></i>
<span>Sign out</span>
<i class="fa-solid fa-angle-right"></i>
</a>

</div>

<?php include "inc/footer.php"; ?>
