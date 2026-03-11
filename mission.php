<?php
session_start();
require_once "config/database.php";

if(!isset($_SESSION['user_id'])){
header("Location: login.php");
exit;
}

$user_id = $_SESSION['user_id'];


/* AUTO EXPIRE VIP */

$pdo->prepare("
UPDATE user_vip
SET status=0
WHERE end_time <= NOW()
")->execute();


/* CLAIM VIP PROFIT */

if(isset($_POST['claim_vip'])){

$user_vip_id=$_POST['user_vip_id'];

$stmt=$pdo->prepare("
SELECT uv.*,v.daily_profit,v.activation_fee
FROM user_vip uv
JOIN vip v ON uv.vip_id=v.id
WHERE uv.id=? AND uv.user_id=? AND uv.status=1
");

$stmt->execute([$user_vip_id,$user_id]);
$vip=$stmt->fetch(PDO::FETCH_ASSOC);

if($vip){

$start=strtotime($vip['start_time']);
$now=time();

$days_passed=floor(($now-$start)/86400);

$claimable_days=$days_passed-$vip['claimed_days'];

if($claimable_days>0){

$profit=$claimable_days*$vip['daily_profit'];

/* ADD BALANCE */

$pdo->prepare("
UPDATE users
SET balance=balance+?
WHERE id=?
")->execute([$profit,$user_id]);

/* UPDATE CLAIMED DAYS */

$new_claimed=$vip['claimed_days']+$claimable_days;

$pdo->prepare("
UPDATE user_vip
SET claimed_days=?
WHERE id=?
")->execute([$new_claimed,$user_vip_id]);

/* SAVE CLAIM */

$pdo->prepare("
INSERT INTO vip_claims
(user_id,vip_id,user_vip_id,days_claimed,profit)
VALUES (?,?,?,?,?)
")->execute([
$user_id,
$vip['vip_id'],
$user_vip_id,
$claimable_days,
$profit
]);

}

}

header("Location: mission.php");
exit;

}


/* FETCH RESET TIME */

$stmt = $pdo->query("SELECT reset_time FROM task_reset LIMIT 1");
$reset = $stmt->fetch(PDO::FETCH_ASSOC);

$reset_time = strtotime($reset['reset_time']);


/* GET RUNNING VIP */

$stmt=$pdo->prepare("
SELECT uv.*,v.daily_profit,v.activation_fee
FROM user_vip uv
JOIN vip v ON uv.vip_id=v.id
WHERE uv.user_id=? AND uv.status=1
");

$stmt->execute([$user_id]);
$running_vips=$stmt->fetchAll(PDO::FETCH_ASSOC);


/* CLAIM HISTORY */

$stmt=$pdo->prepare("
SELECT vc.*,v.activation_fee
FROM vip_claims vc
JOIN vip v ON vc.vip_id=v.id
WHERE vc.user_id=?
ORDER BY vc.id DESC
");

$stmt->execute([$user_id]);
$completed=$stmt->fetchAll(PDO::FETCH_ASSOC);


/* TASK COUNTS */

$today_tasks=count($completed);
$remaining_tasks=count($running_vips);

?>

<?php include "inc/header.php"; ?>


<div class="task-reset-box">

<i class="fa-solid fa-clock"></i>

<span>Task reset</span>

<strong id="countdown"></strong>

</div>


<div class="task-stats">

<div>
<p>All tasks for today</p>
<strong><?php echo $today_tasks; ?></strong>
</div>

<div>
<p>Today's remaining tasks</p>
<strong><?php echo $remaining_tasks; ?></strong>
</div>

</div>


<div class="task-panel">

<div class="task-tabs">

<span class="tab active" onclick="switchTab('progress')">
In progress
</span>

<span class="tab" onclick="switchTab('completed')">
Completed
</span>

</div>


<!-- IN PROGRESS -->

<div id="progress" class="task-content active">

<?php if(!$running_vips): ?>

<div class="no-data">
<i class="fa-solid fa-cloud"></i>
<p>No data</p>
</div>

<?php else: ?>

<?php foreach($running_vips as $vip):

$start=strtotime($vip['start_time']);
$now=time();

$days=floor(($now-$start)/86400);

$claimable=$days-$vip['claimed_days'];

$profit=$claimable*$vip['daily_profit'];

?>

<div class="vip-task-card">

<img src="assets/images/logo-vip.png">

<div class="vip-task-info">

<h3>VIP<?php echo $vip['vip_id']; ?></h3>

<p>Price</p>
<strong><?php echo number_format($vip['activation_fee'],2); ?></strong>

<p>Income</p>
<strong><?php echo number_format($profit,2); ?></strong>

</div>

<?php if($claimable>0): ?>

<form method="POST">

<input type="hidden" name="user_vip_id" value="<?php echo $vip['id']; ?>">

<button name="claim_vip" class="complete-btn">
To complete
</button>

</form>

<?php else: ?>

<button class="complete-btn disabled">
Waiting
</button>

<?php endif; ?>

</div>

<?php endforeach; ?>

<?php endif; ?>

</div>


<!-- COMPLETED -->

<div id="completed" class="task-content">

<?php if(!$completed): ?>

<div class="no-data">
<i class="fa-solid fa-building"></i>
<p>No data</p>
</div>

<?php else: ?>

<?php foreach($completed as $c): ?>

<div class="vip-task-card">

<img src="assets/images/logo-vip.png">

<div class="vip-task-info">

<h3>VIP<?php echo $c['vip_id']; ?></h3>

<p>Price</p>
<strong><?php echo number_format($c['activation_fee'],2); ?></strong>

<p>Income</p>
<strong><?php echo number_format($c['profit'],2); ?></strong>

<p>Complete time</p>
<strong>
<?php echo date("d/m/Y H:i:s",strtotime($c['claimed_at'])); ?>
</strong>

</div>

</div>

<?php endforeach; ?>

<?php endif; ?>

</div>

</div>


<?php include "inc/footer.php"; ?>


<script>

var resetTime = <?php echo $reset_time * 1000; ?>;

function updateCountdown(){

var now = new Date().getTime();
var distance = resetTime - now;

if(distance < 0){
location.reload();
return;
}

var hours = Math.floor((distance % (1000*60*60*24)) / (1000*60*60));
var minutes = Math.floor((distance % (1000*60*60)) / (1000*60));
var seconds = Math.floor((distance % (1000*60)) / 1000);

document.getElementById("countdown").innerHTML =
hours.toString().padStart(2,'0') + ":" +
minutes.toString().padStart(2,'0') + ":" +
seconds.toString().padStart(2,'0');

}

setInterval(updateCountdown,1000);


function switchTab(tab){

document.querySelectorAll(".task-content")
.forEach(el=>el.classList.remove("active"));

document.getElementById(tab).classList.add("active");

document.querySelectorAll(".tab")
.forEach(el=>el.classList.remove("active"));

event.target.classList.add("active");

}

</script>
