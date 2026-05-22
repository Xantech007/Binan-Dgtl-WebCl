<?php

error_reporting(E_ALL);
ini_set('display_errors',1);

session_start();

if(!isset($_SESSION['user_id'])){
header("Location: login.php");
exit;
}

require_once "config/database.php";

$user_id = $_SESSION['user_id'];

/* Get user referral code */
$stmt = $pdo->prepare("SELECT referral_code FROM users WHERE id=?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$ref_code = $user['referral_code'];

/* Referral link */
$ref_link = "https://".$_SERVER['HTTP_HOST']."/register.php?invite=".$ref_code;


/* ================= TEAM STATS ================= */

/* team size */
$stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE referred_by=?");
$stmt->execute([$ref_code]);
$team_size = $stmt->fetchColumn();

/* team recharge */
$stmt = $pdo->prepare("SELECT SUM(amount) FROM deposits 
JOIN users ON deposits.user_id=users.id
WHERE users.referred_by=? AND deposits.status=1");
$stmt->execute([$ref_code]);
$team_recharge = $stmt->fetchColumn() ?? 0;

/* team withdraw */
$stmt = $pdo->prepare("SELECT SUM(amount) FROM withdrawals
JOIN users ON withdrawals.user_id=users.id
WHERE users.referred_by=? AND withdrawals.status=1");
$stmt->execute([$ref_code]);
$team_withdraw = $stmt->fetchColumn() ?? 0;


/* ================= LEVEL 1 ================= */

$stmt = $pdo->prepare("SELECT id,referral_code FROM users WHERE referred_by=?");
$stmt->execute([$ref_code]);
$level1 = $stmt->fetchAll(PDO::FETCH_ASSOC);

$l1_register = count($level1);

$l1_ids = array_column($level1,'id');
$l1_codes = array_column($level1,'referral_code');

$l1_valid = 0;

if($l1_ids){
$in = implode(',',array_fill(0,count($l1_ids),'?'));

$stmt = $pdo->prepare("SELECT COUNT(DISTINCT user_id) FROM deposits WHERE status=1 AND user_id IN ($in)");
$stmt->execute($l1_ids);
$l1_valid = $stmt->fetchColumn();
}


/* ================= LEVEL 2 ================= */

$l2_register = 0;
$l2_valid = 0;

if($l1_codes){

$in = implode(',',array_fill(0,count($l1_codes),'?'));

$stmt = $pdo->prepare("SELECT id,referral_code FROM users WHERE referred_by IN ($in)");
$stmt->execute($l1_codes);

$level2 = $stmt->fetchAll(PDO::FETCH_ASSOC);

$l2_register = count($level2);

$l2_ids = array_column($level2,'id');
$l2_codes = array_column($level2,'referral_code');

if($l2_ids){

$in = implode(',',array_fill(0,count($l2_ids),'?'));

$stmt = $pdo->prepare("SELECT COUNT(DISTINCT user_id) FROM deposits WHERE status=1 AND user_id IN ($in)");
$stmt->execute($l2_ids);
$l2_valid = $stmt->fetchColumn();

}

}else{
$l2_codes=[];
}


/* ================= LEVEL 3 ================= */

$l3_register = 0;
$l3_valid = 0;

if(!empty($l2_codes)){

$in = implode(',',array_fill(0,count($l2_codes),'?'));

$stmt = $pdo->prepare("SELECT id FROM users WHERE referred_by IN ($in)");
$stmt->execute($l2_codes);

$level3 = $stmt->fetchAll(PDO::FETCH_ASSOC);

$l3_register = count($level3);

$l3_ids = array_column($level3,'id');

if($l3_ids){

$in = implode(',',array_fill(0,count($l3_ids),'?'));

$stmt = $pdo->prepare("SELECT COUNT(DISTINCT user_id) FROM deposits WHERE status=1 AND user_id IN ($in)");
$stmt->execute($l3_ids);
$l3_valid = $stmt->fetchColumn();

}

}


/* ================= EXTRA STATS ================= */

/* first time recharge */

$stmt = $pdo->prepare("
SELECT COUNT(DISTINCT users.id)
FROM users
JOIN deposits ON users.id = deposits.user_id
WHERE users.referred_by=? AND deposits.status=1
");
$stmt->execute([$ref_code]);
$first_recharge = $stmt->fetchColumn();


/* first withdrawal */

$stmt = $pdo->prepare("
SELECT COUNT(DISTINCT users.id)
FROM users
JOIN withdrawals ON users.id = withdrawals.user_id
WHERE users.referred_by=? AND withdrawals.status=1
");
$stmt->execute([$ref_code]);
$first_withdraw = $stmt->fetchColumn();

?>

<?php include "inc/header.php"; ?>


<div class="team-container">


<!-- REFERRAL BOX -->

<div class="ref-box">

<div class="ref-code">

<span>Invitation code:</span>

<strong><?php echo $ref_code; ?></strong>

<button onclick="copyCode()">Copy</button>

</div>

<div class="ref-link">

<p>Share your referral link and start earning</p>

<input type="text" value="<?php echo $ref_link; ?>" id="refLink" readonly>

<button onclick="copyLink()">Copy</button>

</div>


<!-- SOCIAL ICONS -->

<div class="social-icons">

<i class="fa-brands fa-x-twitter"></i>
<i class="fa-brands fa-facebook-f"></i>
<i class="fa-brands fa-telegram"></i>
<i class="fa-brands fa-linkedin-in"></i>
<i class="fa-brands fa-whatsapp"></i>
<i class="fa-brands fa-instagram"></i>
<i class="fa-brands fa-tiktok"></i>
<i class="fa-solid fa-share-nodes"></i>

</div>

</div>



<!-- TEAM STATS -->

<div class="team-stats">

<div>
<span>Team size</span>
<strong><?php echo $team_size; ?></strong>
</div>

<div>
<span>Team recharge</span>
<strong>$<?php echo number_format($team_recharge,2); ?></strong>
</div>

<div>
<span>Team Withdrawal</span>
<strong>$<?php echo number_format($team_withdraw,2); ?></strong>
</div>

<div>
<span>New team</span>
<strong><?php echo $team_size; ?></strong>
</div>

<div>
<span>First time recharge</span>
<strong><?php echo $first_recharge; ?></strong>
</div>

<div>
<span>First withdrawal</span>
<strong><?php echo $first_withdraw; ?></strong>
</div>

</div>



<!-- LEVEL 1 -->

<div class="team-level level1">

<div class="level-badge">
<img src="assets/images/medal.png">
<span>LEVEL 1</span>
</div>

<div class="level-panel">

<div class="level-stats">

<div>
<p>Register/Valid</p>
<strong><?php echo $l1_register.'/'.$l1_valid; ?></strong>
</div>

<div>
<p>Total Income</p>
<strong>0</strong>
</div>

</div>

<div class="level-commission">
<p>Commission Percentage</p>
<strong>16%</strong>
</div>

</div>

<a href="team/1.php" class="detail-btn">Details</a>

</div>



<!-- LEVEL 2 -->

<div class="team-level level2">

<div class="level-badge">
<img src="assets/images/medal.png">
<span>LEVEL 2</span>
</div>

<div class="level-panel">

<div class="level-stats">

<div>
<p>Register/Valid</p>
<strong><?php echo $l2_register.'/'.$l2_valid; ?></strong>
</div>

<div>
<p>Total Income</p>
<strong>0</strong>
</div>

</div>

<div class="level-commission">
<p>Commission Percentage</p>
<strong>3%</strong>
</div>

</div>

<a href="team/2.php" class="detail-btn">Details</a>

</div>



<!-- LEVEL 3 -->

<div class="team-level level3">

<div class="level-badge">
<img src="assets/images/medal.png">
<span>LEVEL 3</span>
</div>

<div class="level-panel">

<div class="level-stats">

<div>
<p>Register/Valid</p>
<strong><?php echo $l3_register.'/'.$l3_valid; ?></strong>
</div>

<div>
<p>Total Income</p>
<strong>0</strong>
</div>

</div>

<div class="level-commission">
<p>Commission Percentage</p>
<strong>1%</strong>
</div>

</div>

<a href="team/3.php" class="detail-btn">Details</a>

</div>


<script>

/* COPY FUNCTIONS */

function copyCode(){

navigator.clipboard.writeText("<?php echo $ref_code;?>");

alert("Code copied");

}

function copyLink(){

var link=document.getElementById("refLink");

navigator.clipboard.writeText(link.value);

alert("Link copied");

}

/* SOCIAL ICON ANIMATION */

window.addEventListener("load",function(){

document.querySelectorAll(".social-icons i")
.forEach((icon,index)=>{

setTimeout(()=>{
icon.classList.add("show");
},index*120);

});

});

</script>


<?php include "inc/footer.php"; ?>
