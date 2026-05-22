<?php
session_start();

if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php");
    exit;
}

require_once "../config/database.php";

$user_id = $_SESSION['user_id'];

/* =========================================================
   GET USER REFERRAL CODE
========================================================= */

$stmt = $pdo->prepare("
SELECT referral_code
FROM users
WHERE id=?
");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$my_code = $user['referral_code'];

/* =========================================================
   LEVEL 1 MEMBERS
========================================================= */

$stmt = $pdo->prepare("
SELECT id, email, phone, vip_level, balance, created_at
FROM users
WHERE referred_by=?
ORDER BY id DESC
");
$stmt->execute([$my_code]);

$members = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* =========================================================
   LEVEL 1 STATS
========================================================= */

$total_members = count($members);

$total_balance = 0;
$total_vip = 0;

foreach($members as $m){
    $total_balance += $m['balance'];
    if($m['vip_level'] > 0){
        $total_vip++;
    }
}

include "../inc/header.php";
?>

<link rel="stylesheet" href="../assets/css/team-detail.css">

<div class="team-detail">

<!-- HEADER -->
<div class="team-header">
    <a href="../team.php"><i class="fa fa-arrow-left"></i></a>
    <span>Level 1 Team</span>
</div>

<!-- SUMMARY -->
<div class="team-summary">

    <div class="summary-box">
        <span>Total Members</span>
        <strong><?php echo $total_members; ?></strong>
    </div>

    <div class="summary-box">
        <span>Active VIP</span>
        <strong><?php echo $total_vip; ?></strong>
    </div>

    <div class="summary-box">
        <span>Total Balance</span>
        <strong>$<?php echo number_format($total_balance,2); ?></strong>
    </div>

</div>

<!-- LIST -->
<div class="team-list">

<?php if(!$members): ?>

    <div class="team-empty">
        No referrals yet
    </div>

<?php else: ?>

    <?php foreach($members as $m): ?>

        <div class="team-member">

            <div class="member-main">
                <strong>
                    <?php echo htmlspecialchars($m['email'] ?: $m['phone']); ?>
                </strong>

                <span class="vip-tag">
                    VIP<?php echo (int)$m['vip_level']; ?>
                </span>
            </div>

            <div class="member-meta">

                <div>
                    <span>Balance</span>
                    <strong>
                        $<?php echo number_format($m['balance'],2); ?>
                    </strong>
                </div>

                <div>
                    <span>Joined</span>
                    <strong>
                        <?php echo date("d M Y",strtotime($m['created_at'])); ?>
                    </strong>
                </div>

            </div>

        </div>

    <?php endforeach; ?>

<?php endif; ?>

</div>

</div>

<?php include "../inc/footer.php"; ?>
