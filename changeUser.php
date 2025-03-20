<?php
session_start();
require 'php/conn.php';
// Проверка, авторизован ли пользователь
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit();
}

$userIdSession = $_SESSION['user_id'];

// Проверка, является ли пользователь администратором
$queryAdmin = "SELECT 1 FROM users WHERE user_id = $1 AND is_admin = true";
$resultAdmin = pg_query_params($conn, $queryAdmin, [$userIdSession]);

if (!$resultAdmin || pg_num_rows($resultAdmin) == 0) {
    // Если пользователь не является администратором, перенаправляем на страницу личного кабинета
    header("Location: lk.php");
    exit();
}
if (!isset($_GET['user_id'])) {
    die("Ошибка: пользователь не найден.");
}

$usertId = intval($_GET['user_id']);

// Получаем данные мероприятия
$query = "SELECT * FROM users WHERE user_id = $1";
$result = pg_query_params($conn, $query, [$usertId]);
$user = pg_fetch_assoc($result);

if (!$user) {
    die("Ошибка: пользователь не найден.");
}

// Определяем изображение
$imageSrc = !empty($user["image"])
    ? "data:image/jpeg;base64," . base64_encode(pg_unescape_bytea($user["image"]))
    : "img/profile.jpg";

$dateFormatted = date("d/m/Y", strtotime($user['birth_date']));

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <link rel="stylesheet" href="styles/lk.css">
    <link rel="stylesheet" href="styles/forma.css">
    <link rel="stylesheet" href="styles/auth.css">
    <link rel="stylesheet" href="styles/media/media_lk.css">
    <title>Личный кабинет</title>
    <style>
        .error-border {
            border: 3px solid rgb(202, 32, 17);
        }

        form button {
            margin: 0px 0px;
        }
    </style>
</head>

<body>
    <?php
    require './php/header.php';
    require './php/conn.php';
    // Получаем данные мероприятия
    $query = "SELECT * FROM users WHERE user_id = $1";
    $result = pg_query_params($conn, $query, [$usertId]);
    $user = pg_fetch_assoc($result);
    ?>
    <div class="container">
        <div class="lk">
            <?php require 'php/lk/lk_menu.php'; ?>
            <div class="lk__profile">
                <div class="title1">Редактирование профиля пользователя</div>
                <form id="editUserForm" enctype="multipart/form-data">
                    <input type="hidden" name="user_id" value="<?= $usertId ?>">
                    <div class="input-file-row">
                        <label class="input-file">
                            <input type="file" name="file" multiple accept="image/*" id="profilePictureInput">
                            <span class="title2">Выберите фото для профиля</span>
                        </label>
                        <div class="input-file-list">
                            <div class="input-file-list-item">
                                <img class="input-file-list-img" src="<?= $imageSrc ?>" alt="user_image">
                                <a href="#" onclick="removeFilesItem(this); return false;" class="input-file-list-remove">x</a>
                            </div>
                        </div>
                    </div>
                    <div class="form__group">
                        <input id="last_name" name="last_name" class="input title2" type="text" value="<?php echo $user['last_name']; ?>" placeholder=" " required>
                        <label class="label title2" for="">Фамилия</label>
                    </div>
                    <div class="form__group">
                        <input id="first_name" name="first_name" class="input title2" type="text" value="<?= htmlspecialchars($user['first_name']) ?>" placeholder=" " required>
                        <label class="label title2" for="">Имя</label>
                    </div>
                    <div class="gender title2">
                        <div class="custom">
                            <input type="radio" id="male" name="gender" value="мужской" <?php echo ($user['gender'] == 'мужской') ? 'checked' : ''; ?> required>
                            <label for="male">Мужчина</label>
                        </div>
                        <div class="custom">
                            <input type="radio" id="female" name="gender" value="женский" <?php echo ($user['gender'] == 'женский') ? 'checked' : ''; ?> required>
                            <label for="female">Женщина</label>
                        </div>
                    </div>
                    <div class="form__group">
                        <input id="birth_date" name="birth_date" class="input title2" type="text" value="<?= $dateFormatted ?>" placeholder="ЧЧ/ММ/ГГ" required>
                        <label class="label title2" for="">Дата рождения</label>
                    </div>
                    <div class="text-field__icon text-field__icon_password password">
                        <input id="old_password" name="old_password" class="text-field__input title2" type="password" placeholder="Старый пароль">
                        <a href="#" class="password-control" onclick="return show_hide_password(this, 'old_password');"></a>
                    </div>
                    <div class="text-field__icon text-field__icon_password password">
                        <input id="new_password" name="new_password" class="text-field__input title2" type="password" placeholder="Новый пароль">
                        <a href="#" class="password-control" onclick="return show_hide_password(this, 'new_password');"></a>
                    </div>
                    <div class="text-field__icon text-field__icon_password password">
                        <input id="repeat_password" name="repeat_password" class="text-field__input title2" type="password" placeholder="Повторите новый пароль">
                        <a href="#" class="password-control" onclick="return show_hide_password(this, 'repeat_password');"></a>
                    </div>
                    <div id="error" class="error title2" style="display: none;">Ошибка!</div>
                    <input type="hidden" name="remove_image" id="removeImageField" value="0">
                    <div class="btns__lk">
                        <button class="btn1 title2" type="button" onclick="window.history.back();">Отмена</button>
                        <button class="btn1 title2" id="saveProfile" type="submit">Сохранить изменения</button>
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
    <script src="./scripts/change_user.js"></script>
</body>

</html>