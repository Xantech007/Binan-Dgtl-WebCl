<?php
session_start();
require_once "config/database.php";
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}
$user_id=$_SESSION['user_id'];

/* USER DATA */
$stmt=$pdo->prepare("SELECT id,email,balance,password,country FROM users WHERE id=?");
$stmt->execute([$user_id]);
$user=$stmt->fetch(PDO::FETCH_ASSOC);
$balance=$user['balance']; // stored in USD
$user_country=$user['country'];

/* FETCH PAYMENT METHODS */
$stmt=$pdo->prepare("
SELECT * FROM payment_methods
WHERE status=1
AND (active_country IS NULL OR active_country='' OR active_country=?)
ORDER BY id ASC
");
$stmt->execute([$user_country]);
$methods=$stmt->fetchAll(PDO::FETCH_ASSOC);

$msg="";
if($_SERVER['REQUEST_METHOD']=="POST"){
    $method=$_POST['method'];
    $amount_local=floatval($_POST['amount']); // user enters local currency
    $password=$_POST['password'];
    $address=trim($_POST['address'] ?? '');
    $network_bank=$_POST['network_bank'] ?? null;
    $account_name=$_POST['account_name'] ?? null;
    $account_number=$_POST['account_number'] ?? null;

    /* FETCH METHOD SETTINGS */
    $stmt=$pdo->prepare("SELECT conversion_rate,withdrawal_fee,min_withdraw,currency FROM payment_methods WHERE name=?");
    $stmt->execute([$method]);
    $methodData=$stmt->fetch(PDO::FETCH_ASSOC);
    $currency=$methodData['currency'];
    $rate=$methodData['conversion_rate'];
    $fee=$methodData['withdrawal_fee']; // fixed fee
    $minWithdraw=$methodData['min_withdraw'];

    /* CONVERT TO USD */
    $amount_usd=$amount_local / $rate;

    /* VALIDATIONS */
    if(!password_verify($password,$user['password'])){
        $msg="Incorrect password";
    }elseif($amount_usd>$balance){
        $msg="Insufficient balance";
    }elseif($amount_local<$minWithdraw){
        $msg="Minimum withdrawal is ".$minWithdraw;
    }elseif($amount_local<=0){
        $msg="Invalid withdrawal amount";
    }else{
        $received=$amount_local - $fee;

        /* SAVE WITHDRAWAL */
        $stmt=$pdo->prepare("
        INSERT INTO withdrawals
        (user_id,method,currency,amount,address,network_bank,account_name,account_number,fee,received)
        VALUES(?,?,?,?,?,?,?,?,?,?)
        ");
        $stmt->execute([
            $user_id,
            $method,
            $currency,
            $amount_usd,
            $address,
            $network_bank,
            $account_name,
            $account_number,
            $fee,
            $received
        ]);

        $withdrawal_id = $pdo->lastInsertId();

        /* DEDUCT USER BALANCE */
        $pdo->prepare("UPDATE users SET balance=balance-? WHERE id=?")
             ->execute([$amount_usd,$user_id]);

        /* REDIRECT TO RECEIPT WITH WITHDRAWAL ID */
        header("Location: withdraw-receipt.php?withdrawal_id=$withdrawal_id");
        exit;
    }
}
?>
<?php include "inc/header.php"; ?>
<div class="withdraw-header">
    <a href="#" onclick="goBack()">
        <i class="fa fa-arrow-left"></i>
    </a>
    <span>Withdraw</span>
</div>
<div class="withdraw-container">
    <h3>Withdrawal account</h3>
    <p class="withdraw-note">24 hours withdrawal</p>
    <div class="withdraw-balance">
        Total balance
        <strong><?php echo number_format($balance,2); ?> USD</strong>
    </div>
    <div class="withdraw-balance">
        Available balance
        <strong id="convertedBalance">0</strong>
    </div>
    <form method="POST">
        <label>Withdrawal method:</label>
        <div class="withdraw-methods">
            <?php foreach($methods as $m): ?>
            <label class="method">
                <input type="radio"
                       name="method"
                       value="<?php echo htmlspecialchars($m['name']); ?>"
                       data-type="<?php echo $m['crypto'] ? 'crypto' : $m['type']; ?>"
                       data-rate="<?php echo $m['conversion_rate']; ?>"
                       data-fee="<?php echo $m['withdrawal_fee']; ?>"
                       data-min="<?php echo $m['min_withdraw']; ?>"
                       data-currency="<?php echo $m['currency']; ?>"
                       required>
                <img src="<?php echo htmlspecialchars($m['image']); ?>" class="method-icon">
                <?php echo htmlspecialchars($m['name']); ?>
            </label>
            <?php endforeach; ?>
        </div>
        <input
            type="number"
            step="0.01"
            name="amount"
            id="amountInput"
            placeholder="Enter withdrawal amount"
            required
            class="withdraw-input">
        <div class="withdraw-note" id="usdEquivalent"></div>

        <!-- CRYPTO -->
        <div id="cryptoFields">
            <input
                type="text"
                name="address"
                placeholder="Withdrawal Address"
                class="withdraw-input">
        </div>
        <!-- BANK -->
        <div id="bankFields" style="display:none">
            <input
                type="text"
                name="network_bank"
                placeholder="Bank"
                class="withdraw-input">
            <input
                type="text"
                name="account_name"
                placeholder="Account Name"
                class="withdraw-input">
            <input
                type="text"
                name="account_number"
                placeholder="Account Number"
                class="withdraw-input">
        </div>
        <!-- MOMO -->
        <div id="momoFields" style="display:none">
            <input
                type="text"
                name="network_bank"
                placeholder="Network"
                class="withdraw-input">
            <input
                type="text"
                name="account_name"
                placeholder="MOMO Name"
                class="withdraw-input">
            <input
                type="text"
                name="account_number"
                placeholder="MOMO Number"
                class="withdraw-input">
        </div>

        <input
            type="password"
            name="password"
            placeholder="Enter your password"
            required
            class="withdraw-input">

        <div class="withdraw-summary">
            <div>
                Fees
                <span id="fee">0</span>
            </div>
            <div>
                Payout
                <span id="received">0</span>
            </div>
        </div>
        <button class="withdraw-btn">
            Confirm
        </button>
    </form>

    <?php if(!empty($msg)): ?>
    <div class="withdraw-error">
        <?php echo htmlspecialchars($msg); ?>
    </div>
    <?php endif; ?>
</div>

<script>
function goBack(){
    if(document.referrer){
        window.history.back();
    }else{
        window.location.href="index.php";
    }
}
const radios=document.querySelectorAll("input[name='method']");
const amountInput=document.getElementById("amountInput");
let rate=1;
let fee=0;
let currency="USD";
const usdBalance=<?php echo $balance; ?>;
const cryptoFields=document.getElementById("cryptoFields");
const bankFields=document.getElementById("bankFields");
const momoFields=document.getElementById("momoFields");

radios.forEach(radio=>{
    radio.addEventListener("change",function(){
        rate=parseFloat(this.dataset.rate);
        fee=parseFloat(this.dataset.fee);
        currency=this.dataset.currency;
        let type=this.dataset.type;
        cryptoFields.style.display="none";
        bankFields.style.display="none";
        momoFields.style.display="none";
        if(type==="crypto") cryptoFields.style.display="block";
        if(type==="bank") bankFields.style.display="block";
        if(type==="momo") momoFields.style.display="block";
        /* convert balance */
        let convertedBalance=usdBalance*rate;
        document.getElementById("convertedBalance").innerText=
            convertedBalance.toFixed(2)+" "+currency;
        calculate();
    });
});

amountInput.addEventListener("input",calculate);

function calculate(){
    let amountLocal=parseFloat(amountInput.value)||0;
    let amountUSD=amountLocal/rate;
    document.getElementById("usdEquivalent").innerText=
        "≈ "+amountUSD.toFixed(2)+" USD";
    let received=amountLocal-fee;
    document.getElementById("fee").innerText=fee+" "+currency;
    document.getElementById("received").innerText=received.toFixed(2)+" "+currency;
}
</script>
<?php include "inc/footer.php"; ?>
