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
    $limit = 4; // Количество мероприятий на страницу
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $offset = ($page - 1) * $limit;

    // Запрос на получение текущих мероприятий с пагинацией
    $userId = $_SESSION['user_id'];
    $getEventUser = "SELECT * FROM organizators o
                     JOIN organizators_events oe ON o.organizator_id = oe.organizator_id
                     JOIN events e ON oe.event_id = e.event_id
                     WHERE o.organizator_id = $1 AND e.event_date > CURRENT_DATE AND is_active = true
                     LIMIT $limit OFFSET $offset;";

    $resultGetEventUser = pg_query_params($conn, $getEventUser, [$userId]);

    // Запрос для подсчёта всех записей (без лимита и оффсета)
    $countQuery = "SELECT COUNT(*) FROM organizators o
                   JOIN organizators_events oe ON o.organizator_id = oe.organizator_id
                   JOIN events e ON oe.event_id = e.event_id
                   WHERE o.organizator_id = $1 AND e.event_date > CURRENT_DATE AND is_active = true;";
    $countResult = pg_query_params($conn, $countQuery, [$userId]);
    $totalRows = pg_fetch_result($countResult, 0, 0);
    $totalPages = ceil($totalRows / $limit);
    ?>

    <div class="container">
        <div class="lk">
            <?php require 'php/lk/lk_menu.php'; ?>
            <div class="lk__profile">
                <div class="title1">Текущие мероприятия</div>
                <div class="links">
                    <a href="./listEventActive_admin.php" class="active">Активные мероприятия</a>
                    <a href="./listEventCancelled_admin.php" class="no_active">Отмененные мероприятия</a>
                </div>
                <div class="cards">
                    <?php
                    if ($resultGetEventUser && pg_num_rows($resultGetEventUser) > 0) {
                        while ($row = pg_fetch_assoc($resultGetEventUser)) {
                            require './php/card_change.php';
                        }
                    } else {
                        echo "<p>Нет текущих мероприятий для отображения.</p>";
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