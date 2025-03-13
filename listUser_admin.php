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
    ?>

    <div class="container">
        <div class="lk">
            <?php require 'php/lk/lk_menu.php'; ?>
            <div class="lk__profile">
                <div class="title1">Список пользователей</div>
                <div class="cards">
                    <?php
                    // Функция для получения статистики по мероприятиям
                    function fetchEventStatistics($conn, $userId)
                    {
                        $upcomingEventsQuery = "SELECT count(*) FROM events e
                                    JOIN user_events ue ON ue.event_id = e.event_id
                                    WHERE ue.user_id = $1 AND e.event_date > CURRENT_DATE";
                        $pastEventsQuery = "SELECT count(*) FROM events e
                                JOIN user_events ue ON ue.event_id = e.event_id
                                WHERE ue.user_id = $1 AND e.event_date < CURRENT_DATE";
                        $popularTopicsQuery = "SELECT e.topic, COUNT(*) AS topic_count
                                   FROM user_events ue
                                   JOIN events e ON ue.event_id = e.event_id
                                   WHERE ue.user_id = $1
                                   GROUP BY e.topic
                                   ORDER BY topic_count DESC
                                   LIMIT 3";

                        // Выполнение запросов с защитой от SQL инъекций
                        $upcomingEventCount = pg_fetch_result(pg_query_params($conn, $upcomingEventsQuery, array($userId)), 0, 0);
                        $pastEventCount = pg_fetch_result(pg_query_params($conn, $pastEventsQuery, array($userId)), 0, 0);

                        // Обработка возможных ошибок (если запросы не возвращают результаты)
                        return [
                            'upcomingEventCount' => $upcomingEventCount !== false ? $upcomingEventCount : 0,
                            'pastEventCount' => $pastEventCount !== false ? $pastEventCount : 0,
                            'popularTopics' => pg_fetch_all(pg_query_params($conn, $popularTopicsQuery, array($userId))) ?: []
                        ];
                    }

                    $userId = $_SESSION['user_id'];
                    $getUsers = "SELECT * FROM users;";

                    $resultGetUsers = pg_query($conn, $getUsers);

                    if ($resultGetUsers) {
                        while ($row = pg_fetch_assoc($resultGetUsers)) {
                            require './php/card_user.php';
                        }
                    } else {
                        echo "Ошибка при получении данных: " . pg_last_error();
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