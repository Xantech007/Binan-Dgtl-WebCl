<?php
session_start();
require_once "config/database.php";

if(!isset($_SESSION['user_id'])){
header("Location: login.php");
exit;
}

$user_id=$_SESSION['user_id'];

/* GET USER */

$stmt=$pdo->prepare("SELECT balance FROM users WHERE id=?");
$stmt->execute([$user_id]);
$user=$stmt->fetch(PDO::FETCH_ASSOC);

$balance=$user['balance'];


/* GET ACTIVE VIP */

$stmt=$pdo->prepare("
SELECT * FROM user_vip
WHERE user_id=? AND status=1
ORDER BY vip_id DESC
LIMIT 1
");

$stmt->execute([$user_id]);
$active_vip=$stmt->fetch(PDO::FETCH_ASSOC);


/* AUTO EXPIRE */

if($active_vip){

if(strtotime($active_vip['end_time']) < time()){

$pdo->prepare("UPDATE user_vip SET status=0 WHERE id=?")
->execute([$active_vip['id']]);

$active_vip=null;

}

}


/* ACTIVATE VIP */

if(isset($_POST['activate_vip'])){

$vip_id=$_POST['vip_id'];

$stmt=$pdo->prepare("SELECT * FROM vip WHERE id=?");
$stmt->execute([$vip_id]);
$vip=$stmt->fetch(PDO::FETCH_ASSOC);

$fee=$vip['activation_fee'];
$duration=$vip['duration_days'];

if($balance < $fee){

header("Location: recharge.php");
exit;

}

/* START TIME */

$start=date("Y-m-d H:i:s");
$end=date("Y-m-d H:i:s",strtotime("+$duration days"));

/* DEDUCT BALANCE */

$new_balance=$balance-$fee;

$pdo->prepare("UPDATE users SET balance=? WHERE id=?")
->execute([$new_balance,$user_id]);

/* SAVE VIP */

$pdo->prepare("
INSERT INTO user_vip (user_id,vip_id,start_time,end_time)
VALUES (?,?,?,?)
")->execute([$user_id,$vip_id,$start,$end]);

$_SESSION['vip_msg']="VIP $vip_id activated";

header("Location: vip.php");
exit;

}


/* GET VIP LIST */

$vipQuery=$pdo->query("SELECT * FROM vip ORDER BY id ASC");

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


<?php while($vip=$vipQuery->fetch(PDO::FETCH_ASSOC)): ?>

<?php
$isActive = $active_vip && $active_vip['vip_id']==$vip['id'];
?>

<div class="vip-card">

<!-- LEFT VIP LABEL -->

<div class="vip-label">
VIP<?php echo $vip['id']; ?>
</div>


<!-- RIGHT EFFECTIVE LABEL -->

<?php if($isActive): ?>

<div class="vip-status">
<i class="fa fa-lock"></i> Unlock:Effective
</div>

<?php endif; ?>


<div class="vip-row">

<div class="vip-left">
<img src="assets/images/logo-vip.png">
</div>


<div class="vip-details">

<div class="label">Daily tasks</div>
<div class="value">1</div>

<div class="label">Simple interest</div>
<div class="value green">
<?php echo number_format($vip['daily_profit'],2); ?>
</div>

<div class="label">Daily profit</div>
<div class="value">
<?php echo number_format($vip['daily_profit'],2); ?>
<span class="usdt">USDT</span>
</div>

<div class="label">The total profit</div>
<div class="value">
<?php echo number_format($vip['total_profit'],2); ?>
<span class="usdt">USDT</span>
</div>

</div>

</div>


<div class="vip-action">

<?php if($isActive): ?>

<div class="vip-time">

Effective time:

<?php echo date("d/m/Y H:i:s",strtotime($active_vip['start_time'])); ?>

-

<?php echo date("d/m/Y H:i:s",strtotime($active_vip['end_time'])); ?>

</div>

<?php else: ?>

<button onclick="openPopup(<?php echo $vip['id']; ?>)">

<?php echo number_format($vip['activation_fee'],2); ?> USDT
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


<?php include "inc/footer.php"; ?>


<script>

function openPopup(id){

document.getElementById("vipPopup").style.display="flex";
document.getElementById("vip_id").value=id;

}

function closePopup(){

document.getElementById("vipPopup").style.display="none";

}

</script>
