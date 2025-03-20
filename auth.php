<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/auth.css">
    <link rel="stylesheet" href="styles/media/media_auth.css">
    <title>Авторизация</title>
</head>

<body>
    <?php
    require './php/header.php';
    session_start();

    if (isset($_SESSION['user_id'])) {
        header('Location: lk.php');
        exit();
    }

    ?>

    <div class="container">
        <div class="form">
            <div class="form-title title0">Авторизация</div>
            <form id="authForm">
                <div class="text-field__icon text-field__icon_email">
                    <input id="email" name="email" class="text-field__input title2" type="email" placeholder="Почта" required>
                </div>
                <div class="text-field__icon text-field__icon_password password">
                    <input id="password" name="password" class="text-field__input title2" type="password" placeholder="Пароль" required>
                    <a href="#" class="password-control" onclick="return show_hide_password(this);"></a>
                </div>
                <a href="#!" id="resetPasswordLink" class="title3">Восстановить пароль</a>
                <div id="error" class="error title2" style="display:none;"></div>
                <button class="btn1 title2">Войти</button>
                <a href="./leaderid_login.php" style="text-align: center;">
                    <img src="./img/leader_id.png" alt="Войти через Leader-ID" style="width: 140px; padding: 13px 0px; background: white; border-radius: 10px;">
                </a>
                <a href="./reg.php" class="title3" id="link-reg">Зарегистрироваться</a>
            </form>
        </div>
        <!-- /form -->
    </div>

    </div>
    <!-- /container -->

    <?php
    require './php/footer.php';
    ?>
    <script src="./scripts/auth.js"></script>
</body>

</html>