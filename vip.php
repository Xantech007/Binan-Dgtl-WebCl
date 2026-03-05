<?php
session_start();

if(!isset($_SESSION['user_id'])){
header("Location: login.php");
exit;
}

require_once "config/database.php";

$user_id = $_SESSION['user_id'];

/* USER INFO */

$stmt = $pdo->prepare("SELECT balance,vip_level FROM users WHERE id=?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$balance = $user['balance'];
$current_vip = $user['vip_level'];

$msg="";


/* ACTIVATE VIP */

if(isset($_POST['vip_id'])){

$vip_id = $_POST['vip_id'];

$stmt = $pdo->prepare("SELECT activation_fee FROM vip WHERE id=?");
$stmt->execute([$vip_id]);
$vip = $stmt->fetch(PDO::FETCH_ASSOC);

$fee = $vip['activation_fee'];

if($balance < $fee){

header("Location: recharge.php");
exit;

}else{

$new_balance = $balance - $fee;

$stmt = $pdo->prepare("UPDATE users SET balance=?, vip_level=? WHERE id=?");
$stmt->execute([$new_balance,$vip_id,$user_id]);

$msg="VIP activated successfully";

$balance = $new_balance;
$current_vip = $vip_id;

}

}


/* FETCH VIP PLANS */

$vipQuery = $pdo->query("SELECT * FROM vip WHERE status=1 ORDER BY id ASC");

?>

<?php include "inc/header.php"; ?>


<div class="vip-container">

<?php if($msg): ?>

<div class="vip-success">
<?php echo $msg; ?>
</div>

<?php endif; ?>


<?php while($vip=$vipQuery->fetch(PDO::FETCH_ASSOC)): ?>

<div class="vip-card">

<div class="vip-label">
<?php echo $vip['name']; ?>
</div>


<div class="vip-left">

<img src="assets/images/logo-vip.png">

</div>


<div class="vip-info">

<div>
<span>Daily tasks</span>
<strong><?php echo $vip['daily_tasks']; ?></strong>
</div>

<div>
<span>Simple interest</span>
<strong class="green"><?php echo $vip['simple_interest']; ?></strong>
</div>

<div>
<span>Daily profit</span>
<strong><?php echo number_format($vip['daily_profit'],2); ?> USDT</strong>
</div>

<div>
<span>The total profit</span>
<strong><?php echo number_format($vip['total_profit'],2); ?> USDT</strong>
</div>

</div>


<div class="vip-action">

<?php if($current_vip >= $vip['id']): ?>

<button class="vip-active">
Activated
</button>

<?php else: ?>

<button onclick="openConfirm(<?php echo $vip['id'];?>,<?php echo $vip['activation_fee'];?>)">
<?php echo number_format($vip['activation_fee'],2); ?> USDT Unlock now
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

<button type="submit" class="confirm-btn">
Confirm
</button>

<button type="button" onclick="closePopup()" class="cancel-btn">
Cancel
</button>

</form>

</div>

</div>


<script>

function openConfirm(id,fee){

document.getElementById("vip_id").value=id;
document.getElementById("vipPopup").style.display="flex";

}

function closePopup(){

document.getElementById("vipPopup").style.display="none";

}

</script>


<?php include "inc/footer.php"; ?>
