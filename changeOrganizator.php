<?php
session_start();
require 'php/conn.php';

if (!isset($_GET['organizator_id'])) {
    die("Ошибка: организатор не найден.");
}

$organizatorId = intval($_GET['organizator_id']);
$_SESSION['organizator_id'] = $organizatorId;

// Получаем данные мероприятия
$query = "SELECT * FROM organizators WHERE organizator_id = $1";
$result = pg_query_params($conn, $query, [$organizatorId]);
$organizator = pg_fetch_assoc($result);

if (!$organizator) {
    die("Ошибка: мероприятие не найдено.");
}

$dateFormatted = date("d/m/Y", strtotime($organizator['date_start_work']));
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <link rel="stylesheet" href="styles/lk.css">
    <link rel="stylesheet" href="styles/forma.css">
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
    require './php/header.php';
    require './php/conn.php';
    // Проверка, авторизован ли пользователь
    if (!isset($_SESSION['user_id'])) {
        header("Location: auth.php");
        exit();
    }

    $userId = $_SESSION['user_id'];

    // Проверка, является ли пользователь администратором
    $queryAdmin = "SELECT 1 FROM users WHERE user_id = $1 AND is_admin = true";
    $resultAdmin = pg_query_params($conn, $queryAdmin, [$userId]);

    if (!$resultAdmin || pg_num_rows($resultAdmin) == 0) {
        // Если пользователь не является администратором, перенаправляем на страницу личного кабинета
        header("Location: lk.php");
        exit();
    }

    $organizatorId = intval($_GET['organizator_id']);
    $_SESSION['organizator_id'] = $organizatorId;
    ?>
    <div class="container">
        <div class="lk">
            <?php require 'php/lk/lk_menu.php'; ?>
            <div class="lk__profile">
                <div class="title1">Редактирование профиля организатора</div>
                <form id="changeRequestForm">
                    <input type="hidden" name="organizator_id" value="<?= $organizatorId ?>">
                    <div class="form__group">
                        <input id="name" name="name" class="input title2" type="text" value="<?= htmlspecialchars($organizator['name']) ?>" placeholder=" " required>
                        <label class="label title2" for="">Название организации</label>
                    </div>
                    <div class="form__group">
                        <input id="phone_number" name="phone_number" class="input title2" type="text" value="<?= htmlspecialchars($organizator['phone_number']) ?>" placeholder=" " required>
                        <label class="label title2" for="">Номер телефона</label>
                    </div>
                    <div class="form__group">
                        <input id="date_start_work" name="date_start_work" class="input title2" type="text" value="<?= $dateFormatted ?>" placeholder=" " required>
                        <label class="label title2" for="">Дата начала деятельности</label>
                    </div>
                    <div class="form__group">
                        <textarea class="input textarea title2" id="description" name="description" rows="4" placeholder="Описание деятельности организации"><?= htmlspecialchars($organizator['description']) ?></textarea>
                    </div>
                    <div id="error" class="error title2" style="display: none;"></div>
                    <div class="btns__lk">
                        <button class="btn1 title2" type="button" onclick="window.history.back();">Отмена</button>
                        <button class="btn1 title2" type="submit">Сохранить изменения</button>
                    </div>
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="./scripts/change_organizator.js"></script>
</body>

</html>