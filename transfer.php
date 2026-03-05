<?php
session_start();
require_once "config/database.php";

if(!isset($_SESSION['user_id'])){
header("Location: login.php");
exit;
}

$user_id = $_SESSION['user_id'];

/* GET USER DATA */

$stmt = $pdo->prepare("
SELECT balance,withdrawal_balance,password
FROM users
WHERE id=?
");

$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$basic = $user['balance'];
$withdraw = $user['withdrawal_balance'];

$msg="";

if($_SERVER['REQUEST_METHOD']=="POST"){

$amount=floatval($_POST['amount']);
$password=$_POST['password'];

/* VERIFY PASSWORD */

if(!password_verify($password,$user['password'])){

$msg="Incorrect password";

}elseif($amount<=0){

$msg="Invalid amount";

}elseif($amount>$basic){

$msg="Insufficient balance";

}else{

/* TRANSFER BASIC → WITHDRAWAL */

$pdo->prepare("
UPDATE users
SET balance=balance-?,
withdrawal_balance=withdrawal_balance+?
WHERE id=?
")->execute([$amount,$amount,$user_id]);

$_SESSION['transfer_msg']="Transfer completed successfully";

header("Location: transfer.php");
exit;

}

}
?>

<?php include "inc/header.php"; ?>


<div class="transfer-header">

<a onclick="goBack()">
<i class="fa fa-arrow-left"></i>
</a>

<span>Transfer</span>

</div>



<?php if(isset($_SESSION['transfer_msg'])): ?>

<div class="transfer-success">
<?php
echo $_SESSION['transfer_msg'];
unset($_SESSION['transfer_msg']);
?>
</div>

<?php endif; ?>



<div class="transfer-wrapper">


<!-- BALANCE PANEL -->

<div class="transfer-balance">

<div class="transfer-box">

<p>Withdrawal account</p>
<h3><?php echo number_format($withdraw,2); ?></h3>

</div>

<div class="transfer-icon">
<i class="fa-solid fa-right-left"></i>
</div>

<div class="transfer-box">

<p>Basic account</p>
<h3><?php echo number_format($basic,2); ?></h3>

</div>

</div>



<!-- TRANSFER FORM -->

<div class="transfer-container">

<form method="POST">

<input
type="number"
step="0.01"
name="amount"
placeholder="Conversion quantity"
required
class="transfer-input">


<div class="password-box">

<input
type="password"
name="password"
placeholder="Password"
required
class="transfer-input">

<i class="fa fa-eye toggle-pass"></i>

</div>


<button class="transfer-btn">
Confirm
</button>

</form>


<?php if($msg): ?>

<div class="transfer-error">
<?php echo htmlspecialchars($msg); ?>
</div>

<?php endif; ?>

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


/* PASSWORD TOGGLE */

document.querySelector(".toggle-pass").onclick=function(){

let input=document.querySelector("input[name='password']");

input.type=input.type==="password"?"text":"password";

}

</script>
