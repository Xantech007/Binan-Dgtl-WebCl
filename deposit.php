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

/* FETCH VIP ACTIVATION FEES + LINKS */
$vipStmt = $pdo->prepare("
    SELECT activation_fee, link 
    FROM vip 
    ORDER BY activation_fee ASC
");
$vipStmt->execute();
$vipPlans = $vipStmt->fetchAll(PDO::FETCH_ASSOC);

$msg = "";

/* ================= FORM SUBMIT ================= */

if($_SERVER['REQUEST_METHOD'] == "POST"){

    $amount = (float)($_POST['amount'] ?? 0);

    if($amount <= 0){
        $msg = "Please select amount.";
    }else{

        /* ================= PAYSTACK ================= */

        if($method['type'] == "paystack"){

            /* FIND VIP LINK */
            $vipStmt = $pdo->prepare("
                SELECT link 
                FROM vip 
                WHERE activation_fee=? 
                LIMIT 1
            ");

            $vipStmt->execute([$amount]);

            $vip = $vipStmt->fetch(PDO::FETCH_ASSOC);

            /* LOG DEPOSIT FIRST */
            $depositStmt = $pdo->prepare("
                INSERT INTO deposits
                (
                    user_id,
                    method_id,
                    amount,
                    paid_amount,
                    paid_currency,
                    proof,
                    paystack
                )
                VALUES(?,?,?,?,?,?,?)
            ");

            $depositStmt->execute([
                $user_id,
                $method_id,
                $amount,
                $amount,
                $method['currency'] ?: 'USD',
                '',
                'yes'
            ]);

            if($vip && !empty($vip['link'])){

                header("Location: " . $vip['link']);
                exit;

            }else{

                $msg = "Payment link not found.";

            }

        }

        /* ================= NORMAL METHODS ================= */

        else{

            $paid_amount = $_POST['paid_amount'] ?? 0;
            $paid_currency = $_POST['paid_currency'] ?? 'USD';

            if(isset($_FILES['proof']) && $_FILES['proof']['error'] == 0){

                $upload_dir = "assets/images/proof/";

                if(!is_dir($upload_dir)){
                    mkdir($upload_dir, 0777, true);
                }

                $file_name = time() . "_" . basename($_FILES["proof"]["name"]);

                $target_file = $upload_dir . $file_name;

                move_uploaded_file($_FILES["proof"]["tmp_name"], $target_file);

                $stmt = $pdo->prepare("
                    INSERT INTO deposits
                    (
                        user_id,
                        method_id,
                        amount,
                        paid_amount,
                        paid_currency,
                        proof,
                        paystack
                    )
                    VALUES(?,?,?,?,?,?,?)
                ");

                $stmt->execute([
                    $user_id,
                    $method_id,
                    $amount,
                    $paid_amount,
                    $paid_currency,
                    $target_file,
                    'no'
                ]);

                $_SESSION['recharge_msg'] = "Recharge submitted successfully";

                header("Location: index.php");
                exit;

            }else{

                $msg = "Please upload payment proof.";

            }

        }

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

    <!-- TOP -->
    <div class="deposit-top">

        <img src="assets/images/logo.webp" class="deposit-logo">

        <span>BINANCE DIGITAL</span>

    </div>

    <!-- METHOD -->
    <div class="deposit-method">

        <?php if(!empty($method['image'])): ?>

            <img src="<?php echo htmlspecialchars($method['image']); ?>"
                 class="method-icon">

        <?php endif; ?>

        <span><?php echo htmlspecialchars($method['name']); ?></span>

    </div>

    <!-- QR IMAGE -->
    <?php if(!empty($method['qr_image'])): ?>

        <div class="deposit-qr">

            <img src="<?php echo htmlspecialchars($method['qr_image']); ?>">

        </div>

    <?php endif; ?>

    <!-- NORMAL METHODS ONLY -->
    <?php if($method['type'] != "paystack"): ?>

        <?php if($method['crypto'] == 1): ?>

            <div class="deposit-address-title">
                Address
            </div>

            <div class="deposit-address">

                <input type="text"
                       value="<?php echo htmlspecialchars($method['wallet_address']); ?>"
                       id="walletAddress"
                       readonly>

                <button type="button" onclick="copyAddress()">
                    Copy
                </button>

            </div>

        <?php else: ?>

            <div class="deposit-address-title">

                <?php echo ($method['type'] == "bank") 
                    ? "Bank Details" 
                    : "MOMO Details"; ?>

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

                <button type="button" onclick="copyAccount()">
                    Copy
                </button>

            </div>

        <?php endif; ?>

    <?php endif; ?>

    <!-- ERROR MESSAGE -->
    <?php if(!empty($msg)): ?>

        <div class="deposit-msg">
            <?php echo $msg; ?>
        </div>

    <?php endif; ?>

    <!-- FORM -->
    <form method="POST" enctype="multipart/form-data">

        <!-- SELECT AMOUNT -->
        <div class="upload-proof">

            <label>Select Amount (USD)</label>

            <div class="deposit-address">

                <select id="usdAmount" name="amount" required>

                    <option value="">
                        -- Select Amount --
                    </option>

                    <?php foreach($vipPlans as $plan): ?>

                        <option value="<?php echo $plan['activation_fee']; ?>">

                            $<?php echo number_format($plan['activation_fee'], 2); ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>

        </div>

        <!-- NORMAL METHODS ONLY -->
        <?php if($method['type'] != "paystack"): ?>

            <!-- CONVERTED AMOUNT -->
            <div class="upload-proof">

                <label>
                    Amount to Pay
                    (
                    <span id="currencyLabel">
                        <?php echo htmlspecialchars($method['currency']); ?>
                    </span>
                    )
                </label>

                <input type="text"
                       id="convertedAmount"
                       readonly>

                <input type="hidden"
                       name="paid_amount"
                       id="paidAmountInput">

                <input type="hidden"
                       name="paid_currency"
                       value="<?php echo htmlspecialchars($method['currency']); ?>">

            </div>

            <!-- PROOF -->
            <div class="upload-proof">

                <label>
                    Upload payment proof
                </label>

                <input type="file"
                       name="proof"
                       accept="image/*"
                       required>

            </div>

        <?php endif; ?>

        <!-- BUTTON -->
        <button class="deposit-btn">

            <?php echo ($method['type'] == "paystack")
                ? "Proceed to Pay"
                : "Recharge completed"; ?>

        </button>

    </form>

    <!-- NOTE -->
    <?php if($method['type'] != "paystack"): ?>

        <div class="deposit-note">

            <?php if($method['crypto'] == 1): ?>

                Note. Please use the correct cryptocurrency network when depositing.

            <?php elseif($method['type'] == "bank"): ?>

                Note. Transfer the exact amount to the bank account above and upload the receipt.

            <?php else: ?>

                Note. Send the payment using MOMO to the number above and upload proof.

            <?php endif; ?>

        </div>

    <?php endif; ?>

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

/* ================= CONVERSION ================= */

const rate = <?php echo $method['conversion_rate'] ?: 1; ?>;

const usdInput = document.getElementById("usdAmount");

const converted = document.getElementById("convertedAmount");

const hiddenPaid = document.getElementById("paidAmountInput");

/* ONLY FOR NORMAL METHODS */
if(usdInput && converted){

    usdInput.addEventListener("change", function(){

        let usd = parseFloat(this.value) || 0;

        let convertedAmount = usd * rate;

        converted.value = convertedAmount.toFixed(2);

        hiddenPaid.value = convertedAmount.toFixed(2);

    });

}

</script>
