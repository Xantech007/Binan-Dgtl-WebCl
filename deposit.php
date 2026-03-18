<?php
session_start();
require_once "config/database.php";

if(!isset($_SESSION['user_id'])){
header("Location: login.php");
exit;
}

$user_id=$_SESSION['user_id'];

if(!isset($_GET['id'])){
echo "Invalid payment method";
exit;
}

$method_id=intval($_GET['id']);

$stmt=$pdo->prepare("SELECT * FROM payment_methods WHERE id=?");
$stmt->execute([$method_id]);
$method=$stmt->fetch(PDO::FETCH_ASSOC);

if(!$method){
echo "Payment method not found";
exit;
}

$msg="";

if($_SERVER['REQUEST_METHOD']=="POST"){

$amount=$_POST['amount'];
$paid_amount=$_POST['paid_amount'];
$paid_currency=$_POST['paid_currency'];

if(isset($_FILES['proof']) && $_FILES['proof']['error']==0){

$upload_dir="assets/images/proof/";

if(!is_dir($upload_dir)){
mkdir($upload_dir,0777,true);
}

$file_name=time()."_".basename($_FILES["proof"]["name"]);
$target_file=$upload_dir.$file_name;

move_uploaded_file($_FILES["proof"]["tmp_name"],$target_file);

$stmt=$pdo->prepare(
"INSERT INTO deposits(user_id,method_id,amount,paid_amount,paid_currency,proof)
VALUES(?,?,?,?,?,?)"
);

$stmt->execute([
$user_id,
$method_id,
$amount,
$paid_amount,
$paid_currency,
$target_file
]);

$_SESSION['recharge_msg']="Recharge submitted successfully";
header("Location: index.php");
exit;

}

}
?>

<?php include "inc/header.php"; ?>

<div class="deposit-header">
<a href="recharge.php">
<i class="fa fa-arrow-left"></i>
</a>
<span>Recharge</span>
</div>

<div class="deposit-container">

<div class="deposit-top">
<img src="assets/images/logo.webp" class="deposit-logo">
<span>BINANCE DIGITAL</span>
</div>

<div class="deposit-method">

<?php if(!empty($method['image'])): ?>
<img src="<?php echo htmlspecialchars($method['image']); ?>" class="method-icon">
<?php endif; ?>

<span><?php echo htmlspecialchars($method['name']); ?></span>

</div>


<!-- QR IMAGE (SHOW FOR ALL METHODS) -->

<?php if(!empty($method['qr_image'])): ?>

<div class="deposit-qr">
<img src="<?php echo htmlspecialchars($method['qr_image']); ?>">
</div>

<?php endif; ?>


<?php if($method['crypto']==1): ?>

<!-- CRYPTO SECTION -->

<div class="deposit-address-title">
Address
</div>

<div class="deposit-address">

<input type="text"
value="<?php echo htmlspecialchars($method['wallet_address']); ?>"
id="walletAddress"
readonly>

<button type="button" onclick="copyAddress()">Copy</button>

</div>

<?php else: ?>
  
<!-- BANK / MOMO SECTION -->

<div class="deposit-info">

<?php if($method['type']=="bank"): ?>

<p><strong>Bank:</strong> <?php echo htmlspecialchars($method['network']); ?></p>

<?php else: ?>

<p><strong>Network:</strong> <?php echo htmlspecialchars($method['network']); ?></p>

<?php endif; ?>

<p><strong>
<?php echo ($method['type']=="bank") ? "Account Name" : "MOMO Name"; ?>
:</strong>
<?php echo htmlspecialchars($method['account_name']); ?>
</p>

<p>
<strong>
<?php echo ($method['type']=="bank") ? "Account Number" : "MOMO Number"; ?>
:</strong>

<span id="accountNumber" class="copy-number">
<?php echo htmlspecialchars($method['account_number']); ?>
</span>

</p>

</div>

<?php endif; ?>


<form method="POST" enctype="multipart/form-data">

<div class="upload-proof">

<label>Enter Amount (USD)</label>
<input type="number" id="usdAmount" name="amount" step="0.01" required>

</div>

<div class="upload-proof">

<label>Amount to Pay (<span id="currencyLabel"><?php echo $method['currency']; ?></span>)</label>

<input type="text" id="convertedAmount" readonly>

<input type="hidden" name="paid_amount" id="paidAmountInput">
<input type="hidden" name="paid_currency" value="<?php echo htmlspecialchars($method['currency']); ?>">

</div>

<div class="upload-proof">

<label>Upload payment proof</label>

<input type="file" name="proof" accept="image/*" required>

</div>

<button class="deposit-btn">
Recharge completed
</button>

</form>


<div class="deposit-note">

<?php if($method['crypto']==1): ?>

Note. Please use the correct cryptocurrency network when depositing.

<?php elseif($method['type']=="bank"): ?>

Note. Transfer the exact amount to the bank account above and upload the receipt.

<?php else: ?>

Note. Send the payment using MOMO to the number above and upload proof.

<?php endif; ?>

</div>

</div>

<?php include "inc/footer.php"; ?>

<script>

let pressTimer;
const accountEl = document.getElementById("accountNumber");

if(accountEl){

accountEl.addEventListener("touchstart", startPress);
accountEl.addEventListener("mousedown", startPress);

accountEl.addEventListener("touchend", cancelPress);
accountEl.addEventListener("mouseup", cancelPress);
accountEl.addEventListener("mouseleave", cancelPress);

function startPress(){
    pressTimer = setTimeout(copyAccountNumber, 1500); // 1.5 seconds
}

function cancelPress(){
    clearTimeout(pressTimer);
}

function copyAccountNumber(){

    const text = accountEl.innerText.trim();

    navigator.clipboard.writeText(text).then(() => {
        alert("Account number copied");
    });

}

}

  

function copyAddress(){

var copyText=document.getElementById("walletAddress");

copyText.select();
copyText.setSelectionRange(0,99999);

navigator.clipboard.writeText(copyText.value);

alert("Address copied");

}


const rate = <?php echo $method['conversion_rate'] ?: 1; ?>;

const usdInput = document.getElementById("usdAmount");
const converted = document.getElementById("convertedAmount");
const hiddenPaid = document.getElementById("paidAmountInput");

usdInput.addEventListener("input", function(){

let usd = parseFloat(this.value) || 0;

let convertedAmount = usd * rate;

converted.value = convertedAmount.toFixed(2);

hiddenPaid.value = convertedAmount.toFixed(2);

});
  
</script>
