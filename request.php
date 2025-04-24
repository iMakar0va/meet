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
    require './php/conn.php';
    ?>

    <div class="container">
        <div class="lk">
            <?php require 'php/lk/lk_menu.php'; ?>
            <div class="lk__profile">
                <div class="title1">Хочешь стать организатором?</div>
                <div class="title2" style="color: var(--black-color);">
                    Хочешь не просто участвовать, а создавать собственные мероприятия? Воплоти свои идеи в жизнь — стань организатором и собери единомышленников!
                </div>
                <div class="title1">Формирование заявки</div>
                <form id="requestForm">
                    <div class="form__group">
                        <input id="name_organizer" name="name_organizer" class="input title2" type="text" placeholder=" " required>
                        <label class="label title2" for="">Название организации*</label>
                    </div>
                    <div class="form__group">
                        <input id="phone" name="phone" class="input title2" type="text" placeholder=" " required>
                        <label class="label title2" for="">Номер телефона*</label>
                    </div>
                    <div class="form__group">
                        <input id="date_start_work" name="date_start_work" class="input title2" type="text" placeholder=" " required>
                        <label class="label title2" for="">Дата начала деятельности*</label>
                    </div>
                    <div class="form__group">
                        <textarea class="input textarea title2" id="description" name="description" rows="4" placeholder="Описание деятельности организации*" required></textarea>
                    </div>
                    <div class="" style="color: black;">*-поля для обязательного заполения</div>
                    <div id="error" class="error title2" style="display: none;"></div>
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