<?php
session_start();
require_once "config/database.php";

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

/* Ensure id exists */
if(!isset($_GET['id'])){
    echo "Invalid payment method";
    exit;
}

$method_id = intval($_GET['id']);

/* Fetch payment method */
$stmt = $pdo->prepare("SELECT * FROM payment_methods WHERE id = ?");
$stmt->execute([$method_id]);
$method = $stmt->fetch(PDO::FETCH_ASSOC);

/* Check if method exists */
if(!$method){
    echo "Payment method not found";
    exit;
}

$msg="";

/* Handle proof upload */
if($_SERVER['REQUEST_METHOD']=="POST"){

    if(isset($_FILES['proof']) && $_FILES['proof']['error']==0){

        $upload_dir="assets/images/proof/";

        if(!is_dir($upload_dir)){
            mkdir($upload_dir,0777,true);
        }

        $file_name=time()."_".basename($_FILES["proof"]["name"]);
        $target_file=$upload_dir.$file_name;

        move_uploaded_file($_FILES["proof"]["tmp_name"],$target_file);

        $stmt=$pdo->prepare(
        "INSERT INTO deposits(user_id,method_id,proof)
        VALUES(?,?,?)"
        );

        $stmt->execute([$user_id,$method_id,$target_file]);

        $msg="Recharge submitted successfully";
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

<!-- Payment Method -->
<div class="deposit-method">

<?php if(!empty($method['image'])): ?>
<img src="<?php echo htmlspecialchars($method['image']); ?>" class="method-icon">
<?php endif; ?>

<span><?php echo htmlspecialchars($method['name']); ?></span>

</div>

<!-- QR CODE -->
<div class="deposit-qr">

<?php if(!empty($method['qr_image'])): ?>

<img src="<?php echo htmlspecialchars($method['qr_image']); ?>" alt="QR Code">

<?php else: ?>

<p style="color:white;text-align:center;">QR Code not available</p>

<?php endif; ?>

</div>

<!-- ADDRESS -->
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

<!-- Upload Proof -->
<form method="POST" enctype="multipart/form-data">

<div class="upload-proof">
<label>Upload payment proof</label>
<input type="file" name="proof" required>
</div>

<button class="deposit-btn">
Recharge completed
</button>

</form>

<?php if($msg): ?>

<div class="deposit-msg">
<?php echo $msg; ?>
</div>

<?php endif; ?>

<div class="deposit-note">

Note. Please use the corresponding cryptocurrency for deposits. Deposits below 2 USDT and USDC will not be credited, below 10 TRX, below 0.003 BNB, below 0.001 ETH, and below 1 POLYGON.

</div>

</div>

<?php include "inc/footer.php"; ?>

<script>

function copyAddress(){

var copyText=document.getElementById("walletAddress");

copyText.select();
copyText.setSelectionRange(0,99999);

navigator.clipboard.writeText(copyText.value);

alert("Address copied");

}

</script>
