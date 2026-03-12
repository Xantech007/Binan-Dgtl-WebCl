<?php
session_start();
require_once "config/database.php";
require_once "inc/countries.php";

if(isset($_SESSION['user_id'])){
header("Location: index.php");
exit;
}

$msg="";

/* Detect user country from IP */
function getUserCountry(){

$ip=$_SERVER['REMOTE_ADDR'];

$api=@json_decode(file_get_contents("http://ip-api.com/json/".$ip));

if($api && $api->status=="success"){
return $api->country;
}

return "";
}

$user_country=getUserCountry();

/* Get invite code from URL */
$invite_code = isset($_GET['invite']) ? htmlspecialchars($_GET['invite']) : "";

if($_SERVER["REQUEST_METHOD"]=="POST"){

$type=$_POST['type'];
$password=$_POST['password'];
$confirm=$_POST['confirm'];
$ref=trim($_POST['invite']);
$country=$_POST['country'];

if(empty($invite)){
$msg="Invitation code is required";

}elseif($password!=$confirm){

$msg="Passwords do not match";

}else{

$hash=password_hash($password,PASSWORD_DEFAULT);

if($type=="email"){

$email=$_POST['email'];

$check=$pdo->prepare("SELECT id FROM users WHERE email=?");
$check->execute([$email]);

if($check->rowCount()>0){
$msg="Email already exists";
}else{

$stmt=$pdo->prepare(
"INSERT INTO users(email,password,invite_code,country,vip_level,balance)
VALUES(?,?,?,?,?,?)"
);

$stmt->execute([$email,$hash,$invite,$country,0,0]);

header("Location: login.php");
exit;
}

}else{

$phone=$_POST['phone'];

$check=$pdo->prepare("SELECT id FROM users WHERE phone=?");
$check->execute([$phone]);

if($check->rowCount()>0){
$msg="Phone already exists";
}else{

$stmt=$pdo->prepare(
"INSERT INTO users(phone,password,invite_code,country,vip_level,balance)
VALUES(?,?,?,?,?,?)"
);

$stmt->execute([$phone,$hash,$invite,$country,0,0]);

header("Location: login.php");
exit;
}
}
}
}
?>

<!DOCTYPE html>
<html>
<head>

<meta name="viewport" content="width=device-width,initial-scale=1">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<title>Register</title>

<style>

body{
margin:0;
background:#0f1115;
font-family:Arial;
display:flex;
justify-content:center;
align-items:center;
min-height:100vh;
}

.wrapper{
width:100%;
max-width:700px;
padding:30px;
}

.box{
background:#14161c;
border-radius:20px;
padding:50px 40px;
box-shadow:0 0 40px rgba(0,0,0,.6);
}

.logo{
width:110px;
height:110px;
border-radius:50%;
display:block;
margin:auto;
}

.title{
text-align:center;
color:#f0b24b;
font-size:28px;
margin:15px 0 25px;
}

.tabs{
display:flex;
margin-bottom:20px;
}

.tabs div{
flex:1;
text-align:center;
padding:12px;
color:#aaa;
cursor:pointer;
border-bottom:2px solid transparent;
}

.tabs .active{
color:#fff;
border-color:#fff;
}

.input{
display:flex;
align-items:center;
background:rgba(240,178,75,.25);
padding:15px;
border-radius:10px;
margin-bottom:15px;
}

.input i{
color:white;
margin-right:10px;
}

.input input,
.input select{
border:none;
background:transparent;
outline:none;
color:white;
flex:1;
font-size:16px;
}

select option{
color:black;
}

.btn{
width:100%;
padding:16px;
border:none;
border-radius:30px;
font-size:17px;
cursor:pointer;
margin-top:10px;
}

.signup{
background:#f0b24b;
color:white;
}

.signin{
background:#2b2b2b;
color:white;
}

.form{
display:none;
}

.form.active{
display:block;
}

.msg{
color:red;
text-align:center;
margin-top:10px;
}

</style>
</head>

<body>

<div class="wrapper">
<div class="box">

<img src="assets/images/logo.webp" class="logo">

<div class="title">Create Account</div>

<div class="tabs">
<div class="active" onclick="switchTab(event,'emailForm')">Email Sign Up</div>
<div onclick="switchTab(event,'phoneForm')">Phone Sign Up</div>
</div>

<!-- EMAIL REGISTER -->

<form method="POST" id="emailForm" class="form active">

<input type="hidden" name="type" value="email">

<div class="input">
<i class="fa fa-envelope"></i>
<input type="email" name="email" placeholder="Email" required>
</div>

<div class="input">
<i class="fa fa-globe"></i>
<select name="country" required>

<option value="">Select Country</option>

<?php
foreach($countries as $country){

$selected = ($country == $user_country) ? "selected" : "";

echo "<option value=\"$country\" $selected>$country</option>";
}
?>

</select>
</div>

<div class="input">
<i class="fa fa-lock"></i>
<input type="password" name="password" placeholder="Password" required>
</div>

<div class="input">
<i class="fa fa-lock"></i>
<input type="password" name="confirm" placeholder="Confirm Password" required>
</div>

<div class="input">
<i class="fa fa-thumbs-up"></i>
<input type="text" name="invite" placeholder="Invitation Code"
value="<?= $invite_code ?>" required>
</div>

<button type="submit" class="btn signup">Create Account</button>

<button type="button" class="btn signin"
onclick="location.href='login.php'">
Sign In
</button>

</form>


<!-- PHONE REGISTER -->

<form method="POST" id="phoneForm" class="form">

<input type="hidden" name="type" value="phone">

<div class="input">
<i class="fa fa-phone"></i>
<input type="text" name="phone" placeholder="Phone Number" required>
</div>

<div class="input">
<i class="fa fa-globe"></i>
<select name="country" required>

<option value="">Select Country</option>

<?php
foreach($countries as $country){

$selected = ($country == $user_country) ? "selected" : "";

echo "<option value=\"$country\" $selected>$country</option>";
}
?>

</select>
</div>

<div class="input">
<i class="fa fa-lock"></i>
<input type="password" name="password" placeholder="Password" required>
</div>

<div class="input">
<i class="fa fa-lock"></i>
<input type="password" name="confirm" placeholder="Confirm Password" required>
</div>

<div class="input">
<i class="fa fa-thumbs-up"></i>
<input type="text" name="invite" placeholder="Invitation Code"
value="<?= $invite_code ?>" required>
</div>

<button type="submit" class="btn signup">Create Account</button>

<button type="button" class="btn signin"
onclick="location.href='login.php'">
Sign In
</button>

</form>

<?php if($msg): ?>
<p class="msg"><?= htmlspecialchars($msg) ?></p>
<?php endif; ?>

</div>
</div>

<script>

function switchTab(event,id){

document.querySelectorAll('.form')
.forEach(f=>f.classList.remove('active'));

document.getElementById(id)
.classList.add('active');

document.querySelectorAll('.tabs div')
.forEach(t=>t.classList.remove('active'));

event.target.classList.add('active');

}

</script>

</body>
</html>
