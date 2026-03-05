<?php
session_start();

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

require_once "config/database.php";

/* Fetch logged in user */
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT email, vip_level, balance FROM users WHERE id=?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$user_email = $user['email'] ?? "Unknown";
$user_vip = "VIP".($user['vip_level'] ?? 0);
$user_balance = $user['balance'] ?? 0;

/* Fetch news */
$query = $pdo->query("SELECT title FROM news ORDER BY id DESC");

?>

<?php include "inc/header.php"; ?>

<!-- Scrolling News Section -->
<div class="news-wrapper">
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

<!-- ================= DASHBOARD ACTION SECTION ================= -->

<div class="dashboard-container">

    <div class="dashboard-top">
        <div class="user-info">
            <span class="user-email"><?php echo htmlspecialchars($user_email); ?></span>
            <span class="vip-badge"><?php echo $user_vip; ?></span>
        </div>

        <a href="wallet.php" class="wallet-btn">
            <i class="fa-solid fa-wallet"></i>
        </a>
    </div>

    <div class="balance-box">
        <span>Balance</span>
        <strong>$<?php echo number_format($user_balance,2); ?></strong>
    </div>

    <div class="dashboard-actions">

        <a href="#" class="action-item">
            <div class="icon-circle"><i class="fa-solid fa-money-bill-wave"></i></div>
            <span>Recharge</span>
        </a>

        <a href="#" class="action-item">
            <div class="icon-circle"><i class="fa-solid fa-arrow-up-from-bracket"></i></div>
            <span>Withdraw</span>
        </a>

        <a href="#" class="action-item">
            <div class="icon-circle"><i class="fa-solid fa-mobile-screen"></i></div>
            <span>App</span>
        </a>

        <a href="#" class="action-item">
            <div class="icon-circle"><i class="fa-solid fa-building"></i></div>
            <span>Company Profile</span>
        </a>

    </div>

</div>

<!-- ================= BANNER ================= -->

<div class="banner-slider">
    <div class="banner-track">
        <img src="assets/images/banner1.jpeg">
        <img src="assets/images/banner2.jpeg">
    </div>
</div>

<?php
$vipQuery = $pdo->query("SELECT * FROM vip WHERE status = 1 ORDER BY id ASC");
?>

<!-- ================= TASK HALL ================= -->

<?php
$vipQuery = $pdo->query("SELECT * FROM vip WHERE status = 1 ORDER BY id ASC");
?>

<div class="task-section">
<h2 class="task-title">Task Hall</h2>

<?php while($vip = $vipQuery->fetch(PDO::FETCH_ASSOC)): ?>

<div class="task-card">

<img src="assets/images/vip.jpg" class="task-left">

<div class="task-content">

<h3><?php echo htmlspecialchars($vip['name']); ?></h3>

<p>
Mine rate - $<?php echo $vip['mine_rate']; ?> per minute  
Earn $<?php echo $vip['daily_income']; ?> daily
</p>

</div>

<div class="task-right">

<?php if($user['vip_level'] < $vip['id']): ?>

<i class="fa-solid fa-lock lock-icon"></i>

<a href="activate_vip.php?id=<?php echo $vip['id']; ?>" class="vip-btn">
Activation fee: $<?php echo $vip['activation_fee']; ?>
</a>

<?php else: ?>

<a href="mine.php?vip=<?php echo $vip['id']; ?>" class="vip-active">
Start Mining
</a>

<?php endif; ?>

</div>

</div>

<?php endwhile; ?>

</div>
<!-- ================= MEMBER LIST ================= -->

<div class="member-section">
    <h2 class="member-title">Member list</h2>

    <div class="member-wrapper">
        <div class="member-track">

<?php
$members = [
["VIP1","johnb****@gmail.com"],
["VIP2","maryc****@yahoo.com"],
["VIP3","alexd****@gmail.com"],
["VIP4","kelvine****@hotmail.com"],
["VIP5","amaka****@gmail.com"],
["VIP6","fghgh****@gmail.com"],
["VIP7","sandra****@yahoo.com"],
["VIP8","jamesk****@gmail.com"],
["VIP3","cvxbb****@gmail.com"],
["VIP6","fghgh****@gmail.com"]
];

shuffle($members);

foreach($members as $member):

$earning = rand(50,1500);
?>

<div class="member-row">
<div class="member-card">
<div class="vip-level"><?php echo $member[0]; ?></div>
<div class="earning">+$<?php echo number_format($earning,2); ?></div>
<div class="email"><?php echo $member[1]; ?></div>
</div>
</div>

<?php endforeach; ?>

<?php foreach($members as $member):
$earning = rand(50,1500);
?>

<div class="member-row">
<div class="member-card">
<div class="vip-level"><?php echo $member[0]; ?></div>
<div class="earning">+$<?php echo number_format($earning,2); ?></div>
<div class="email"><?php echo $member[1]; ?></div>
</div>
</div>

<?php endforeach; ?>

        </div>
    </div>
</div>

<!-- ================= REGULATORY AUTHORITY ================= -->

<div class="reg-section">
    <h2 class="reg-title">Regulatory Authority</h2>

    <div class="reg-container">
        <img src="assets/images/reg1.webp">
        <img src="assets/images/reg2.webp">
    </div>
</div>

<?php include "inc/footer.php"; ?>
