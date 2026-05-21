<?php
session_start();

require_once "config/database.php";

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];


/* AUTO EXPIRE ALL VIPs */

$pdo->prepare("
UPDATE user_vip
SET status = 0
WHERE end_time <= NOW()
")->execute();


/* GET USER */

$stmt = $pdo->prepare("
SELECT balance, vip_level
FROM users
WHERE id=?
");

$stmt->execute([$user_id]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

$balance = $user['balance'];
$current_vip_level = $user['vip_level'];


/* GET ACTIVE VIPS */

$stmt = $pdo->prepare("
SELECT *
FROM user_vip
WHERE user_id=? AND status=1
");

$stmt->execute([$user_id]);

$active_vips = $stmt->fetchAll(PDO::FETCH_ASSOC);


/* ACTIVATE VIP */

if(isset($_POST['activate_vip'])){

    $vip_id = (int)$_POST['vip_id'];

    /* CHECK IF USER HAS EVER ACTIVATED THIS VIP BEFORE */

    $check = $pdo->prepare("
    SELECT id
    FROM user_vip
    WHERE user_id=? AND vip_id=?
    LIMIT 1
    ");

    $check->execute([$user_id, $vip_id]);

    if($check->fetch()){

        $_SESSION['vip_error'] = "You have already unlocked and mined this VIP plan before.";

        header("Location: vip.php");
        exit;
    }


    /* GET VIP */

    $stmt = $pdo->prepare("
    SELECT *
    FROM vip
    WHERE id=?
    ");

    $stmt->execute([$vip_id]);

    $vip = $stmt->fetch(PDO::FETCH_ASSOC);

    if(!$vip){

        $_SESSION['vip_error'] = "VIP plan not found.";

        header("Location: vip.php");
        exit;
    }

    $fee = $vip['activation_fee'];
    $duration = $vip['duration_days'];


    /* CHECK BALANCE */

    if($balance < $fee){

        $_SESSION['vip_error'] = "Insufficient balance. Please recharge.";

        header("Location: recharge.php");
        exit;
    }


    /* START +5 HOURS */

    $start = date("Y-m-d H:i:s", strtotime("+5 hours"));

    $end = date(
        "Y-m-d H:i:s",
        strtotime("+$duration days +5 hours")
    );


    /* DEDUCT BALANCE */

    $new_balance = $balance - $fee;

    $pdo->prepare("
    UPDATE users
    SET balance=?
    WHERE id=?
    ")->execute([$new_balance, $user_id]);


    /* SAVE VIP */

    $pdo->prepare("
    INSERT INTO user_vip
    (
        user_id,
        vip_id,
        start_time,
        end_time,
        status
    )
    VALUES
    (
        ?,
        ?,
        ?,
        ?,
        1
    )
    ")->execute([
        $user_id,
        $vip_id,
        $start,
        $end
    ]);


    /* UPDATE USER VIP LEVEL */

    if($vip_id > $current_vip_level){

        $pdo->prepare("
        UPDATE users
        SET vip_level=?
        WHERE id=?
        ")->execute([
            $vip_id,
            $user_id
        ]);
    }


    /* SUCCESS MESSAGE */

    $_SESSION['vip_msg'] = "VIP ".$vip_id." activated successfully.";

    header("Location: vip.php");
    exit;
}


/* GET VIP LIST */

$vipQuery = $pdo->query("
SELECT *
FROM vip
ORDER BY id ASC
");

?>

<?php include "inc/header.php"; ?>


<div class="vip-container">


<?php if(isset($_SESSION['vip_msg'])): ?>

<div class="vip-success">

<?php
echo $_SESSION['vip_msg'];
unset($_SESSION['vip_msg']);
?>

</div>

<?php endif; ?>


<?php if(isset($_SESSION['vip_error'])): ?>

<div class="vip-error">

<?php
echo $_SESSION['vip_error'];
unset($_SESSION['vip_error']);
?>

</div>

<?php endif; ?>



<?php while($vip = $vipQuery->fetch(PDO::FETCH_ASSOC)): ?>

<?php

$isActive = false;
$alreadyUsed = false;

$start_time = "";
$end_time = "";


/* CHECK ACTIVE VIP */

foreach($active_vips as $a){

    if($a['vip_id'] == $vip['id']){

        $isActive = true;

        $start_time = $a['start_time'];
        $end_time = $a['end_time'];

        break;
    }
}


/* CHECK IF USER HAS USED THIS VIP BEFORE */

$usedCheck = $pdo->prepare("
SELECT id
FROM user_vip
WHERE user_id=? AND vip_id=?
LIMIT 1
");

$usedCheck->execute([$user_id, $vip['id']]);

if($usedCheck->fetch()){
    $alreadyUsed = true;
}

?>


<div class="vip-card">


<!-- LEFT VIP LABEL -->

<div class="vip-label">
VIP<?php echo $vip['id']; ?>
</div>


<!-- STATUS -->

<?php if($isActive): ?>

<div class="vip-status">
<i class="fa fa-unlock"></i>
Unlock: Effective
</div>

<?php endif; ?>


<div class="vip-row">

<div class="vip-left">
<img src="assets/images/logo-vip.png">
</div>


<div class="vip-details">

<div class="label">Daily tasks</div>
<div class="value">
<?php echo $vip['daily_tasks']; ?>
</div>


<div class="label">Simple interest</div>
<div class="value green">
<?php echo number_format($vip['simple_interest'],2); ?>%
</div>


<div class="label">Daily profit</div>
<div class="value">
<?php echo number_format($vip['daily_profit'],2); ?>
<span class="usdt">USD</span>
</div>


<div class="label">The total profit</div>
<div class="value">
<?php echo number_format($vip['total_profit'],2); ?>
<span class="usdt">USD</span>
</div>

</div>

</div>


<div class="vip-action">


<?php if($isActive): ?>

<div class="vip-time">

Effective time:

<?php echo date("d/m/Y H:i:s", strtotime($start_time)); ?>

-

<?php echo date("d/m/Y H:i:s", strtotime($end_time)); ?>

</div>


<?php elseif($alreadyUsed): ?>

<button
type="button"
class="vip-used-btn"
onclick="usedVipPopup()"
>

Already Mined

</button>


<?php else: ?>

<button onclick="openPopup(<?php echo $vip['id']; ?>)">

<?php echo number_format($vip['activation_fee'],2); ?> USD

Unlock now

</button>

<?php endif; ?>

</div>

</div>

<?php endwhile; ?>

</div>



<!-- CONFIRM POPUP -->

<div class="vip-popup" id="vipPopup">

<div class="popup-box">

<p>Confirm VIP activation?</p>

<form method="POST">

<input type="hidden" name="vip_id" id="vip_id">

<button type="submit" name="activate_vip" class="confirm-btn">
Confirm
</button>

<button type="button" class="cancel-btn" onclick="closePopup()">
Cancel
</button>

</form>

</div>

</div>



<!-- USED VIP POPUP -->

<div class="vip-popup" id="usedVipPopup">

<div class="popup-box">

<p style="margin-bottom:20px;">
You have already unlocked and mined this VIP plan once.
</p>

<button
type="button"
class="cancel-btn"
onclick="closeUsedPopup()"
>
OK
</button>

</div>

</div>


<?php include "inc/footer.php"; ?>


<style>

.vip-error{
background:#f85149;
color:#fff;
padding:14px;
border-radius:8px;
margin-bottom:20px;
text-align:center;
}

.vip-used-btn{
background:#666;
color:#fff;
border:none;
padding:12px 18px;
border-radius:8px;
cursor:pointer;
font-weight:bold;
}

</style>


<script>

function openPopup(id){

    document.getElementById("vipPopup").style.display = "flex";

    document.getElementById("vip_id").value = id;
}

function closePopup(){

    document.getElementById("vipPopup").style.display = "none";
}


function usedVipPopup(){

    document.getElementById("usedVipPopup").style.display = "flex";
}

function closeUsedPopup(){

    document.getElementById("usedVipPopup").style.display = "none";
}

</script>
