<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/auth.css">
    <link rel="stylesheet" href="styles/media/media_auth.css">
    <title>Регистрация</title>
    <style>
        .error-border {
            border: 3px solid rgb(202, 32, 17);
        }
    </style>
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
            <div class="form-title title0">Регистрация</div>
            <form id="regForm" enctype="multipart/form-data">
                <div class="text-field__icon text-field__icon_person">
                    <input id="last_name" name="last_name" class="text-field__input title2" type="text" placeholder="Фамилия*" required>
                </div>
                <div class="text-field__icon text-field__icon_person">
                    <input id="first_name" name="first_name" class="text-field__input title2" type="text" placeholder="Имя*" required>
                </div>
                <div class="gender title2">
                    <div class="custom">
                        <input type="radio" id="male" name="gender" value="мужской" required checked>
                        <label for="male">Мужчина</label>
                    </div>
                    <div class="custom">
                        <input type="radio" id="female" name="gender" value="женский" required>
                        <label for="female">Женщина</label>
                    </div>
                </div>
                <div class="text-field__icon text-field__icon_email">
                    <input id="email" name="email" class="text-field__input title2" type="email" placeholder="Почта*" required>
                </div>
                <div class="text-field__icon text-field__icon_password password">
                    <input id="password_reg" name="password" class="text-field__input title2" type="password" placeholder="Пароль*" required>
                    <a href="#" class="password-control" onclick="return show_hide_password(this, 'password_reg');"></a>
                </div>
                <div class="text-field__icon text-field__icon_password password">
                    <input id="repeat_password" name="repeat_password" class="text-field__input title2" type="password" placeholder="Повторите пароль*" required>
                    <a href="#" class="password-control" onclick="return show_hide_password(this, 'repeat_password');"></a>
                </div>
                <div class="text-field__icon text-field__icon_calendar">
                    <input
                        class="text-field__input title2"
                        type="text"
                        name="birth_date"
                        placeholder="ДД/ММ/ГГГГ*"
                        maxlength="10"
                        required
                        id="birthDateInput">
                </div>
                <!-- Чекбокс для согласия с пользовательским соглашением -->
                <div class="checkbox-container">
                    <input type="checkbox" id="terms1" name="terms1" required>
                    <label for="terms" class="terms-label">Я согласен на <a href="document/согласие на обработку персональных данных.docx" style="color: #FADD84;">Обработку персональных данных</a></label>
                </div>
                <div class="checkbox-container">
                    <input type="checkbox" id="terms2" name="terms2" required>
                    <label for="terms" class="terms-label">Я согласен с <a href="document/политика обработки данных.docx" style="color: #FADD84;">Политикой обрабоки персональных данных</a></label>
                </div>
                <div class="checkbox-container">
                    <input type="checkbox" id="terms3" name="terms3" required>
                    <label for="terms" class="terms-label">Я согласен с <a href="document/пользовательское соглашение.docx" style="color: #FADD84;">Пользовательским соглашением</a></label>
                </div>
                <div class="">*-поля для обязательного заполения</div>
                <div id="error" class="error title2" style="display: none;">Пользователь с такой почтой уже зарегистрирован!</div>
                <button class="btn1" type="submit">Зарегистрироваться</button>
                <a href="./leaderid_login.php" style="text-align: center;">
                    <img src="./img/leader_id.png" alt="Войти через Leader-ID" style="width: 140px; padding: 13px 0px; background: white; border-radius: 10px;">
                </a>
                <a href="./auth.php" class="title3" id="link-auth">Авторизоваться</a>
            </form>
        </div>
        <!-- /form -->
    </div>
    <!-- /container -->
    <?php
    require './php/footer.php';
    ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="./scripts/reg.js"></script>
</body>

</html>