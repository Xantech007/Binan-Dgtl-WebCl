<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<div class="footer-wrapper">
    <div class="footer">

        <a href="index.php" class="<?php if($currentPage=='index.php') echo 'active'; ?>">
            <i class="fa-solid fa-house"></i>
            <span>Home</span>
        </a>

        <a href="mission.php" class="<?php if($currentPage=='mission.php') echo 'active'; ?>">
            <i class="fa-solid fa-list-check"></i>
            <span>Task</span>
        </a>

        <a href="team.php" class="<?php if($currentPage=='team.php') echo 'active'; ?>">
            <i class="fa-solid fa-people-group"></i>
            <span>Team</span>
        </a>

        <a href="vip.php" class="<?php if($currentPage=='vip.php') echo 'active'; ?>">
            <i class="fa-solid fa-crown"></i>
            <span>VIP</span>
        </a>

        <a href="mine.php" class="<?php if($currentPage=='mine.php') echo 'active'; ?>">
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
