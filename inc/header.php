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

/* NEWS SECTION */

.news-wrapper{
display:flex;
align-items:center;
background:#111;
padding:10px 0;
overflow:hidden;
border-bottom:1px solid #111;
}

.news-icon{
padding:0 15px;
font-size:16px;
color:#fff;
}

.news-marquee{
flex:1;
overflow:hidden;
white-space:nowrap;
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

/* RECHARGE PAGE */

.recharge-header{
display:flex;
align-items:center;
padding:15px;
color:white;
background:linear-gradient(90deg,#2c1f16,#4a3324);
}

.recharge-header span{
flex:1;
text-align:center;
font-size:16px;
}

.recharge-header a{
color:white;
text-decoration:none;
}

.recharge-container{
margin:20px;
background:linear-gradient(90deg,#3a2b20,#5a402e);
border-radius:12px;
overflow:hidden;
}

.recharge-item{
display:flex;
justify-content:space-between;
align-items:center;
padding:18px;
text-decoration:none;
color:white;
border-bottom:1px dashed rgba(255,255,255,0.3);
}

.recharge-item:last-child{
border-bottom:none;
}

.recharge-left{
display:flex;
align-items:center;
gap:12px;
}

.recharge-icon{
width:28px;
height:28px;
object-fit:contain;
}

.recharge-name{
font-size:14px;
}

.recharge-right{
color:#ccc;
font-size:16px;
}

/* ================= DEPOSIT PAGE ================= */

.deposit-header{
display:flex;
align-items:center;
padding:15px;
background:linear-gradient(90deg,#2c1f16,#4a3324);
color:#fff;
font-size:16px;
}

.deposit-header a{
color:#fff;
text-decoration:none;
margin-right:10px;
font-size:18px;
}

.deposit-header span{
flex:1;
text-align:center;
font-weight:500;
}

/* MAIN CONTAINER */

.deposit-container{
margin:20px;
background:linear-gradient(135deg,#3a2b20,#5a402e);
padding:20px;
border-radius:15px;
color:#fff;
box-shadow:0 4px 15px rgba(0,0,0,0.4);
}

/* TOP BRAND */

.deposit-top{
display:flex;
align-items:center;
gap:10px;
margin-bottom:20px;
}

.deposit-logo{
width:30px;
height:30px;
border-radius:50%;
}

.deposit-top span{
font-size:14px;
font-weight:500;
}

/* PAYMENT METHOD */

.deposit-method{
display:flex;
justify-content:center;
align-items:center;
gap:8px;
margin-bottom:15px;
font-size:14px;
}

.method-icon{
width:28px;
height:28px;
object-fit:contain;
}

/* QR CODE */

.deposit-qr{
display:flex;
justify-content:center;
margin:20px 0;
}

.deposit-qr img{
width:180px;
height:180px;
border-radius:12px;
background:#fff;
padding:10px;
}

/* ADDRESS */

.deposit-address-title{
text-align:center;
font-size:15px;
margin-bottom:10px;
}

.deposit-address{
display:flex;
background:#222;
border-radius:10px;
overflow:hidden;
margin-bottom:20px;
}

.deposit-address input{
flex:1;
border:none;
background:#222;
color:#fff;
padding:12px;
font-size:13px;
outline:none;
}

.deposit-address button{
background:#f0b24b;
border:none;
padding:10px 16px;
cursor:pointer;
font-size:13px;
border-radius:0 10px 10px 0;
}

/* UPLOAD PROOF */

.upload-proof{
margin-bottom:20px;
}

.upload-proof label{
font-size:13px;
display:block;
margin-bottom:6px;
}

.upload-proof input{
width:100%;
background:#111;
border:1px solid #444;
padding:10px;
border-radius:8px;
color:#fff;
}

/* RECHARGE BUTTON */

.deposit-btn{
width:100%;
padding:14px;
background:#f0b24b;
border:none;
border-radius:30px;
font-size:15px;
cursor:pointer;
color:#fff;
font-weight:500;
box-shadow:0 4px 10px rgba(0,0,0,0.4);
}

/* MESSAGE */

.deposit-msg{
margin-top:15px;
color:#4CAF50;
text-align:center;
font-size:14px;
}

/* NOTE */

.deposit-note{
margin-top:20px;
font-size:12px;
opacity:0.85;
line-height:1.5;
background:#1a1a1a;
padding:15px;
border-radius:10px;
}

/* MOBILE FIX */

@media(max-width:480px){

.deposit-container{
margin:15px;
padding:18px;
}

.deposit-qr img{
width:150px;
height:150px;
}

.deposit-address input{
font-size:12px;
}

.deposit-btn{
font-size:14px;
}

}

.recharge-success{
margin:20px;
background:#2ecc71;
color:#fff;
padding:15px;
border-radius:10px;
text-align:center;
font-size:14px;
}

/* ================= WITHDRAW ================= */

.withdraw-header{
display:flex;
align-items:center;
padding:15px;
background:linear-gradient(90deg,#2c1f16,#4a3324);
color:white;
}

.withdraw-header span{
flex:1;
text-align:center;
font-weight:500;
}

.withdraw-header a{
color:#fff;
text-decoration:none;
font-size:18px;
}

/* MAIN CONTAINER */

.withdraw-container{
margin:20px;
background:linear-gradient(135deg,#3a2b20,#5a402e);
padding:20px;
border-radius:15px;
color:white;
}

/* BALANCE BOX */

.withdraw-balance{
background:#2b2b2b;
padding:15px;
border-radius:10px;
margin-bottom:15px;
font-size:14px;
}

.withdraw-balance strong{
display:block;
margin-top:5px;
font-size:18px;
}

/* WITHDRAW METHODS */

.withdraw-methods{
display:flex;
flex-wrap:wrap;
gap:10px;
margin-bottom:15px;
}

.method{
display:flex;
align-items:center;
gap:8px;
background:#2b2b2b;
padding:8px 12px;
border-radius:8px;
cursor:pointer;
font-size:13px;
}

.method input{
display:none;
}

.method-icon{
width:20px;
height:20px;
object-fit:contain;
}

/* SELECTED METHOD */

.method input:checked + img{
border:2px solid #f0b24b;
border-radius:6px;
}

/* INPUTS */

.withdraw-input{
width:100%;
padding:12px;
margin-bottom:12px;
border-radius:10px;
border:none;
background:#222;
color:white;
font-size:14px;
}

/* SUMMARY */

.withdraw-summary{
margin:10px 0;
display:flex;
justify-content:space-between;
font-size:14px;
}

/* BUTTON */

.withdraw-btn{
width:100%;
padding:14px;
background:#f0b24b;
border:none;
border-radius:25px;
font-size:16px;
cursor:pointer;
color:#fff;
font-weight:500;
}

/* INFO TEXT */

.withdraw-info{
margin-top:15px;
font-size:12px;
opacity:.8;
line-height:1.5;
}

/* SUCCESS MESSAGE */

.withdraw-success{
margin:20px;
background:#3498db;
color:#fff;
padding:15px;
border-radius:10px;
text-align:center;
font-size:14px;
}

/* ERROR MESSAGE */

.withdraw-error{
margin-top:15px;
background:#e74c3c;
color:#fff;
padding:12px;
border-radius:8px;
text-align:center;
font-size:14px;
}

/* MOBILE OPTIMIZATION */

@media(max-width:480px){

.withdraw-container{
margin:15px;
padding:18px;
}

.method{
font-size:12px;
padding:6px 10px;
}

.withdraw-btn{
font-size:15px;
}

}

.method input:checked + img{
border:2px solid #f0b24b;
box-shadow:0 0 8px #f0b24b;
}

/* COMPANY PAGE */

.company-header{
display:flex;
align-items:center;
padding:15px;
background:linear-gradient(90deg,#2c1f16,#4a3324);
color:white;
}

.company-header span{
flex:1;
text-align:center;
font-weight:500;
}

.company-header a{
color:#fff;
text-decoration:none;
font-size:18px;
}

.company-container{
margin:20px;
color:white;
}

.company-container h3{
margin-bottom:10px;
}

.company-box{
background:linear-gradient(135deg,#3a2b20,#5a402e);
padding:20px;
border-radius:15px;
margin-bottom:20px;
}

.company-name{
text-align:center;
margin-bottom:10px;
}

.company-description{
background:linear-gradient(135deg,#3a2b20,#5a402e);
padding:20px;
border-radius:15px;
margin-bottom:20px;
}

.company-doc{
text-align:center;
}

.company-doc img{
width:100%;
max-width:350px;
border-radius:10px;
background:#fff;
padding:10px;
}

 /* MISSION PAGE */

.task-reset-box{
margin:20px;
border:1px solid #f0b24b;
padding:12px;
border-radius:10px;
color:white;
display:flex;
align-items:center;
gap:8px;
}

.task-reset-box strong{
margin-left:auto;
color:#f0b24b;
font-size:16px;
}

.task-stats{
margin:20px;
background:linear-gradient(135deg,#3a2b20,#5a402e);
padding:15px;
border-radius:12px;
display:flex;
justify-content:space-between;
color:white;
}

.task-stats div{
text-align:center;
}

.task-panel{
margin:20px;
background:linear-gradient(135deg,#3a2b20,#5a402e);
border-radius:12px;
padding:15px;
color:white;
}

.task-tabs{
display:flex;
justify-content:space-between;
margin-bottom:20px;
}

.tab{
cursor:pointer;
padding-bottom:5px;
}

.tab.active{
border-bottom:2px solid white;
}

.task-content{
display:none;
text-align:center;
padding:40px 0;
}

.task-content.active{
display:block;
}

.no-data{
opacity:.7;
}

.no-data i{
font-size:40px;
margin-bottom:10px;
}   

/* TASK RESET */

.task-reset-container{
text-align:center;
margin:20px;
}

.reset-time{
font-size:24px;
font-weight:bold;
color:#f0b24b;
}

.reset-label{
color:#f0b24b;
font-size:14px;
margin-top:5px;
}

/* ================= TEAM PAGE ================= */

.team-container{
margin:20px;
color:#fff;
}

/* ================= REFERRAL BOX ================= */

.ref-box{
background:linear-gradient(135deg,#3a2b20,#5a402e);
padding:20px;
border-radius:14px;
margin-bottom:20px;
}

.ref-code{
display:flex;
align-items:center;
gap:10px;
margin-bottom:10px;
}

.ref-code span{
font-size:14px;
opacity:.9;
}

.ref-code strong{
font-size:24px;
font-weight:bold;
}

.ref-code button{
background:#000;
color:#fff;
border:none;
border-radius:20px;
padding:4px 12px;
font-size:12px;
cursor:pointer;
}

.ref-link p{
font-size:13px;
margin:10px 0 5px;
opacity:.8;
}

.ref-link{
display:flex;
align-items:center;
gap:10px;
flex-wrap:wrap;
}

.ref-link input{
flex:1;
padding:8px 10px;
border-radius:8px;
border:none;
background:#2b2b2b;
color:#fff;
font-size:12px;
}

.ref-link button{
background:#000;
color:#fff;
border:none;
border-radius:20px;
padding:4px 12px;
font-size:12px;
cursor:pointer;
}

/* ================= SOCIAL ICONS ================= */

.social-icons{
display:flex;
gap:12px;
margin-top:15px;
flex-wrap:wrap;
}

.social-icons i{
width:34px;
height:34px;
border-radius:50%;
display:flex;
align-items:center;
justify-content:center;
background:#3c3c3c;
font-size:14px;
opacity:0;
transform:scale(.5);
transition:0.4s;
}

.social-icons i.show{
opacity:1;
transform:scale(1);
}

/* ================= TEAM STATS ================= */

.team-stats{
background:linear-gradient(135deg,#3a2b20,#5a402e);
padding:18px;
border-radius:12px;
display:grid;
grid-template-columns:repeat(3,1fr);
gap:15px;
text-align:center;
margin-bottom:20px;
}

.team-stats span{
font-size:12px;
opacity:.8;
display:block;
}

.team-stats strong{
font-size:16px;
display:block;
margin-top:3px;
}

/* ================= LEVEL CARDS ================= */

.team-level{
position:relative;
display:flex;
align-items:center;
padding:18px;
border-radius:14px;
margin-bottom:18px;
color:white;
overflow:hidden;
}

/* LEVEL COLORS */

.level1{
background:linear-gradient(90deg,#f1d24c,#2fb07f);
}

.level2{
background:linear-gradient(90deg,#ff5d87,#ff7e66);
}

.level3{
background:linear-gradient(90deg,#5aa6d6,#a9d2a1);
}

/* LEFT BADGE */

.level-badge{
display:flex;
align-items:center;
gap:8px;
min-width:110px;
}

.level-badge img{
width:36px;
}

.level-badge span{
font-weight:bold;
font-size:14px;
}

/* INNER PANEL */

.level-panel{
flex:1;
background:rgba(0,0,0,0.2);
padding:15px 18px;
border-radius:12px;
display:flex;
justify-content:space-between;
align-items:center;
}

/* STATS */

.level-stats{
display:flex;
flex-direction:column;
gap:6px;
}

.level-stats p{
font-size:12px;
margin:0;
opacity:.9;
}

.level-stats strong{
font-size:14px;
}

/* COMMISSION */

.level-commission{
text-align:right;
}

.level-commission p{
font-size:12px;
margin:0;
opacity:.9;
}

.level-commission strong{
font-size:15px;
}

/* DETAILS BUTTON */

.detail-btn{
background:#000;
color:#fff;
padding:6px 14px;
border-radius:20px;
font-size:12px;
text-decoration:none;
margin-left:12px;
white-space:nowrap;
}

/* ================= MOBILE FIX ================= */

@media (max-width:600px){

.level-panel{
flex-direction:column;
align-items:flex-start;
gap:8px;
}

.level-commission{
text-align:left;
}

}
    
/* ================= TEAM DETAILS PAGES ================= */

.team-detail{
margin:20px;
color:#fff;
}

/* HEADER */

.team-header{
display:flex;
align-items:center;
padding:12px 0;
margin-bottom:15px;
}

.team-header a{
color:#fff;
font-size:18px;
text-decoration:none;
}

.team-header span{
flex:1;
text-align:center;
font-size:18px;
font-weight:500;
}

/* EMPTY STATE */

.team-empty{
background:linear-gradient(135deg,#3a2b20,#5a402e);
padding:40px;
border-radius:12px;
text-align:center;
opacity:.7;
}

/* MEMBER CARD */

.team-member{
background:linear-gradient(135deg,#3a2b20,#5a402e);
padding:15px;
border-radius:12px;
margin-bottom:12px;
box-shadow:0 3px 10px rgba(0,0,0,0.2);
transition:.2s;
}

.team-member:hover{
transform:translateY(-2px);
}

/* MEMBER EMAIL */

.team-member strong{
font-size:14px;
word-break:break-all;
}

/* INFO ROW */

.member-info{
font-size:13px;
margin-top:4px;
opacity:.9;
}

/* VIP BADGE */

.member-info span{
background:#f0b24b;
color:#000;
padding:2px 8px;
border-radius:12px;
font-size:11px;
margin-right:5px;
}

/* DATE */

.member-date{
font-size:12px;
margin-top:4px;
opacity:.7;
}

/* MOBILE */

@media (max-width:600px){

.team-member{
padding:14px;
}

.team-header span{
font-size:16px;
}

.member-info{
font-size:12px;
}

.member-date{
font-size:11px;
}

}

/* ================= VIP PAGE ================= */

.vip-container{
margin:20px;
}


/* SUCCESS MESSAGE */

.vip-success{
background:#4CAF50;
color:#fff;
padding:12px;
border-radius:10px;
text-align:center;
margin-bottom:15px;
font-size:14px;
}


/* ================= VIP CARD ================= */

.vip-card{
background:linear-gradient(135deg,#3a2b20,#5a402e);
border-radius:14px;
padding:20px;
margin-bottom:18px;
position:relative;
color:#fff;
}


/* VIP LABEL */

.vip-label{
position:absolute;
top:0;
left:0;
background:linear-gradient(90deg,#ff7b6b,#ffb36b);
padding:4px 12px;
font-size:12px;
font-weight:bold;
border-radius:0 0 10px 0;
color:#000;
}


/* ROW LAYOUT */

.vip-row{
display:flex;
align-items:center;
gap:15px;
}


/* ICON */

.vip-left{
width:70px;
display:flex;
align-items:center;
justify-content:center;
}

.vip-left img{
width:50px;
}


/* INNER DETAILS CONTAINER */

.vip-details{
background:rgba(0,0,0,0.25);
padding:12px 16px;
border-radius:10px;
flex:1;
display:grid;
grid-template-columns:1fr auto;
row-gap:8px;
}


/* LABEL COLUMN */

.vip-details .label{
opacity:.85;
font-size:13px;
}


/* VALUE COLUMN */

.vip-details .value{
text-align:right;
font-weight:600;
font-size:13px;
}


/* GREEN TEXT */

.green{
color:#00e676;
}


/* USDT COLOR */

.usdt{
color:#a0a0a0;
margin-left:3px;
}


/* ================= BUTTON ================= */

.vip-action{
margin-top:12px;
display:flex;
justify-content:flex-end;
}

.vip-action button{
background:linear-gradient(90deg,#f6c27a,#e7a850);
border:none;
padding:8px 18px;
border-radius:20px;
font-size:12px;
cursor:pointer;
color:#fff;
position:relative;
overflow:hidden;
}


/* ACTIVATED */

.vip-active{
background:#777 !important;
cursor:default;
}


/* ================= SHINE EFFECT ================= */

.vip-action button::before{
content:'';
position:absolute;
top:0;
left:-120%;
width:60%;
height:100%;
background:linear-gradient(
120deg,
transparent,
rgba(255,255,255,0.6),
transparent
);
transform:skewX(-25deg);
animation:glassshine 3s infinite;
}

@keyframes glassshine{

0%{ left:-120%; }

35%{ left:150%; }

100%{ left:150%; }

}


/* ================= POPUP ================= */

.vip-popup{
position:fixed;
top:0;
left:0;
width:100%;
height:100%;
background:rgba(0,0,0,.65);
display:none;
align-items:center;
justify-content:center;
z-index:999;
}

.popup-box{
background:#222;
padding:25px;
border-radius:12px;
text-align:center;
width:260px;
color:#fff;
}

.popup-box p{
margin-bottom:20px;
font-size:15px;
}

.confirm-btn{
background:#f0b24b;
border:none;
padding:10px 20px;
border-radius:20px;
margin-right:10px;
cursor:pointer;
color:#fff;
}

.cancel-btn{
background:#444;
border:none;
padding:10px 20px;
border-radius:20px;
color:white;
cursor:pointer;
}


/* ================= MOBILE ================= */

@media (max-width:600px){

.vip-card{
padding:18px;
}

.vip-left img{
width:42px;
}

.vip-details{
font-size:12px;
}

.vip-action button{
font-size:11px;
padding:6px 12px;
}

}

/* ================= MINE PAGE ================= */

.mine-wrapper{
margin:20px;
background:linear-gradient(135deg,#3a2b20,#5a402e);
padding:20px;
border-radius:15px;
color:#fff;
}


/* ================= TOP SECTION ================= */

.mine-top{
display:flex;
align-items:center;
justify-content:space-between;
margin-bottom:18px;
}


/* USER INFO */

.mine-user{
flex:1;
min-width:0;
}

.mine-user h3{
margin:0;
font-size:15px;
font-weight:500;

/* prevent long emails from pushing GIF */

white-space:nowrap;
overflow:hidden;
text-overflow:ellipsis;
}


/* VIP BADGE */

.vip-badge{
background:#f0b24b;
color:#000;
padding:4px 10px;
border-radius:10px;
font-size:12px;
display:inline-block;
margin-top:6px;
}


/* ================= GIF ================= */

.mine-gif{
width:95px;
height:95px;
display:flex;
align-items:center;
justify-content:center;
margin-left:12px;
}

.mine-gif img{
width:95px;
height:95px;
object-fit:cover;
border-radius:12px;

/* glow effect */

box-shadow:0 0 20px rgba(0,255,170,0.35);
}


/* ================= BALANCE PANEL ================= */

.mine-balance{
background:linear-gradient(90deg,#e6cf9b,#e7b55f);
padding:20px;
border-radius:12px;
display:flex;
justify-content:space-between;
align-items:center;
text-align:center;
color:#000;
}


/* BALANCE ITEMS */

.balance-item{
flex:1;
}

.balance-item p{
margin:0;
font-size:12px;
}

.balance-item h2{
margin-top:6px;
font-size:20px;
font-weight:600;
}


/* ================= MENU ================= */

.mine-menu{
margin:20px;
background:#111;
border-radius:12px;
overflow:hidden;
}


/* MENU ITEM */

.menu-item{
display:flex;
align-items:center;
justify-content:space-between;
padding:16px;
color:#fff;
text-decoration:none;
border-bottom:1px solid #333;
transition:background .2s ease;
}

.menu-item:last-child{
border-bottom:none;
}


/* ICON */

.menu-item i:first-child{
width:28px;
text-align:center;
}


/* TEXT */

.menu-item span{
flex:1;
margin-left:12px;
font-size:14px;
}


/* HOVER EFFECT */

.menu-item:hover{
background:#1b1b1b;
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
