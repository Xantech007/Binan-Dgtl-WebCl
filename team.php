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

/* =========================================================
   GET USER REFERRAL CODE
========================================================= */

$stmt = $pdo->prepare("
SELECT referral_code
FROM users
WHERE id=?
");

$stmt->execute([$user_id]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

$ref_code = $user['referral_code'];

/* =========================================================
   REFERRAL LINK
========================================================= */

$ref_link = "https://".$_SERVER['HTTP_HOST']."/register.php?invite=".$ref_code;

/* =========================================================
   TEAM STATS (LEVEL CAP = 3)
========================================================= */

$max_level = 3;

/* ---------------------------------------------------------
   GET ALL TEAM USERS UP TO LEVEL 3
--------------------------------------------------------- */

$current_codes = [$ref_code];
$all_team_ids = [];

for($i = 1; $i <= $max_level; $i++){

    if(empty($current_codes)) break;

    $in = implode(',', array_fill(0,count($current_codes),'?'));

    $stmt = $pdo->prepare("
        SELECT id, referral_code
        FROM users
        WHERE referred_by IN ($in)
    ");

    $stmt->execute($current_codes);

    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if(!$users) break;

    $ids = array_column($users,'id');
    $codes = array_column($users,'referral_code');

    $all_team_ids = array_merge($all_team_ids, $ids);
    $current_codes = $codes;
}

$all_team_ids = array_unique($all_team_ids);

/* ---------------------------------------------------------
   TEAM STATS CALCULATION
--------------------------------------------------------- */

$team_size = count($all_team_ids);

$team_recharge = 0;
$team_withdraw = 0;
$first_recharge = 0;
$first_withdraw = 0;

if($all_team_ids){

    $in = implode(',', array_fill(0,count($all_team_ids),'?'));

    /* TEAM RECHARGE */
    $stmt = $pdo->prepare("
        SELECT SUM(amount)
        FROM deposits
        WHERE status=1
        AND user_id IN ($in)
    ");
    $stmt->execute($all_team_ids);
    $team_recharge = $stmt->fetchColumn() ?? 0;

    /* TEAM WITHDRAW */
    $stmt = $pdo->prepare("
        SELECT SUM(amount)
        FROM withdrawals
        WHERE status=1
        AND user_id IN ($in)
    ");
    $stmt->execute($all_team_ids);
    $team_withdraw = $stmt->fetchColumn() ?? 0;

    /* FIRST RECHARGE */
    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT user_id)
        FROM deposits
        WHERE status=1
        AND user_id IN ($in)
    ");
    $stmt->execute($all_team_ids);
    $first_recharge = $stmt->fetchColumn();

    /* FIRST WITHDRAW */
    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT user_id)
        FROM withdrawals
        WHERE status=1
        AND user_id IN ($in)
    ");
    $stmt->execute($all_team_ids);
    $first_withdraw = $stmt->fetchColumn();
}

/* =========================================================
   GET LEVEL SETTINGS (LIMIT 3)
========================================================= */

$stmt = $pdo->prepare("
SELECT *
FROM referral_levels
WHERE status=1
AND level_no <= 3
ORDER BY level_no ASC
");

$stmt->execute();

$referral_levels = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* =========================================================
   BUILD LEVEL DATA
========================================================= */

$levels_data = [];
$current_codes = [$ref_code];

foreach($referral_levels as $level){

    $level_no = $level['level_no'];

    $register = 0;
    $valid = 0;
    $income = 0;
    $next_codes = [];

    if(!empty($current_codes)){

        $in = implode(',', array_fill(0,count($current_codes),'?'));

        $stmtUsers = $pdo->prepare("
            SELECT id, referral_code
            FROM users
            WHERE referred_by IN ($in)
        ");

        $stmtUsers->execute($current_codes);

        $users = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);

        $register = count($users);

        $user_ids = array_column($users,'id');
        $next_codes = array_column($users,'referral_code');

        if($user_ids){

            $in2 = implode(',', array_fill(0,count($user_ids),'?'));

            /* VALID USERS */
            $stmtValid = $pdo->prepare("
                SELECT COUNT(DISTINCT user_id)
                FROM deposits
                WHERE status=1
                AND user_id IN ($in2)
            ");

            $stmtValid->execute($user_ids);

            $valid = $stmtValid->fetchColumn();

            /* INCOME */
            $stmtIncome = $pdo->prepare("
                SELECT SUM(amount)
                FROM referral_commissions
                WHERE from_user_id IN ($in2)
                AND level=?
                AND user_id=?
            ");

            $params = array_merge($user_ids, [$level_no, $user_id]);

            $stmtIncome->execute($params);

            $income = $stmtIncome->fetchColumn() ?? 0;
        }
    }

    $levels_data[] = [
        'level_no' => $level_no,
        'register' => $register,
        'valid' => $valid,
        'income' => $income,
        'commission' => $level['commission_percent']
    ];

    $current_codes = $next_codes;
}

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

    <div><span>Team size</span><strong><?php echo $team_size; ?></strong></div>
    <div><span>Team recharge</span><strong>$<?php echo number_format($team_recharge,2); ?></strong></div>
    <div><span>Team Withdrawal</span><strong>$<?php echo number_format($team_withdraw,2); ?></strong></div>
    <div><span>New team</span><strong><?php echo $team_size; ?></strong></div>
    <div><span>First time recharge</span><strong><?php echo $first_recharge; ?></strong></div>
    <div><span>First withdrawal</span><strong><?php echo $first_withdraw; ?></strong></div>

</div>

<!-- LEVELS -->
<?php foreach($levels_data as $level): ?>

<div class="team-level level<?php echo $level['level_no']; ?>">

    <div class="level-badge">
        <img src="assets/images/medal.png">
        <span>LEVEL <?php echo $level['level_no']; ?></span>
    </div>

    <div class="level-panel">

        <div class="level-stats">

            <div>
                <p>Register/Valid</p>
                <strong><?php echo $level['register'].'/'.$level['valid']; ?></strong>
            </div>

            <div>
                <p>Total Income</p>
                <strong>$<?php echo number_format($level['income'],2); ?></strong>
            </div>

        </div>

        <div class="level-commission">
            <p>Commission Percentage</p>
            <strong><?php echo $level['commission']; ?>%</strong>
        </div>

    </div>

    <a href="team/<?php echo $level['level_no']; ?>.php" class="detail-btn">
        Details
    </a>

</div>

<?php endforeach; ?>

</div>

<script>

function copyCode(){
    navigator.clipboard.writeText("<?php echo $ref_code; ?>");
    alert("Code copied");
}

function copyLink(){
    var link = document.getElementById("refLink");
    navigator.clipboard.writeText(link.value);
    alert("Link copied");
}

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
