<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Home</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link rel="icon" type="image/png" href="assets/images/logo.webp">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<?php include "assets/css/style.css"; ?>

</head>
<body>

<div class="header" id="header">

<div class="header-left">

<a href="/" style="display:flex; align-items:center; gap:10px; text-decoration:none;">

    <img src="assets/images/logo.webp">

    <div class="header-title">
        BINANCE DIGITAL
    </div>

</a>

</div>

<!-- GOOGLE TRANSLATE -->
<div class="lang-btn">

    <i class="fa-solid fa-globe"></i>

    <?php include __DIR__ . "/translate.php"; ?>

</div>

</div>

<?php include __DIR__ . "/whatsapp-support.php"; ?>
