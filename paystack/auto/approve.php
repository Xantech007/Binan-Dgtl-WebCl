<?php
session_start();

require_once "../../config/database.php";

/* USER MUST BE LOGGED IN */
if(!isset($_SESSION['user_id'])){

    $_SESSION['withdraw_msg'] = "Please login first.";

    header("Location: ../../index.php");
    exit;
}

$user_id = (int)$_SESSION['user_id'];

try{

    /*
    |--------------------------------------------------------------------------
    | GET LATEST USER DEPOSIT
    |--------------------------------------------------------------------------
    */
    $stmt = $pdo->prepare("
        SELECT *
        FROM deposits
        WHERE user_id = ?
        ORDER BY id DESC
        LIMIT 1
    ");

    $stmt->execute([$user_id]);

    $deposit = $stmt->fetch(PDO::FETCH_ASSOC);

    /* NO DEPOSIT */
    if(!$deposit){

        $_SESSION['withdraw_msg'] = "No deposit found.";

        header("Location: ../../index.php");
        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | STRICT CHECKS
    |--------------------------------------------------------------------------
    */

    /* MUST BE PAYSTACK */
    if($deposit['paystack'] !== 'yes'){

        $_SESSION['withdraw_msg'] = "Latest deposit is not a Paystack payment.";

        header("Location: ../../index.php");
        exit;
    }

    /* MUST STILL BE PENDING */
    if((string)$deposit['status'] !== "0"){

        $_SESSION['withdraw_msg'] = "Deposit already processed.";

        header("Location: ../../index.php");
        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | APPROVE ONLY THIS EXACT DEPOSIT
    |--------------------------------------------------------------------------
    */
    $update = $pdo->prepare("
        UPDATE deposits
        SET status = '1'
        WHERE id = ?
        AND user_id = ?
        AND paystack = 'yes'
        AND status = '0'
        LIMIT 1
    ");

    $update->execute([
        $deposit['id'],
        $user_id
    ]);

    /* VERIFY UPDATE */
    if($update->rowCount() > 0){

        $_SESSION['recharge_msg'] = "Payment approved successfully.";

    }else{

        $_SESSION['withdraw_msg'] = "Unable to approve payment.";

    }

}catch(Exception $e){

    $_SESSION['withdraw_msg'] = "An error occurred while approving payment.";

}

/* REDIRECT */
header("Location: ../../index.php");
exit;
?>
