<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <link rel="stylesheet" href="styles/auth.css">
    <link rel="stylesheet" href="styles/lk.css">
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
                <h1 class="title1">Список организаторов</h1>
                <div class="cards">
                    <?php
                    $getUsers = "SELECT * FROM organizators;";
                    $resultGetUsers = pg_query_params($conn, $getUsers, []);

                    if ($resultGetUsers) {
                        while ($row = pg_fetch_assoc($resultGetUsers)) {
                            require './php/card_organizator.php';
                        }
                    } else {
                        echo "Ошибка при получении данных: " . pg_last_error();
                    }
                    ?>
                </div>
            </div>
        </div>
        <?php
        pg_close($conn);
        ?>
    </div>

    <?php
    require './php/footer.php';
    ?>
</body>

</html>
