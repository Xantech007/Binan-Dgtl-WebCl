<?php
session_start();

if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php");
    exit;
}

require_once "../config/database.php";

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

$my_code = $user['referral_code'];

/* =========================================================
   FETCH LEVEL 1 MEMBERS
========================================================= */

$stmt = $pdo->prepare("
SELECT
    id,
    email,
    phone,
    vip_level,
    balance,
    created_at
FROM users
WHERE referred_by=?
ORDER BY id DESC
");

$stmt->execute([$my_code]);

$members = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* =========================================================
   TEAM ANALYTICS
========================================================= */

$rows = [];

$total_users = count($members);

$total_balance = 0;
$total_deposits = 0;
$total_withdrawals = 0;
$total_generated = 0;

foreach($members as $m){

    $member_id = $m['id'];

    /*
    |--------------------------------------------------------------------------
    | TOTAL DEPOSITS
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
    SELECT SUM(amount)
    FROM deposits
    WHERE user_id=?
    AND status=1
    ");

    $stmt->execute([$member_id]);

    $deposits = $stmt->fetchColumn() ?? 0;

    /*
    |--------------------------------------------------------------------------
    | TOTAL WITHDRAWALS
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
    SELECT SUM(amount)
    FROM withdrawals
    WHERE user_id=?
    AND status=1
    ");

    $stmt->execute([$member_id]);

    $withdrawals = $stmt->fetchColumn() ?? 0;

    /*
    |--------------------------------------------------------------------------
    | TOTAL GENERATED FOR YOU
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
    SELECT SUM(amount)
    FROM referral_commissions
    WHERE user_id=?
    AND from_user_id=?
    ");

    $stmt->execute([
        $user_id,
        $member_id
    ]);

    $generated = $stmt->fetchColumn() ?? 0;

    /*
    |--------------------------------------------------------------------------
    | TOTAL VIP CLAIMS
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM vip_claims
    WHERE user_id=?
    ");

    $stmt->execute([$member_id]);

    $vip_claims = $stmt->fetchColumn();

    /*
    |--------------------------------------------------------------------------
    | LAST VIP ACTIVITY
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
    SELECT claimed_at
    FROM vip_claims
    WHERE user_id=?
    ORDER BY id DESC
    LIMIT 1
    ");

    $stmt->execute([$member_id]);

    $last_claim = $stmt->fetchColumn();

    /*
    |--------------------------------------------------------------------------
    | SUMMARY TOTALS
    |--------------------------------------------------------------------------
    */

    $total_balance += $m['balance'];
    $total_deposits += $deposits;
    $total_withdrawals += $withdrawals;
    $total_generated += $generated;

    /*
    |--------------------------------------------------------------------------
    | STORE ROW
    |--------------------------------------------------------------------------
    */

    $rows[] = [
        'user' => $m,
        'deposits' => $deposits,
        'withdrawals' => $withdrawals,
        'generated' => $generated,
        'vip_claims' => $vip_claims,
        'last_claim' => $last_claim
    ];
}

include "../inc/header.php";
?>

<style>

.team-page{
    padding:20px;
    color:#fff;
}

.team-top{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:20px;
}

.team-top a{
    color:#fff;
    text-decoration:none;
    font-size:14px;
}

.team-title{
    font-size:22px;
    font-weight:bold;
}

.summary-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(180px,1fr));
    gap:12px;
    margin-bottom:20px;
}

.summary-card{
    background:#181b22;
    border-radius:12px;
    padding:15px;
}

.summary-card span{
    display:block;
    color:#aaa;
    font-size:13px;
    margin-bottom:8px;
}

.summary-card strong{
    font-size:18px;
    color:#f0b24b;
}

.table-wrap{
    overflow-x:auto;
}

table{
    width:100%;
    border-collapse:collapse;
    background:#14161c;
    border-radius:12px;
    overflow:hidden;
}

th{
    background:#1e222b;
    color:#f0b24b;
    padding:14px;
    font-size:14px;
    text-align:left;
}

td{
    padding:14px;
    border-bottom:1px solid #222;
    font-size:14px;
}

tr:last-child td{
    border-bottom:none;
}

.vip-badge{
    background:#2d3140;
    padding:5px 10px;
    border-radius:6px;
    font-size:12px;
    display:inline-block;
}

.empty-box{
    background:#181b22;
    padding:30px;
    border-radius:12px;
    text-align:center;
    color:#999;
}

.user-name{
    font-weight:bold;
    color:#fff;
}

.small-text{
    font-size:12px;
    color:#888;
    margin-top:5px;
}

</style>

