<?php
session_start();

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

require_once "config/database.php";

/* Fetch logged in user */
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT email, vip_level, balance FROM users WHERE id=?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$user_email = $user['email'] ?? "Unknown";
$user_vip = "VIP".intval($user['vip_level'] ?? 0);
$user_balance = $user['balance'] ?? 0;

/* Fetch news */
$query = $pdo->query("SELECT title FROM news ORDER BY id DESC");

/* Fetch VIP plans */
$vipQuery = $pdo->query("SELECT * FROM vip WHERE status = 1 ORDER BY id ASC");

/* FETCH RESET TIME */

$stmt = $pdo->query("SELECT reset_time FROM task_reset LIMIT 1");
$reset = $stmt->fetch(PDO::FETCH_ASSOC);

$reset_time = strtotime($reset['reset_time']);
$now = time();

/* IF RESET TIME EXPIRED → ADD 12 HOURS */

if($reset_time <= $now){

$new_reset = date("Y-m-d H:i:s", strtotime("+12 hours"));

$pdo->prepare("
UPDATE task_reset
SET reset_time=?
")->execute([$new_reset]);

$reset_time = strtotime($new_reset);

}

<?php include "inc/header.php"; ?>


<?php if(isset($_SESSION['recharge_msg'])): ?>

<div class="recharge-success">
<?php 
echo $_SESSION['recharge_msg']; 
unset($_SESSION['recharge_msg']);
?>
</div>

<?php endif; ?>


<?php if(isset($_SESSION['withdraw_msg'])): ?>

<div class="withdraw-success">
<?php 
echo $_SESSION['withdraw_msg'];
unset($_SESSION['withdraw_msg']);
?>
</div>

<?php endif; ?>

<!-- ================= NEWS SCROLL ================= -->

<div class="news-wrapper">

    <div class="news-icon">
        <i class="fa-solid fa-bell"></i>
    </div>

    <div class="news-marquee">
        <div class="news-content">

            <?php
            while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                echo "<span class='news-item'>" . htmlspecialchars($row['title']) . "</span>";
            }
            ?>

        </div>
    </div>

</div>



<!-- DASHBOARD ACTION SECTION -->

<div class="dashboard-container">

    <div class="dashboard-top">
        <div class="user-info">
            <span class="user-email"><?php echo htmlspecialchars($user_email); ?></span>
            <span class="vip-badge"><?php echo $user_vip; ?></span>
        </div>

        <a href="balance.php" class="wallet-btn">
            <i class="fa-solid fa-wallet"></i>
        </a>
    </div>

    <div class="balance-box">
        <span>Balance</span>
        <strong>$<?php echo number_format($user_balance,2); ?></strong>
    </div>

    <div class="dashboard-actions">

        <div class="action-item">
            <a href="recharge.php">
                <div class="icon-circle">
                    <i class="fa-solid fa-money-bill-wave"></i>
                </div>
            </a>
            <span>Recharge</span>
        </div>

        <div class="action-item">
            <a href="withdraw.php">
                <div class="icon-circle">
                    <i class="fa-solid fa-arrow-up-from-bracket"></i>
                </div>
            </a>
            <span>Withdraw</span>
        </div>

        <div class="action-item">
            <a href="app.php">
                <div class="icon-circle">
                    <i class="fa-solid fa-mobile-screen"></i>
                </div>
            </a>
            <span>App</span>
        </div>

        <div class="action-item">
            <a href="company.php">
                <div class="icon-circle">
                    <i class="fa-solid fa-building"></i>
                </div>
            </a>
            <span>Company Profile</span>
        </div>

    </div>

</div>


<!-- BANNER -->

<div class="banner-slider">
    <div class="banner-track">
        <img src="assets/images/banner1.jpeg">
        <img src="assets/images/banner2.jpeg">
    </div>
</div>


<!-- TASK RESET COUNTDOWN -->

<div class="task-reset-container">

<div class="reset-time" id="taskCountdown">
00:00:00
</div>

<div class="reset-label">
Task Reset Countdown
</div>

</div>


<!-- TASK HALL -->

