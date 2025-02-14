<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <link rel="stylesheet" href="styles/auth.css">
    <link rel="stylesheet" href="styles/lk.css">
    <link rel="stylesheet" href="styles/forma.css">
    <link rel="stylesheet" href="styles/media/media_auth.css">
    <link rel="stylesheet" href="styles/media/media_lk.css">
    <title>Личный кабинет</title>
</head>

<body>
    <?php
    session_start();
    require './php/header.php';
    require './php/conn.php';
    ?>

    <div class="container">
        <div class="lk">
            <?php require 'php/lk/lk_menu.php'; ?>
            <div class="lk__profile">
                <div class="title1">Формирование заявки</div>
                <form id="requestForm">
                    <div class="text-field__icon">
                        <input id="name_organizer" name="name_organizer" class="text-field__input title2" type="text" placeholder="Название оргинизации" required>
                    </div>
                    <div class="text-field__icon">
                        <input id="phone" name="phone" class="text-field__input title2" type="text" placeholder="Номер телефона" required>
                    </div>
                    <div class="text-field__icon">
                        <input id="email" name="email" class="text-field__input title2" type="email" placeholder="Эл.почта" required>
                    </div>
                    <div class="text-field__icon">
                        <input id="date_start_work" name="date_start_work" class="text-field__input title2" type="text" placeholder="Дата начала деятельности" required>
                    </div>
                    <div class="form__group">
                        <textarea class="input textarea title2" id="description" name="description" rows="4" placeholder="Описание деятельности организации"></textarea>
                    </div>
                    <div id="error" class="error title2" style="display: none;">Пользователь с такой почтой уже зарегистрирован!</div>
                    <button class="btn1 title2" type="submit">Отправить заявку</button>
                </form>
            </div>
            <!-- /lk__profile -->
        </div>
        <!-- /lk -->
        <?php
        pg_close($conn);
        ?>
    </div>
    <!-- /container -->


    <?php
    require './php/footer.php';
    ?>

    <script src="./scripts/request.js"></script>
</body>

</html>