<div class="team-page">

    <!-- HEADER -->

    <div class="team-top">

        <a href="../team.php">
            ← Back
        </a>

        <div class="team-title">
            Level 1 Team
        </div>

    </div>

    <!-- SUMMARY -->

    <div class="summary-grid">

        <div class="summary-card">
            <span>Total Members</span>
            <strong><?php echo $total_users; ?></strong>
        </div>

        <div class="summary-card">
            <span>Total Deposits</span>
            <strong>$<?php echo number_format($total_deposits,2); ?></strong>
        </div>

        <div class="summary-card">
            <span>Total Withdrawals</span>
            <strong>$<?php echo number_format($total_withdrawals,2); ?></strong>
        </div>

        <div class="summary-card">
            <span>Total Generated</span>
            <strong>$<?php echo number_format($total_generated,2); ?></strong>
        </div>

        <div class="summary-card">
            <span>Total Balance</span>
            <strong>$<?php echo number_format($total_balance,2); ?></strong>
        </div>

    </div>

    <!-- TABLE -->

    <div class="table-wrap">

        <table>

            <tr>
                <th>User</th>
                <th>VIP</th>
                <th>Deposits</th>
                <th>Withdrawals</th>
                <th>Generated</th>
                <th>VIP Claims</th>
                <th>Balance</th>
                <th>Last Activity</th>
                <th>Joined</th>
            </tr>

            <?php if(!$rows): ?>

            <tr>

                <td colspan="9">

                    <div class="empty-box">
                        No referrals yet
                    </div>

                </td>

            </tr>

            <?php else: ?>

            <?php foreach($rows as $r):

                $u = $r['user'];

            ?>

            <tr>

                <td>

                    <div class="user-name">
                        <?php
                        echo htmlspecialchars(
                            $u['email'] ?: $u['phone']
                        );
                        ?>
                    </div>

                </td>

                <td>

                    <span class="vip-badge">
                        VIP<?php echo (int)$u['vip_level']; ?>
                    </span>

                </td>

                <td>
                    $<?php echo number_format($r['deposits'],2); ?>
                </td>

                <td>
                    $<?php echo number_format($r['withdrawals'],2); ?>
                </td>

                <td>
                    $<?php echo number_format($r['generated'],2); ?>
                </td>

                <td>
                    <?php echo $r['vip_claims']; ?>
                </td>

                <td>
                    $<?php echo number_format($u['balance'],2); ?>
                </td>

                <td>

                    <?php
                    if($r['last_claim']){

                        echo date(
                            "d M Y H:i",
                            strtotime($r['last_claim'])
                        );

                    }else{

                        echo "No activity";

                    }
                    ?>

                </td>

                <td>
                    <?php
                    echo date(
                        "d M Y",
                        strtotime($u['created_at'])
                    );
                    ?>
                </td>

            </tr>

            <?php endforeach; ?>

            <?php endif; ?>

        </table>

    </div>

</div>

    <!-- TEAM-FOOTER -->
<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<div class="footer-wrapper">
    <div class="footer">

        <a href="index.php" class="<?php if($currentPage=='../index.php') echo 'active'; ?>">
            <i class="fa-solid fa-house"></i>
            <span>Home</span>
        </a>

        <a href="mission.php" class="<?php if($currentPage=='../mission.php') echo 'active'; ?>">
            <i class="fa-solid fa-list-check"></i>
            <span>Task</span>
        </a>

        <a href="team.php" class="<?php if($currentPage=='../team.php') echo 'active'; ?>">
            <i class="fa-solid fa-people-group"></i>
            <span>Team</span>
        </a>

        <a href="vip.php" class="<?php if($currentPage=='../vip.php') echo 'active'; ?>">
            <i class="fa-solid fa-crown"></i>
            <span>VIP</span>
        </a>

        <a href="mine.php" class="<?php if($currentPage=='../mine.php') echo 'active'; ?>">
            <i class="fa-solid fa-user"></i>
            <span>Me</span>
        </a>

    </div>
</div>

<script>

/* BANNER SLIDER */

window.addEventListener("load", function(){

    var track = document.querySelector(".banner-track");
    var slides = document.querySelectorAll(".banner-track img");

    if(!track || slides.length < 2) return;

    var current = 0;

    setInterval(function(){
        current = (current + 1) % slides.length;
        track.style.transform = "translateX(-" + (current * 100) + "%)";
    },1500);

});


/* HEADER SCROLL EFFECT */

document.addEventListener("scroll", function () {
    const header = document.getElementById("header");

    if(!header) return;

    if (window.scrollY > 10) {
        header.classList.add("scrolled");
    } else {
        header.classList.remove("scrolled");
    }
});


/* HEADER + FOOTER SPACING */

function adjustSpacing() {

    const header = document.querySelector('.header');
    const footer = document.querySelector('.footer-wrapper');

    if(!header || !footer) return;

    const headerHeight = header.offsetHeight;
    const footerHeight = footer.offsetHeight;

    document.body.style.paddingTop = headerHeight + "px";
    document.body.style.paddingBottom = footerHeight + "px";
}

window.addEventListener("load", adjustSpacing);
window.addEventListener("resize", adjustSpacing);

</script>

<script>

function googleTranslateElementInit() {

new google.translate.TranslateElement(
{
pageLanguage:'en',
includedLanguages:'en,es,fr,pt,ru,ar,zh-CN,hi',
autoDisplay:false
},
'google_translate_element'
);

}

</script>

<script src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>

<script>
let deferredPrompt;

window.addEventListener("beforeinstallprompt", (e) => {
    e.preventDefault();
    deferredPrompt = e;
    console.log("PWA install ready");
});

function installApp(){
    if(deferredPrompt){
        deferredPrompt.prompt();

        deferredPrompt.userChoice.then((choiceResult) => {
            if(choiceResult.outcome === "accepted"){
                console.log("User installed the app");
            } else {
                console.log("User dismissed install");
            }
            deferredPrompt = null;
        });
    } else {
        alert("Install not available yet. Use Chrome and visit again.");
    }
}
</script>

</body>
</html>
