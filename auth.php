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
                <div id="error" class="error title2" style="display:none;">Неверный пароль или логин</div>
                <button class="btn1 title2">Войти</button>
                <a href="./leaderid_login.php" class="btn1 title2">Войти через Leader-ID</a>
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
    <script>
        document.getElementById('resetPasswordLink').addEventListener('click', function(e) {
            e.preventDefault();
            window.location.href = './reset_password.php';
        });

        function show_hide_password(target) {
            var input = document.getElementById('password');
            if (input.getAttribute('type') == 'password') {
                target.classList.add('view');
                input.setAttribute('type', 'text');
            } else {
                target.classList.remove('view');
                input.setAttribute('type', 'password');
            }
            return false;
        }
    </script>
</body>

</html>