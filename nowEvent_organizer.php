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
                <div class="title1">Текущие мероприятия</div>
                <div class="cards">
                    <?php
                    $userId = $_SESSION['user_id'];
                    $getEventUser = "SELECT * FROM organizators o
                                     JOIN organizators_events oe ON o.organizator_id = oe.organizator_id
                                     JOIN events e ON oe.event_id = e.event_id
                                     WHERE o.organizator_id = $1 and e.event_date > CURRENT_DATE;";

                    $resultGetEventUser = pg_query_params($conn, $getEventUser, [$userId]);

                    if ($resultGetEventUser && pg_num_rows($resultGetEventUser) > 0) {
                        while ($row = pg_fetch_assoc($resultGetEventUser)) {
                            require './php/card_change.php';
                        }
                    } else {
                        echo "<p>Нет текущих мероприятий для отображения.</p>";
                    } ?>
                </div>
                <!-- /cards -->
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
</body>

</html>