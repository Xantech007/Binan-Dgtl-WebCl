<?php
session_start();
require_once "config/database.php";

if(!isset($_SESSION['user_id'])){
header("Location: login.php");
exit;
}

$user_id = $_SESSION['user_id'];


/* GET DEPOSITS */

$stmt=$pdo->prepare("
SELECT
d.amount,
d.paid_amount,
d.paid_currency,
d.status,
d.created_at,
pm.name AS method
FROM deposits d
LEFT JOIN payment_methods pm ON d.method_id = pm.id
WHERE d.user_id=?
ORDER BY d.id DESC
");

$stmt->execute([$user_id]);
$deposits=$stmt->fetchAll(PDO::FETCH_ASSOC);


/* GET WITHDRAWALS */

$stmt=$pdo->prepare("
SELECT
amount,
received,
currency,
method,
status,
created_at
FROM withdrawals
WHERE user_id=?
ORDER BY id DESC
");

$stmt->execute([$user_id]);
$withdrawals=$stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<?php include "inc/header.php"; ?>


<div class="financial-header">

<a onclick="goBack()">
<i class="fa fa-arrow-left"></i>
</a>

<span>Financial records</span>

</div>


<div class="financial-container">

<div class="financial-tabs">

<div class="tab active" onclick="switchTab('basic')">
Deposits
</div>

<div class="tab" onclick="switchTab('withdraw')">
Withdrawals
</div>

</div>


<!-- Deposits -->

<div class="financial-content active" id="basic">

<?php if(count($deposits) == 0): ?>

<div class="financial-empty">

<img src="assets/images/nodata.png">

<p>No data</p>

</div>

<?php else: ?>

<?php foreach($deposits as $row): ?>

<div class="financial-item">

<div>

Deposit

<?php if(!empty($row['method'])): ?>
<br><small><?php echo htmlspecialchars($row['method']); ?></small>
<?php endif; ?>

<br><small>
<?php echo date("Y-m-d H:i",strtotime($row['created_at'])); ?>
</small>

<br>

<small>

<?php
if($row['status']==0){
echo "Pending";
}elseif($row['status']==1){
echo "Approved";
}else{
echo "Rejected";
}
?>

</small>

</div>

<div class="amount">

+<?php echo number_format($row['amount'],2); ?> USD

<?php if(!empty($row['paid_amount'])): ?>
<br>
<small>
Paid: <?php echo number_format($row['paid_amount'],2)." ".htmlspecialchars($row['paid_currency']); ?>
</small>
<?php endif; ?>

</div>

</div>

<?php endforeach; ?>

<?php endif; ?>

</div>



<!-- WITHDRAWALS -->

<div class="financial-content" id="withdraw">

<?php if(count($withdrawals) == 0): ?>

<div class="financial-empty">

<img src="assets/images/nodata.png">

<p>No data</p>

</div>

<?php else: ?>

<?php foreach($withdrawals as $row): ?>

<div class="financial-item">

<div>

Withdraw

<br><small>
<?php echo htmlspecialchars($row['method']); ?>
</small>

<br><small>
<?php echo date("Y-m-d H:i",strtotime($row['created_at'])); ?>
</small>

<br>

<small>

<?php
if($row['status']==0){
echo "Pending";
}elseif($row['status']==1){
echo "Approved";
}else{
echo "Rejected";
}
?>

</small>

</div>

<div class="amount minus">

-<?php echo number_format($row['amount'],2); ?> USD

<br>

<small>
Received: <?php echo number_format($row['received'],2).' '.htmlspecialchars($row['currency'] ?? 'USD'); ?>
</small>

</div>

</div>

<?php endforeach; ?>

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


/* TAB SWITCH */

function switchTab(tab){

document.querySelectorAll(".tab").forEach(t=>t.classList.remove("active"));
event.target.classList.add("active");

document.querySelectorAll(".financial-content").forEach(c=>c.classList.remove("active"));

document.getElementById(tab).classList.add("active");

}

</script>
