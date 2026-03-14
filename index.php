<?php
session_start();

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

require_once "config/database.php";

/* Fetch logged in user */
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT email, vip_level, balance FROM users WHERE id=? LIMIT 1");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$user_email = $user['email'] ?? "Unknown";
$user_vip_level = intval($user['vip_level'] ?? 0);
$user_vip = "VIP".$user_vip_level;
$user_balance = floatval($user['balance'] ?? 0);

/* Fetch news */
$query = $pdo->query("SELECT title FROM news ORDER BY id DESC");

/* Fetch VIP plans */
$stmt = $pdo->prepare("SELECT id,name,activation_fee FROM vip WHERE status=1 ORDER BY id ASC");
$stmt->execute();
$vipQuery = $stmt;

/* FETCH RESET TIME */
$stmt = $pdo->query("SELECT reset_time FROM task_reset LIMIT 1");
$reset = $stmt->fetch(PDO::FETCH_ASSOC);

$reset_time = strtotime($reset['reset_time'] ?? "+12 hours");
$now = time();

/* IF RESET TIME EXPIRED → ADD 12 HOURS */
if($reset_time <= $now){

$new_reset = date("Y-m-d H:i:s", strtotime("+12 hours"));

$pdo->prepare("UPDATE task_reset SET reset_time=?")->execute([$new_reset]);

$reset_time = strtotime($new_reset);

}
?>

<?php include "inc/header.php"; ?>

<?php if(isset($_SESSION['recharge_msg'])): ?>
<div class="recharge-success">
<?php 
echo $_SESSION['recharge_msg']; 
unset($_SESSION['recharge_msg']);
?>
</div>
<?php endif; ?>

<?php if(isset($_SESSION['withdraw_msg'])): ?>
<div class="withdraw-success">
<?php 
echo $_SESSION['withdraw_msg'];
unset($_SESSION['withdraw_msg']);
?>
</div>
<?php endif; ?>


<!-- NEWS SCROLL -->

<div class="news-wrapper">

<div class="news-icon">
<i class="fa-solid fa-bell"></i>
</div>

<div class="news-marquee">
<div class="news-content">

<?php
while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
echo "<span class='news-item'>" . htmlspecialchars($row['title']) . "</span>";
}
?>

</div>
</div>

</div>


<!-- DASHBOARD -->

<div class="dashboard-container">

<div class="dashboard-top">
<div class="user-info">
<span class="user-email"><?php echo htmlspecialchars($user_email); ?></span>
<span class="vip-badge"><?php echo $user_vip; ?></span>
</div>

<a href="balance.php" class="wallet-btn">
<i class="fa-solid fa-wallet"></i>
</a>
</div>

<div class="balance-box">
<span>Balance</span>
<strong>$<?php echo number_format($user_balance,2); ?></strong>
</div>

<div class="dashboard-actions">

<div class="action-item">
<a href="recharge.php">
<div class="icon-circle">
<i class="fa-solid fa-money-bill-wave"></i>
</div>
</a>
<span>Recharge</span>
</div>

<div class="action-item">
<a href="withdraw.php">
<div class="icon-circle">
<i class="fa-solid fa-arrow-up-from-bracket"></i>
</div>
</a>
<span>Withdraw</span>
</div>

<div class="action-item">
<a href="app.php">
<div class="icon-circle">
<i class="fa-solid fa-mobile-screen"></i>
</div>
</a>
<span>App</span>
</div>

<div class="action-item">
<a href="company.php">
<div class="icon-circle">
<i class="fa-solid fa-building"></i>
</div>
</a>
<span>Company Profile</span>
</div>

</div>

</div>


<!-- BANNER -->

<div class="banner-slider">
<div class="banner-track">
<img src="assets/images/banner1.jpeg">
<img src="assets/images/banner2.jpeg">
</div>
</div>


<!-- TASK RESET COUNTDOWN -->

<div class="task-reset-container">

<div class="reset-time" id="taskCountdown">
00:00:00
</div>

<div class="reset-label">
Task Reset Countdown
</div>

</div>


<!-- TASK HALL -->

<div class="task-section">
<h2 class="task-title">Task Hall</h2>

<?php while($vip = $vipQuery->fetch(PDO::FETCH_ASSOC)): ?>

<?php
$vip_id = intval($vip['id']);
$vip_name = $vip['name'] ?? "VIP";
$activation_fee = floatval($vip['activation_fee'] ?? 0);
?>

<a href="vip.php?id=<?php echo $vip_id; ?>" class="task-card">

<div class="task-left">

<img src="assets/images/task.png" class="vip-icon">

<?php if($user_vip_level < $vip_id): ?>
<i class="fa-solid fa-lock lock-overlay"></i>
<?php endif; ?>

</div>

<div class="task-content">

<div class="unlock-text">
Unlock amount <span>$<?php echo number_format($activation_fee,2); ?></span>
</div>

<div class="vip-name">
<?php echo htmlspecialchars($vip_name); ?>
</div>

</div>

<div class="task-arrow">
<i class="fa-solid fa-angle-right"></i>
</div>

</a>

<?php endwhile; ?>

</div>


<script>

/* TASK RESET COUNTDOWN */

var resetTime = <?php echo $reset_time * 1000; ?>;

function updateCountdown(){

var now = new Date().getTime();
var distance = resetTime - now;

if(distance < 0){
location.reload();
return;
}

var hours = Math.floor((distance % (1000*60*60*24)) / (1000*60*60));
var minutes = Math.floor((distance % (1000*60*60)) / (1000*60));
var seconds = Math.floor((distance % (1000*60)) / 1000);

document.getElementById("taskCountdown").innerHTML =
hours.toString().padStart(2,'0') + ":" +
minutes.toString().padStart(2,'0') + ":" +
seconds.toString().padStart(2,'0');

}

setInterval(updateCountdown,1000);

</script>

<?php include "inc/footer.php"; ?>
