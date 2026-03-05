<?php
session_start();
require_once "config/database.php";

if(!isset($_SESSION['user_id'])){
header("Location: login.php");
exit;
}

$user_id = $_SESSION['user_id'];

/* FETCH USER */
$stmt = $pdo->prepare("SELECT balance,vip_level FROM users WHERE id=?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$balance = $user['balance'];
$current_vip = $user['vip_level'];

$msg = "";

/* HANDLE VIP ACTIVATION */

if(isset($_POST['activate_vip'])){

$vip_id = $_POST['vip_id'];

$stmt = $pdo->prepare("SELECT * FROM vip WHERE id=?");
$stmt->execute([$vip_id]);
$vip = $stmt->fetch(PDO::FETCH_ASSOC);

$fee = $vip['activation_fee'];

if($balance < $fee){

header("Location: recharge.php");
exit;

}

/* UPDATE USER */

$new_balance = $balance - $fee;

$update = $pdo->prepare("UPDATE users SET balance=?, vip_level=? WHERE id=?");
$update->execute([$new_balance,$vip_id,$user_id]);

$_SESSION['vip_msg'] = "VIP".$vip_id." activated successfully";

header("Location: vip.php");
exit;

}

$vipQuery = $pdo->query("SELECT * FROM vip ORDER BY id ASC");

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


<?php while($vip = $vipQuery->fetch(PDO::FETCH_ASSOC)): ?>

<div class="vip-card">

<div class="vip-label">
VIP<?php echo $vip['id']; ?>
</div>

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


<div class="vip-action">

<?php if($current_vip >= $vip['id']): ?>

<button class="vip-active">
Activated
</button>

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


<!-- POPUP -->

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
