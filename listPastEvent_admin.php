<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <link rel="stylesheet" href="styles/auth.css">
    <link rel="stylesheet" href="styles/lk.css">
    <!-- <link rel="stylesheet" href="styles/events.css"> -->
    <link rel="stylesheet" href="styles/media/media_auth.css">
    <link rel="stylesheet" href="styles/media/media_lk.css">
    <title>Личный кабинет</title>
</head>

<body>
    <?php
    session_start();
    require './php/header.php';
    require './php/conn.php';

    $limit = 1; // Количество мероприятий на страницу
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $offset = ($page - 1) * $limit;

    // Запрос на получение прошедших мероприятий с пагинацией
    $userId = $_SESSION['user_id'];
    $getEventUser = "SELECT * FROM organizators o JOIN organizators_events oe ON o.organizator_id = oe.organizator_id JOIN events e ON oe.event_id = e.event_id WHERE e.event_date < CURRENT_DATE LIMIT $limit OFFSET $offset;";

    $resultGetEventUser = pg_query($conn, $getEventUser);

    // Запрос для подсчёта всех записей (без лимита и оффсета)
    $countQuery = "SELECT COUNT(*) FROM organizators o JOIN organizators_events oe ON o.organizator_id = oe.organizator_id JOIN events e ON oe.event_id = e.event_id WHERE e.event_date < CURRENT_DATE;";
    $countResult = pg_query($conn, $countQuery);
    $totalRows = pg_fetch_result($countResult, 0, 0);
    $totalPages = ceil($totalRows / $limit);
    ?>

    <div class="container">
        <div class="lk">
            <?php require 'php/lk/lk_menu.php'; ?>
            <div class="lk__profile">
                <div class="title1">Прошедшие мероприятия</div>
                <div class="cards">
                    <?php
                    if ($resultGetEventUser) {
                        while ($row = pg_fetch_assoc($resultGetEventUser)) {
                            require './php/card_report.php';
                        }
                    } else {
                        echo "Ошибка при получении данных: " . pg_last_error();
                    }
                    ?>
                </div>
                <!-- /cards -->

                <!-- Пагинация -->
                <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">Назад</a>
                        <?php endif; ?>

                        <span>Страница <?= $page ?> из <?= $totalPages ?></span>

                        <?php if ($page < $totalPages): ?>
                            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">Вперед</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
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
