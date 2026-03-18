<?php
session_start();
require_once "config/database.php";

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Security: must have both parameters and user_id must match session
if(!isset($_GET['withdrawal_id']) || !isset($_GET['user_id']) || (int)$_GET['user_id'] !== $user_id){
    header("Location: index.php");
    exit;
}

$withdrawal_id = (int)$_GET['withdrawal_id'];

// Fetch the exact withdrawal (only for this user)
$stmt = $pdo->prepare("
    SELECT * FROM withdrawals 
    WHERE id = ? AND user_id = ?
");
$stmt->execute([$withdrawal_id, $user_id]);
$withdrawal = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$withdrawal){
    $_SESSION['error_msg'] = "Receipt not found or access denied.";
    header("Location: index.php");
    exit;
}

// Get user email for display
$stmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$email = $user['email'] ?? 'N/A';
?>
<?php include "inc/header.php"; ?>

<div style="max-width: 700px; margin: 40px auto; padding: 30px; border: 2px solid #000; border-radius: 10px; background: #fff; font-family: Arial, sans-serif; box-shadow: 0 0 15px rgba(0,0,0,0.2);">
    <div style="text-align: center; margin-bottom: 30px;">
        <h1 style="margin:0; color:#28a745;">✅ Withdrawal Receipt</h1>
        <p style="color:#555; font-size:18px;">Your withdrawal has been submitted successfully!</p>
    </div>

    <div style="border-bottom: 2px dashed #ccc; padding-bottom: 15px; margin-bottom: 20px;">
        <table width="100%" cellpadding="8" style="border-collapse: collapse;">
            <tr>
                <td style="font-weight:bold; width:40%;">Transaction ID</td>
                <td>#30654<?php echo $withdrawal['id']; ?></td>
            </tr>
            <tr>
                <td style="font-weight:bold;">Date & Time</td>
                <td><?php echo date('d M Y H:i:s', strtotime($withdrawal['created_at'])); ?></td>
            </tr>
            <tr>
                <td style="font-weight:bold;">Customer Email</td>
                <td><?php echo htmlspecialchars($email); ?></td>
            </tr>
            <tr>
                <td style="font-weight:bold;">Withdrawal Method</td>
                <td><?php echo htmlspecialchars($withdrawal['method']); ?></td>
            </tr>
            <tr>
                <td style="font-weight:bold;">Amount Deducted</td>
                <td><strong><?php echo number_format($withdrawal['amount'], 2); ?> USD</strong></td>
            </tr>
            <tr>
                <td style="font-weight:bold;">Fee</td>
                <td><?php echo number_format($withdrawal['fee'], 2); ?> <?php echo htmlspecialchars($withdrawal['currency'] ?? 'USD'); ?></td>
            </tr>
            <tr>
                <td style="font-weight:bold;">Payout Amount</td>
                <td><strong><?php echo number_format($withdrawal['received'], 2); ?> <?php echo htmlspecialchars($withdrawal['currency'] ?? 'USD'); ?></strong></td>
            </tr>
            <tr>
                <td style="font-weight:bold;">Status</td>
                <td>
                    <?php if($withdrawal['status'] == 1): ?>
                        <span style="color:#28a745; font-weight:bold;">✅ Approved / Completed</span>
                    <?php else: ?>
                        <span style="color:#ffc107; font-weight:bold;">⏳ Pending Approval</span>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
    </div>

    <!-- Extra details -->
    <?php if(!empty($withdrawal['address'])): ?>
    <p><strong>Withdrawal Address:</strong> <?php echo htmlspecialchars($withdrawal['address']); ?></p>
    <?php endif; ?>

    <?php if(!empty($withdrawal['network_bank'])): ?>
    <p><strong>Bank / Network:</strong> <?php echo htmlspecialchars($withdrawal['network_bank']); ?></p>
    <?php endif; ?>

    <?php if(!empty($withdrawal['account_name'])): ?>
    <p><strong>Account Name:</strong> <?php echo htmlspecialchars($withdrawal['account_name']); ?></p>
    <?php endif; ?>

    <?php if(!empty($withdrawal['account_number'])): ?>
    <p><strong>Account / MOMO Number:</strong> <?php echo htmlspecialchars($withdrawal['account_number']); ?></p>
    <?php endif; ?>

    <div style="margin-top:30px; text-align:center;">
        <button onclick="window.print()" 
                style="background:#28a745; color:white; border:none; padding:12px 30px; font-size:16px; border-radius:5px; cursor:pointer;">
            🖨️ Print Receipt
        </button>
        &nbsp;&nbsp;
        <a href="index.php" 
           style="background:#007bff; color:white; padding:12px 30px; text-decoration:none; border-radius:5px; display:inline-block;">
            Back to Dashboard
        </a>
    </div>

    <p style="text-align:center; margin-top:30px; color:#777; font-size:12px;">
        Thank you for using our platform.<br>
        This is an official receipt. Keep it for your records.
    </p>
</div>

<style>
@media print {
    body * { visibility: hidden; }
    .receipt-container, .receipt-container * { visibility: visible; }
    .receipt-container { position: absolute; left: 0; top: 0; width: 100%; }
    button, a { display: none !important; }
}
</style>

<?php include "inc/footer-exception.php"; ?>
