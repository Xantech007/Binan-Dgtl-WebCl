<?php
session_start();
require_once "config/database.php";

if(!isset($_SESSION['user_id'])){
header("Location: login.php");
exit;
}

$user_id = $_SESSION['user_id'];

$msg="";
$success="";

/* GET USER PASSWORD */

$stmt=$pdo->prepare("SELECT password FROM users WHERE id=?");
$stmt->execute([$user_id]);
$user=$stmt->fetch(PDO::FETCH_ASSOC);


if($_SERVER["REQUEST_METHOD"]=="POST"){

$old=$_POST['old_password'];
$new=$_POST['new_password'];
$confirm=$_POST['confirm_password'];


/* VERIFY OLD PASSWORD */

if(!password_verify($old,$user['password'])){

$msg="Old password is incorrect";

}

elseif(strlen($new) < 6){

$msg="Password must be at least 6 characters";

}

elseif($new != $confirm){

$msg="New passwords do not match";

}

else{

$newHash=password_hash($new,PASSWORD_DEFAULT);

/* UPDATE PASSWORD */

$stmt=$pdo->prepare("UPDATE users SET password=? WHERE id=?");
$stmt->execute([$newHash,$user_id]);

$success="Password changed successfully";

}

}

?>

<?php include "inc/header.php"; ?>


<div class="change-header">

<a onclick="goBack()">
<i class="fa fa-arrow-left"></i>
</a>

<span>Change Password</span>

</div>


<div class="change-container">


<form method="POST">

<div class="pwd-box">

<input
type="password"
name="old_password"
placeholder="Old Password"
required>

<i class="fa fa-eye toggle"></i>

</div>


<div class="pwd-box">

<input
type="password"
name="new_password"
placeholder="New Password"
required>

<i class="fa fa-eye toggle"></i>

</div>


<div class="pwd-box">

<input
type="password"
name="confirm_password"
placeholder="Reenter new password"
required>

<i class="fa fa-eye toggle"></i>

</div>


<button class="change-btn">
Confirm
</button>

</form>


<?php if($msg): ?>

<div class="change-error">
<?php echo htmlspecialchars($msg); ?>
</div>

<?php endif; ?>


<?php if($success): ?>

<div class="change-success">
<?php echo htmlspecialchars($success); ?>
</div>

<?php endif; ?>


</div>


<?php include "inc/footer.php"; ?>


<script>

/* BACK BUTTON */

function goBack(){

if(document.referrer){
window.history.back();
}else{
window.location.href="index.php";
}

}


/* PASSWORD TOGGLE */

document.querySelectorAll(".toggle").forEach(function(icon){

icon.onclick=function(){

let input=this.previousElementSibling;

input.type = input.type === "password" ? "text" : "password";

}

});

</script>
