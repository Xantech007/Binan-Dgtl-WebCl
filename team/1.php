<?php
session_start();

if(!isset($_SESSION['user_id'])){
header("Location: ../login.php");
exit;
}

require_once "../config/database.php";

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT referral_code FROM users WHERE id=?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$my_code = $user['referral_code'];

$stmt = $pdo->prepare("
SELECT email,phone,vip_level,balance,created_at
FROM users
WHERE referred_by=?
ORDER BY id DESC
");

$stmt->execute([$my_code]);

$members = $stmt->fetchAll(PDO::FETCH_ASSOC);

include "../inc/header.php";
?>

<link rel="stylesheet" href="assets/css/team-detail.css">

<div class="team-detail">

<div class="team-header">
<a href="../team.php"><i class="fa fa-arrow-left"></i></a>
<span>Level 1 Team</span>
</div>

<?php if(!$members): ?>

<div class="team-empty">
No referrals yet
</div>

<?php endif; ?>


<?php foreach($members as $m): ?>

<div class="team-member">

<div>
<strong>
<?php echo $m['email'] ?: $m['phone']; ?>
</strong>
</div>

<div class="member-info">
VIP<?php echo $m['vip_level']; ?> |
$<?php echo number_format($m['balance'],2); ?>
</div>

<div class="member-date">
<?php echo date("d M Y",strtotime($m['created_at'])); ?>
</div>

</div>

<?php endforeach; ?>

</div>

<?php include "../inc/footer.php"; ?>
