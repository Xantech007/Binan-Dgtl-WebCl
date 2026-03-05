<?php
session_start();
require_once "config/database.php";

if(!isset($_SESSION['user_id'])){
header("Location: login.php");
exit;
}

$user_id = $_SESSION['user_id'];

/* FETCH USER BALANCE */

$stmt = $pdo->prepare("SELECT balance FROM users WHERE id=?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$balance = $user['balance'];
?>

<?php include "inc/header.php"; ?>


<div class="balance-header">

<a href="#" onclick="goBack()">
<i class="fa fa-arrow-left"></i>
</a>

</div>


<div class="balance-container">

<div class="balance-card">

<div class="balance-left">

<div class="balance-item">

<p>Basic account</p>

<h3>
<?php echo number_format($balance,2); ?>
<span>USDT</span>
</h3>

</div>


<div class="balance-item">

<p>Withdrawal account</p>

<h3>
<?php echo number_format($balance,2); ?>
<span>USDT</span>
</h3>

</div>

</div>


<div class="balance-right">

<img src="assets/images/bag.png">

</div>

</div>

</div>



<?php include "inc/footer.php"; ?>


<script>

function goBack(){

if(document.referrer){
window.history.back();
}else{
window.location.href="index.php";
}

}

</script>
