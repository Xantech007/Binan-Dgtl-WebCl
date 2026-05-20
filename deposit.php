<?php
session_start();
require_once "config/database.php";

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

if(!isset($_GET['id'])){
    echo "Invalid payment method";
    exit;
}

$method_id = intval($_GET['id']);

$stmt = $pdo->prepare("SELECT * FROM payment_methods WHERE id=?");
$stmt->execute([$method_id]);
$method = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$method){
    echo "Payment method not found";
    exit;
}

/* FETCH VIP ACTIVATION FEES */
$vipStmt = $pdo->prepare("SELECT activation_fee FROM vip ORDER BY activation_fee ASC");
$vipStmt->execute();
$vipPlans = $vipStmt->fetchAll(PDO::FETCH_ASSOC);

$msg = "";

if($_SERVER['REQUEST_METHOD'] == "POST"){

    $amount = $_POST['amount'];
    $paid_amount = $_POST['paid_amount'];
    $paid_currency = $_POST['paid_currency'];

    if(isset($_FILES['proof']) && $_FILES['proof']['error'] == 0){

        $upload_dir = "assets/images/proof/";

        if(!is_dir($upload_dir)){
            mkdir($upload_dir, 0777, true);
        }

        $file_name = time() . "_" . basename($_FILES["proof"]["name"]);
        $target_file = $upload_dir . $file_name;

        move_uploaded_file($_FILES["proof"]["tmp_name"], $target_file);

        $stmt = $pdo->prepare(
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

        $_SESSION['recharge_msg'] = "Recharge submitted successfully";

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

    <!-- QR -->
    <?php if(!empty($method['qr_image'])): ?>
        <div class="deposit-qr">
            <img src="<?php echo htmlspecialchars($method['qr_image']); ?>">
        </div>
    <?php endif; ?>


    <?php if($method['crypto'] == 1): ?>

        <div class="deposit-address-title">Address</div>

        <div class="deposit-address">
            <input type="text"
                   value="<?php echo htmlspecialchars($method['wallet_address']); ?>"
                   id="walletAddress"
                   readonly>

            <button type="button" onclick="copyAddress()">Copy</button>
        </div>

    <?php else: ?>

        <div class="deposit-address-title">
            <?php echo ($method['type'] == "bank") ? "Bank Details" : "MOMO Details"; ?>
        </div>

        <div class="deposit-address">
            <input type="text"
                   value="<?php echo htmlspecialchars($method['network']); ?>"
                   readonly>
        </div>

        <div class="deposit-address">
            <input type="text"
                   value="<?php echo htmlspecialchars($method['account_name']); ?>"
                   readonly>
        </div>

        <div class="deposit-address">
            <input type="text"
                   value="<?php echo htmlspecialchars($method['account_number']); ?>"
                   id="accountNumber"
                   readonly>

            <button type="button" onclick="copyAccount()">Copy</button>
        </div>

    <?php endif; ?>


    <form method="POST" enctype="multipart/form-data">

        <!-- AMOUNT DROPDOWN -->
        <div class="upload-proof">
        
            <label>Select Amount (USD)</label>
        
            <div class="deposit-address">
        
                <select id="usdAmount" name="amount" required
                style="width:100%;border:none;background:none;outline:none;font-size:15px;">
        
                    <option value="">-- Select Amount --</option>
        
                    <?php foreach($vipPlans as $plan): ?>
        
                        <option value="<?php echo $plan['activation_fee']; ?>">
        
                            $<?php echo number_format($plan['activation_fee'], 2); ?>
        
                        </option>
        
                    <?php endforeach; ?>
        
                </select>
        
            </div>
        
        </div>
        <!-- CONVERTED AMOUNT -->
        <div class="upload-proof">

            <label>
                Amount to Pay
                (<span id="currencyLabel">
                    <?php echo htmlspecialchars($method['currency']); ?>
                </span>)
            </label>

            <input type="text" id="convertedAmount" readonly>

            <input type="hidden" name="paid_amount" id="paidAmountInput">

            <input type="hidden"
                   name="paid_currency"
                   value="<?php echo htmlspecialchars($method['currency']); ?>">

        </div>

        <!-- PROOF -->
        <div class="upload-proof">
            <label>Upload payment proof</label>

            <input type="file"
                   name="proof"
                   accept="image/*"
                   required>
        </div>

        <button class="deposit-btn">
            Recharge completed
        </button>

    </form>

    <div class="deposit-note">

        <?php if($method['crypto'] == 1): ?>

            Note. Please use the correct cryptocurrency network when depositing.

        <?php elseif($method['type'] == "bank"): ?>

            Note. Transfer the exact amount to the bank account above and upload the receipt.

        <?php else: ?>

            Note. Send the payment using MOMO to the number above and upload proof.

        <?php endif; ?>

    </div>

</div>

<?php include "inc/footer.php"; ?>

<script>

/* COPY ADDRESS */
function copyAddress(){

    const el = document.getElementById("walletAddress");

    if(!el) return;

    navigator.clipboard.writeText(el.value);
}

/* COPY ACCOUNT */
function copyAccount(){

    const el = document.getElementById("accountNumber");

    if(!el) return;

    navigator.clipboard.writeText(el.value);
}

/* CONVERSION RATE */
const rate = <?php echo $method['conversion_rate'] ?: 1; ?>;

const usdInput = document.getElementById("usdAmount");
const converted = document.getElementById("convertedAmount");
const hiddenPaid = document.getElementById("paidAmountInput");

/* UPDATE CONVERTED AMOUNT */
usdInput.addEventListener("change", function(){

    let usd = parseFloat(this.value) || 0;

    let convertedAmount = usd * rate;

    converted.value = convertedAmount.toFixed(2);

    hiddenPaid.value = convertedAmount.toFixed(2);

});

</script>
