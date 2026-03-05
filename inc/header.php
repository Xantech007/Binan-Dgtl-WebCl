<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Home</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
<style>
*{
    box-sizing:border-box;
}

body{
    margin:0;
    font-family:Arial, sans-serif;
    background:#111;
}

/* --- HEADER --- */
.header {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 20px;
    z-index: 9999;

    /* Transparent at top */
    background: rgba(0,0,0,0);
    transition: background 0.4s ease, backdrop-filter 0.4s ease;
}

/* Logo + Text */
.header-left {
    display: flex;
    align-items: center;
    gap: 10px;
}

.header-left img {
    width: 38px;
    height: 38px;
    border-radius: 50%;
}

.header-title {
    font-size: 20px;
    font-weight: 700;
    color: #fff;
    letter-spacing: 1px;
}

/* Language button exactly like screenshot */
.lang-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    background: rgba(255,255,255,0.1);
    padding: 6px 12px;
    border-radius: 10px;
    font-size: 14px;
    color: #fff;
    font-weight: 500;
}

.lang-btn i {
    font-size: 16px;
}

/* Brown header background when scrolling */
.header.scrolled {
    background: linear-gradient(135deg,#3a2b20,#5a402e);
    backdrop-filter: blur(4px);
}

/* ================= NEWS SCROLL ================= */

.news-wrapper{
    background:#111;
    padding:10px 0;
    overflow:hidden;
    border-bottom:1px solid #111;
}

.news-marquee{
    white-space:nowrap;
    overflow:hidden;
}

.news-content{
    display:inline-block;
    padding-left:100%;
    animation:scroll-left 20s linear infinite;
}

.news-item{
    margin-right:50px;
    font-weight:500;
    color:#fff;
}

@keyframes scroll-left{
    0%{ transform:translateX(0); }
    100%{ transform:translateX(-100%); }
}

/* ================= DASHBOARD ACTION SECTION ================= */

.dashboard-container{
    margin:20px;
    padding:20px;
    border-radius:18px;
    background:linear-gradient(135deg,#3a2b20,#5a402e);
    box-shadow:0 6px 18px rgba(0,0,0,0.3);
    color:#fff;
}

/* Top row */
.dashboard-top{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:15px;
}

.user-email{
    font-size:14px;
    opacity:0.9;
}

.vip-badge{
    background:#f6c27a;
    color:#000;
    padding:3px 8px;
    font-size:12px;
    border-radius:12px;
    margin-left:8px;
    font-weight:bold;
}

/* Wallet button */
.wallet-btn{
    width:38px;
    height:38px;
    background:#111;
    border-radius:50%;
    display:flex;
    justify-content:center;
    align-items:center;
    color:#fff;
    text-decoration:none;
    font-size:16px;
}

/* Balance */
.balance-box{
    background:#111;
    padding:15px;
    border-radius:30px;
    display:flex;
    justify-content:center;
    align-items:center;
    gap:10px;
    font-size:16px;
    margin-bottom:20px;
}

.balance-box strong{
    font-size:20px;
    color:#f6c27a;
}

/* Action icons */
.dashboard-actions{
    display:flex;
    justify-content:space-between;
    text-align:center;
}

.action-item{
text-decoration:none;
color:white;
text-align:center;
flex:1;
display:block;
}

.icon-circle{
    width:55px;
    height:55px;
    margin:0 auto 8px auto;
    border-radius:50%;
    background:linear-gradient(145deg,#f5c16c,#d89c3a);
    display:flex;
    justify-content:center;
    align-items:center;
    font-size:20px;
    box-shadow:0 4px 10px rgba(0,0,0,0.4);
}

.action-item span{
    font-size:13px;
}

.dashboard-actions a{
display:inline-block;
}

.dashboard-actions a{
text-decoration:none;
color:inherit;
}

.icon-circle i{
color:#fff;
}
    
/* ================= SMOOTH SLIDER ================= */

.banner-slider{
    margin:20px;
    border-radius:15px;
    overflow:hidden;
}

.banner-track{
    display:flex;
    transition:transform 0.6s ease-in-out;
}

.banner-track img{
    width:100%;
    height:auto;
    flex-shrink:0;
}

/* ================= COMMON TITLES ================= */

.task-title,
.member-title,
.reg-title{
    font-size:18px;
    font-weight:bold;
    margin-bottom:15px;
    color:#fff;
}

/* ================= TASK HALL ================= */


.task-section{
margin:20px;
}

.task-title{
color:#fff;
font-size:18px;
margin-bottom:15px;
}

.task-card{
display:flex;
align-items:center;
justify-content:space-between;
background:linear-gradient(90deg,#3a2b20,#5a402e);
border-radius:14px;
margin-bottom:14px;
text-decoration:none;
color:white;
overflow:hidden;
}

/* LEFT ICON */

.task-left{
position:relative;
width:70px;
height:70px;
display:flex;
align-items:center;
justify-content:center;
}

.vip-icon{
width:45px;
height:45px;
}

/* LOCK */

.lock-overlay{
position:absolute;
color:white;
font-size:20px;
}

/* TEXT */

.task-content{
flex:1;
padding:15px;
}

.unlock-text{
font-size:13px;
opacity:.8;
}

.unlock-text span{
color:#f6c27a;
font-weight:bold;
}

.vip-name{
font-size:14px;
margin-top:4px;
}

/* RIGHT ARROW */

.task-arrow{
width:55px;
height:70px;
background:linear-gradient(180deg,#f6c65c,#e5a826);
display:flex;
align-items:center;
justify-content:center;
}

.task-arrow i{
color:white;
font-size:18px;
}

/* ================= MEMBER LIST ================= */

.member-section{
    margin:20px;
}

.member-wrapper{
    height:210px;
    overflow:hidden;
    position:relative;
}

.member-track{
    display:flex;
    flex-direction:column;
    animation:scrollUp 15s linear infinite;
}

.member-row{
    margin-bottom:15px;
}

.member-card{
    background:linear-gradient(135deg,#3a2b20,#5a402e);
    border-radius:18px;
    padding:20px;
    color:#fff;
    box-shadow:0 4px 12px rgba(0,0,0,0.2);
}

.vip-level{
    font-size:14px;
    opacity:0.9;
}

.earning{
    font-size:22px;
    font-weight:bold;
    margin:8px 0;
    color:#f6c27a;
}

.email{
    font-size:13px;
    opacity:0.8;
}

@keyframes scrollUp{
    0% { transform:translateY(0); }
    100% { transform:translateY(-50%); }
}

/* ================= REGULATORY AUTHORITY ================= */

.reg-section{
    margin:20px;
}

.reg-container{
    background:linear-gradient(135deg,#3a2b20,#5a402e);
    border-radius:15px;
    padding:20px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    box-shadow:0 4px 12px rgba(0,0,0,0.2);
}

.reg-container img{
    width:48%;
    height:auto;
    object-fit:cover;
    border-radius:15px;
}

/* ================= FOOTER ================= */

/* Full-width wrapper touching screen edges */
.footer-wrapper {
    position: fixed;
    bottom: 0;
    left: 0;
    width: 100%;
    padding: 10px; /* space around the rounded footer */
    background: transparent; /* no background around */
}

/* Rounded footer bar */
.footer {
    background: linear-gradient(135deg,#3a2b20,#5a402e);
    padding: 12px 0;
    display: flex;
    justify-content: space-around;
    border-radius: 20px;
}

/* Footer links */
.footer a {
    color: #bdbdbd;
    text-decoration: none;
    font-size: 12px;
    display: flex;
    flex-direction: column;
    align-items: center;
}

/* Icons */
.footer i {
    font-size: 20px;
    margin-bottom: 4px;
}

/* Active icon + text (yellow highlight like screenshot) */
.footer a.active {
    color: #f4c277;
}

.footer a.active i {
    color: #f4c277;
}

/* Hover effect (optional) */
.footer a:hover,
.footer a:hover i {
    color: #e4b060;
}

/* Prevent content from hiding behind fixed header + footer */
body {
    padding-top: 75px;   /* adjust to match your header height */
    padding-bottom: 90px; /* adjust to match your footer height */
}


.lock-icon{
font-size:18px;
color:#fff;
margin-right:8px;
}

.vip-btn{
background:#f0b24b;
padding:8px 14px;
border-radius:8px;
color:#000;
text-decoration:none;
font-size:12px;
}

.vip-active{
background:#2ecc71;
padding:8px 14px;
border-radius:8px;
color:white;
text-decoration:none;
font-size:12px;
}

</style>
</head>
<body>

<div class="header" id="header">
    <div class="header-left">
        <img src="assets/images/logo.webp" alt="Logo">
        <div class="header-title">BINANCE DIGITAL</div>
    </div>

    <div class="lang-btn">
        <i class="fa-solid fa-globe"></i>
        English
    </div>
</div>
