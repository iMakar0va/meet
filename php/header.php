<header>
    <div class="container">
        <div class="header__wrapper nav" id="nav">
            <div class="logo">
                <a href="main.php"><img src="img/icons/logo.svg" alt="logo"></a>
            </div>
            <div class="menu title2">
                <a href="main.php">Главная</a>
                <a href="events.php">Афиша</a>
                <a href="#!">Курсы</a>
                <a href="organizators.php">Организаторы</a>
                <a href="php/auth_or_lk.php" id="lk">Личный кабинет</a>
            </div>
            <!-- /menu -->
            <button id="nav-btn" class="nav-button">
                <img id="nav-btn-img" src="img/icons/nav-open.svg" alt="open">
            </button>
            <div class="header__icons">
                <a href="php/auth_or_lk.php" id="lk_person"><img src="img/icons/person.svg" alt="person"></a>
                <?php
                // session_start();
                if (isset($_SESSION['user_id'])) { ?>
                    <a href="#!" onclick="logout()"><img src="img/icons/exit.svg" alt="exit"></a>
                <?php
                }
                ?>
            </div>
            <!-- header__icons -->
        </div>
        <!-- /header__wrapper -->
    </div>
    <!-- /container -->
</header>
<script src="./scripts/exit.js"></script>
<script>
    const nav = document.querySelector('#nav');
    const navBtn = document.querySelector('#nav-btn');
    const navBtnImg = document.querySelector('#nav-btn-img');

    navBtn.onclick = () => {
        if (nav.classList.toggle('open')) {
            navBtnImg.src = "img/icons/nav-close.svg";
        } else {
            navBtnImg.src = "img/icons/nav-open.svg";
        }
    }
</script>