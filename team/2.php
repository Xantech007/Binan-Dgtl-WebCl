<?php
session_start();

if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php");
    exit;
}

require_once "../config/database.php";

$user_id = $_SESSION['user_id'];

/* =========================================================
   GET MY REFERRAL CODE
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
   GET LEVEL 1 REFERRAL CODES
========================================================= */

$stmt = $pdo->prepare("
SELECT referral_code
FROM users
WHERE referred_by=?
");

$stmt->execute([$my_code]);

$level1_codes = $stmt->fetchAll(PDO::FETCH_COLUMN);

/* =========================================================
   GET LEVEL 2 MEMBERS
========================================================= */

$members = [];

if($level1_codes){

    $placeholders = implode(
        ',',
        array_fill(0,count($level1_codes),'?')
    );

    $sql = "
    SELECT
        id,
        email,
        phone,
        vip_level,
        balance,
        created_at
    FROM users
    WHERE referred_by IN ($placeholders)
    ORDER BY id DESC
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute($level1_codes);

    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

}

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
    | TOTAL GENERATED FOR YOU (LEVEL 2)
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
    SELECT SUM(amount)
    FROM referral_commissions
    WHERE user_id=?
    AND from_user_id=?
    AND level=2
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
            Level 2 Team
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
                        No level 2 referrals
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

<?php include "inc/team-footer.php"; ?>
