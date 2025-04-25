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

    // Проверка, является ли пользователь организатором
    $query = "SELECT 1 FROM organizators WHERE organizator_id = $1";
    $result = pg_query_params($conn, $query, [$userId]);

    if (!$result || pg_num_rows($result) == 0) {
        // Если пользователь не организатор, перенаправляем в личный кабинет
        header("Location: lk.php");
        exit();
    }

    // Настройки пагинации
    $limit = 6; // Количество мероприятий на страницу
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $offset = ($page - 1) * $limit;

    // Запрос на получение текущих мероприятий с пагинацией
    $userId = $_SESSION['user_id'];
    $getEvents = "SELECT * FROM organizators o
                 JOIN organizators_events oe ON o.organizator_id = oe.organizator_id
                 JOIN events e ON oe.event_id = e.event_id
                 WHERE o.organizator_id = $1 AND e.event_date = CURRENT_DATE AND is_active = true
                 LIMIT $limit OFFSET $offset;";

    $resultGetEvents = pg_query_params($conn, $getEvents, [$userId]);

    // Запрос для подсчёта всех записей (без лимита и оффсета)
    $countQuery = "SELECT COUNT(*) FROM organizators o
               JOIN organizators_events oe ON o.organizator_id = oe.organizator_id
               JOIN events e ON oe.event_id = e.event_id
               WHERE o.organizator_id = $1 AND e.event_date = CURRENT_DATE AND is_active = true;";
    $countResult = pg_query_params($conn, $countQuery, [$userId]);
    $totalRows = pg_fetch_result($countResult, 0, 0);
    $totalPages = ceil($totalRows / $limit);
    ?>

    <div class="container">
        <div class="lk">
            <?php require 'php/lk/lk_menu.php'; ?>
            <div class="lk__profile">
                <div class="title1">Список мероприятий сегодня</div>
                <div class="cards">
                    <?php
                    if ($resultGetEvents && pg_num_rows($resultGetEvents) > 0) {
                        while ($row = pg_fetch_assoc($resultGetEvents)) {
                            require './php/card_scan.php';
                        }
                    } else {
                        echo "<p>Нет текущих мероприятий для отображения.</p>";
                    }
                    ?>
                </div>

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
        </div>
        <?php pg_close($conn); ?>
    </div>

    <?php require './php/footer.php'; ?>
</body>

</html>