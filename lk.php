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

    if (!isset($_SESSION['user_id'])) {
        header('Location: auth.php');
        exit();
    }
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