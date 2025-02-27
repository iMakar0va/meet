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
    session_start();
    require './php/header.php';
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
                <div class="input-file-row">
                    <label class="input-file">
                        <input type="file" name="file" multiple accept="image/*">
                        <span class="title2">Выберите фото для профиля</span>
                    </label>
                    <div class="input-file-list"></div>
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
                <div class="">*-поля для обязательного заполения</div>
                <div id="error" class="error title2" style="display: none;">Пользователь с такой почтой уже зарегистрирован!</div>
                <button class="btn1 title2" type="submit">Зарегистрироваться</button>
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
    <script>
        // Формат даты ДД/ММ/ГГ
        document.addEventListener('DOMContentLoaded', () => {
            const birthDateInput = document.getElementById('birthDateInput');

            birthDateInput.addEventListener('input', (e) => {
                let value = birthDateInput.value.replace(/[^0-9]/g, '');
                if (value.length > 2) value = value.slice(0, 2) + '/' + value.slice(2);
                if (value.length > 5) value = value.slice(0, 5) + '/' + value.slice(5);
                birthDateInput.value = value.slice(0, 10);
            });
        });

        // Сохранение фото пользователя
        var dt = new DataTransfer();

        $('.input-file input[type=file]').on('change', function() {
            let $files_list = $(this).closest('.input-file').next();
            $files_list.empty();

            for (var i = 0; i < this.files.length; i++) {
                let file = this.files.item(i);
                dt.items.add(file);

                let reader = new FileReader();
                reader.readAsDataURL(file);
                reader.onloadend = function() {
                    let new_file_input = '<div class="input-file-list-item">' +
                        '<img class="input-file-list-img" src="' + reader.result + '">' +
                        '<a href="#" onclick="removeFilesItem(this); return false;" class="input-file-list-remove">x</a>' +
                        '</div>';
                    $files_list.append(new_file_input);
                }
            };
            this.files = dt.files;
        });

        // Удаление фото
        function removeFilesItem(target) {
            let name = $(target).prev().text();
            let input = $(target).closest('.input-file-row').find('input[type=file]');
            $(target).closest('.input-file-list-item').remove();
            for (let i = 0; i < dt.items.length; i++) {
                if (name === dt.items[i].getAsFile().name) {
                    dt.items.remove(i);
                }
            }
            input[0].files = dt.files;
        }

        // Скрыть/показать пароль
        function show_hide_password(target, inputId) {
            var input = document.getElementById(inputId);
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
    <script src="./scripts/reg.js"></script>
</body>

</html>