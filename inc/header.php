<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Home</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
    
<?php include "assets/css/style.css"; ?>


</head>
<body>

<div class="header" id="header">

<div class="header-left">
<img src="assets/images/logo.webp">
<div class="header-title">BINANCE DIGITAL</div>
</div>

<div class="lang-btn" id="langToggle">

<i class="fa-solid fa-globe"></i>
<span id="currentLang">English</span>

<i class="fa-solid fa-angle-down"></i>

<div class="lang-dropdown" id="langMenu">

<div onclick="setLanguage('en','English')">English</div>
<div onclick="setLanguage('es','Spanish')">Español</div>
<div onclick="setLanguage('fr','French')">Français</div>
<div onclick="setLanguage('pt','Portuguese')">Português</div>
<div onclick="setLanguage('ru','Russian')">Русский</div>
<div onclick="setLanguage('ar','Arabic')">العربية</div>
<div onclick="setLanguage('zh-CN','Chinese')">中文</div>
<div onclick="setLanguage('hi','Hindi')">Hindi</div>

</div>

</div>

</div>

<div id="google_translate_element" style="display:none;"></div>
