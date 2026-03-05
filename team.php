<?php
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
$ref_link = "https://".$_SERVER['HTTP_HOST']."/register.php?ref=".$ref_code;


/* TEAM STATS */

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
<strong>0</strong>
</div>

<div>
<span>First withdrawal</span>
<strong>0</strong>
</div>

</div>



<!-- LEVEL 1 -->

<div class="team-level level1">

<div class="level-left">
<img src="assets/images/medal.png">
<span>LEVEL 1</span>
</div>

<div class="level-center">

<div>
Register/Valid
<strong>0/0</strong>
</div>

<div>
Total Income
<strong>0</strong>
</div>

</div>

<div class="level-right">

<div>
Commission Percentage
<strong>16%</strong>
</div>

<a href="team/1.php" class="detail-btn">Details</a>

</div>

</div>



<!-- LEVEL 2 -->

<div class="team-level level2">

<div class="level-left">
<img src="assets/images/medal.png">
<span>LEVEL 2</span>
</div>

<div class="level-center">

<div>
Register/Valid
<strong>0/0</strong>
</div>

<div>
Total Income
<strong>0</strong>
</div>

</div>

<div class="level-right">

<div>
Commission Percentage
<strong>3%</strong>
</div>

<a href="team/2.php" class="detail-btn">Details</a>

</div>

</div>



<!-- LEVEL 3 -->

<div class="team-level level3">

<div class="level-left">
<img src="assets/images/medal.png">
<span>LEVEL 3</span>
</div>

<div class="level-center">

<div>
Register/Valid
<strong>0/0</strong>
</div>

<div>
Total Income
<strong>0</strong>
</div>

</div>

<div class="level-right">

<div>
Commission Percentage
<strong>1%</strong>
</div>

<a href="team/3.php" class="detail-btn">Details</a>

</div>

</div>



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