<div class="task-section">
<h2 class="task-title">Task Hall</h2>

<?php while($vip = $vipQuery->fetch(PDO::FETCH_ASSOC)): ?>

<a href="activate_vip.php?id=<?php echo $vip['id']; ?>" class="task-card">

<div class="task-left">

<img src="assets/images/vip.jpg" class="vip-icon">

<?php if($user['vip_level'] < $vip['id']): ?>
<i class="fa-solid fa-lock lock-overlay"></i>
<?php endif; ?>

</div>

<div class="task-content">

<div class="unlock-text">
Unlock amount <span>$<?php echo number_format($vip['activation_fee'],2); ?></span>
</div>

<div class="vip-name">
<?php echo htmlspecialchars($vip['name']); ?>
</div>

</div>

<div class="task-arrow">
<i class="fa-solid fa-angle-right"></i>
</div>

</a>

<?php endwhile; ?>

</div>


<!-- MEMBER LIST -->

<div class="member-section">
<h2 class="member-title">Member list</h2>

<div class="member-wrapper">
<div class="member-track">

<?php
$members = [
    ["VIP6", "hillls@yahoo.com"],
    ["VIP4", "char*****24@outlook.com"],
    ["VIP8", "rjoh********93@hotmail.com"],
    ["VIP2", "lind***********02@gmail.com"],
    ["VIP5", "lewisa@outlook.com"],
    ["VIP1", "will*******24@yahoo.com"],
    ["VIP8", "jenn********or@yahoo.com"],
    ["VIP3", "just******er@hotmail.com"],
    ["VIP2", "sara********ng@yahoo.com"],
    ["VIP3", "grac***du@gmail.com"],
    ["VIP5", "yqua*****96@hotmail.com"],
    ["VIP5", "moor***57@gmail.com"],
    ["VIP2", "mich**********ez@hotmail.com"],
    ["VIP6", "aess*******51@gmail.com"],
    ["VIP8", "comf***********86@outlook.com"],
    ["VIP3", "rose***na@yahoo.com"],
    ["VIP3", "kevi*71@hotmail.com"],
    ["VIP3", "mega**co@gmail.com"],
    ["VIP2", "vict*******66@outlook.com"],
    ["VIP1", "bran****ho@hotmail.com"],
    ["VIP6", "aman********on@gmail.com"],
    ["VIP1", "eliz*********70@outlook.com"],
    ["VIP1", "rebe**********26@hotmail.com"],
    ["VIP6", "whitej@hotmail.com"],
    ["VIP10", "dian*******ng@yahoo.com"],
    ["VIP6", "ande***nt@gmail.com"],
    ["VIP7", "jose********ts@yahoo.com"],
    ["VIP1", "hasi*******41@yahoo.com"],
    ["VIP5", "bran****ou@gmail.com"],
    ["VIP5", "rose*****62@outlook.com"],
    ["VIP8", "emil******er@gmail.com"],
    ["VIP7", "joyc**ar@hotmail.com"],
    ["VIP4", "geor******es@hotmail.com"],
    ["VIP2", "efua**du@gmail.com"],
    ["VIP7", "alex*********ll@yahoo.com"],
    ["VIP2", "stev**********ez@outlook.com"],
    ["VIP1", "fait*88@yahoo.com"],
    ["VIP6", "rona******is@outlook.com"],
    ["VIP2", "isab****30@gmail.com"],
    ["VIP3", "rebe*******su@yahoo.com"],
    ["VIP2", "thom******rk@yahoo.com"],
    ["VIP4", "pris***********31@hotmail.com"],
    ["VIP6", "rebe****po@hotmail.com"],
    ["VIP10", "vkoo*****35@hotmail.com"],
    ["VIP5", "laur***ew@hotmail.com"],
    ["VIP7", "jhil**32@hotmail.com"],
    ["VIP9", "jgar****76@outlook.com"],
    ["VIP8", "anel*******33@outlook.com"],
    ["VIP8", "davi******in@yahoo.com"],
    ["VIP9", "sama*******05@hotmail.com"],
    ["VIP8", "vero***a8@gmail.com"],
    ["VIP4", "char*****ee@hotmail.com"],
    ["VIP9", "merc*80@gmail.com"],
    ["VIP6", "geor*************23@hotmail.com"],
    ["VIP1", "will*****11@yahoo.com"],
    ["VIP4", "abig***97@yahoo.com"],
    ["VIP6", "ava335@hotmail.com"],
    ["VIP1", "abig***59@gmail.com"],
    ["VIP9", "kowu**89@gmail.com"],
    ["VIP3", "euni***********05@hotmail.com"],
    ["VIP4", "clarkc@outlook.com"],
    ["VIP7", "thom****44@yahoo.com"],
    ["VIP8", "geor**73@hotmail.com"],
    ["VIP4", "jose***ee@hotmail.com"],
    ["VIP10", "nico*******es@yahoo.com"],
    ["VIP2", "mary****05@gmail.com"],
    ["VIP10", "edwa****ee@gmail.com"],
    ["VIP10", "bran*******67@outlook.com"],
    ["VIP8", "ford***ry@hotmail.com"],
    ["VIP2", "aamo******49@hotmail.com"],
    ["VIP9", "step*************91@gmail.com"],
    ["VIP3", "grac*********95@gmail.com"],
    ["VIP7", "eriv****66@gmail.com"],
    ["VIP7", "isab**********15@yahoo.com"],
    ["VIP6", "geor***re@outlook.com"],
    ["VIP9", "stel********ur@gmail.com"],
    ["VIP6", "emma****en@yahoo.com"],
    ["VIP2", "jenn***********88@gmail.com"],
    ["VIP6", "ther*******ei@outlook.com"],
    ["VIP1", "paul*******on@outlook.com"],
    ["VIP2", "ella76@hotmail.com"],
    ["VIP5", "step************86@yahoo.com"],
    ["VIP5", "evel*********ez@yahoo.com"],
    ["VIP8", "tayl***********61@yahoo.com"],
    ["VIP5", "rebe***83@hotmail.com"],
    ["VIP8", "ama.*****ur@hotmail.com"],
    ["VIP3", "pris***********su@gmail.com"],
    ["VIP4", "ella*******48@outlook.com"],
    ["VIP2", "garc*am@yahoo.com"],
    ["VIP5", "fait****17@gmail.com"],
    ["VIP2", "aman***co@yahoo.com"],
    ["VIP5", "laur******01@hotmail.com"],
    ["VIP7", "oliv*********ez@hotmail.com"],
    ["VIP10", "merc*91@outlook.com"],
    ["VIP1", "brid**********22@outlook.com"],
    ["VIP8", "andr*****ll@gmail.com"],
    ["VIP6", "rona*********43@yahoo.com"],
    ["VIP10", "jenn**************32@gmail.com"],
    ["VIP10", "appi****57@hotmail.com"],
    ["VIP7", "gonz***ze@outlook.com"]
];
shuffle($members);
?>

<!-- Your container divs -->
<div class="members-container">
<?php foreach($members as $member): 
    $earning = rand(50, 1500);
?>
    <div class="member-row">
        <div class="member-card">
            <div class="vip-level"><?php echo $member[0]; ?></div>
            <div class="earning">+$<?php echo number_format($earning, 2); ?></div>
            <div class="email"><?php echo $member[1]; ?></div>
        </div>
    </div>
<?php endforeach; ?>
</div>
</div>
</div>


<!-- REGULATORY AUTHORITY -->

<div class="reg-section">
<h2 class="reg-title">Regulatory Authority</h2>

<div class="reg-container">
<img src="assets/images/reg1.webp">
<img src="assets/images/reg2.webp">
</div>

</div>


<script>

/* TASK RESET COUNTDOWN */

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

document.getElementById("taskCountdown").innerHTML =
hours.toString().padStart(2,'0') + ":" +
minutes.toString().padStart(2,'0') + ":" +
seconds.toString().padStart(2,'0');

}

setInterval(updateCountdown,1000);

</script>


<?php include "inc/footer.php"; ?>
