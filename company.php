<?php
session_start();

if(!isset($_SESSION['user_id'])){
header("Location: login.php");
exit;
}
?>

<?php include "inc/header.php"; ?>

<div class="company-header">

<a href="index.php">
<i class="fa fa-arrow-left"></i>
</a>

<span>Company Profile</span>

</div>


<div class="company-container">

<h3>Company Profile</h3>

<div class="company-box">

<h4 class="company-name">
Binance Digital Limited
</h4>

<p>
Company number <strong>12340481</strong>
</p>

<p>
7 Holburn Bell Court, London, WC2A 2JR
</p>

<p>
The company was established on November 29, 2019.
</p>

<br>

<p>
Our vision is to enable money to flow freely around the world. 
We believe that by promoting this freedom, we can significantly 
improve lives around the world.
</p>

</div>


<div class="company-description">

<h4>Company Profile</h4>

<p>
Binance's core values guide the team's goals, decisions, and actions, enabling collaboration across nationalities, cultures, and backgrounds to ultimately achieve the Binance team's shared vision.
</p>

</div>


<div class="company-doc">

<img src="assets/images/doc.jpg">

</div>

</div>


<?php include "inc/footer.php"; ?>
