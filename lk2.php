<?php
require 'php-jwt-main/src/JWT.php'; // Подключаем библиотеку JWT
require 'php-jwt-main/src/JWK.php'; // Подключаем библиотеку JWT
require 'php-jwt-main/src/Key.php'; // Подключаем библиотеку JWT

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$secret_key = "1a2s3d4f5g"; // Тот же ключ, что и при генерации

// header('Content-Type: application/json');
header('Content-Type: text/html; charset=UTF-8');

if (!isset($_COOKIE['token'])) {
    echo json_encode(['success' => false, 'message' => 'Нет токена']);
    header("Location: auth.php");
    exit();
}

try {
    $jwt = $_COOKIE['token'];
    $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));

    $userId = $decoded->user_id;
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Ошибка валидации токена']);
    header("Location: auth.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/lk.css">
    <link rel="stylesheet" href="styles/auth.css">
    <link rel="stylesheet" href="styles/media/media_auth.css">
    <link rel="stylesheet" href="styles/media/media_lk.css">
    <title>Личный кабинет</title>
    <script src="scripts/setting.js" defer></script>
</head>

<body>


    <?php
    session_start();
    require './php/header.php';
    require './php/conn.php';
    // if (!isset($_SESSION['user_id'])) {
    //     header('Location: auth.php');
    //     exit();
    // }
    ?>

    <div class="container">
        <div class="lk">
            <?php require 'php/lk/lk_menu.php'; ?>
            <div class="lk__profile lk__right">
                <?php require 'php/lk/lk_profile.php'; ?>
            </div>
            <!-- /lk__profile -->
            <?php require 'php/lk/lk_setting.php'; ?>
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
    <script src="scripts/lk_script.js"></script>
    <script>
        // отображение/удаление фото профиля
        var dt = new DataTransfer();

        $('.input-file input[type=file]').on('change', function() {
            let $files_list = $(this).closest('.input-file').next();
            $files_list.empty();
            $('#removeImageField').val('0');

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
                };
            }
            this.files = dt.files;
        });

        function removeFilesItem(target) {
            let input = $(target).closest('.input-file-row').find('input[type=file]');
            $(target).closest('.input-file-list-item').remove();
            dt.items.clear();
            input[0].files = dt.files;

            $('#removeImageField').val('1');
        }
    </script>
</body>

</html>