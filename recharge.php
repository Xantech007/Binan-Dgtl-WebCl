<?php
session_start();
require_once "config/database.php";

if(!isset($_SESSION['user_id'])){
header("Location: login.php");
exit;
}

$methods = $pdo->query("SELECT * FROM payment_methods WHERE status=1 ORDER BY id ASC");
?>

<?php include "inc/header.php"; ?>

<div class="recharge-header">
<a href="javascript:history.back()">
<i class="fa fa-arrow-left"></i>
</a>
<span>Select the recharge currency</span>
</div>

<div class="recharge-container">

<?php while($row = $methods->fetch(PDO::FETCH_ASSOC)): ?>

<a href="deposit.php?id=<?php echo $row['id']; ?>" class="recharge-item">

<div class="recharge-left">

<img src="<?php echo $row['image']; ?>" class="recharge-icon">

<span class="recharge-name">
<?php echo htmlspecialchars($row['name']); ?>
</span>

</div>

<div class="recharge-right">
<i class="fa-solid fa-angle-right"></i>
</div>

</a>

<?php endwhile; ?>

</div>

<?php include "inc/footer.php"; ?>
