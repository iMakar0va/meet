<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <link rel="stylesheet" href="styles/lk.css">
    <link rel="stylesheet" href="styles/search_form.css">
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

    $limit = 4; // Количество мероприятий на страницу
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $offset = ($page - 1) * $limit;
    // Фильтрация
    $whereClauses = [];
    $params = [];

    if (!empty($_GET['title'])) {
        $whereClauses[] = "title ILIKE '%' || $" . (count($params) + 1) . " || '%'";
        $params[] = $_GET['title'];
    }

    if (!empty($_GET['event_date'])) {
        $whereClauses[] = "event_date = $" . (count($params) + 1);
        $params[] = $_GET['event_date'];
    }
    $whereClause = count($whereClauses) > 0 ? " AND " . implode(" AND ", $whereClauses) : "";

    // Запрос на получение мероприятий с пагинацией
    $getEventUser = "SELECT * FROM organizators o JOIN organizators_events oe ON o.organizator_id = oe.organizator_id JOIN events e ON oe.event_id = e.event_id WHERE e.event_date < CURRENT_DATE and e.is_approved = true and e.is_active = true $whereClause LIMIT $limit OFFSET $offset;";
    $resultGetEventUser = pg_query_params($conn, $getEventUser, $params);;

    // Запрос для подсчёта всех записей (без лимита и оффсета)
    $countQuery = "SELECT COUNT(*) FROM organizators o JOIN organizators_events oe ON o.organizator_id = oe.organizator_id JOIN events e ON oe.event_id = e.event_id WHERE e.event_date < CURRENT_DATE and e.is_approved = true and e.is_active = true $whereClause;";
    $countResult = pg_query_params($conn, $countQuery, $params);
    $totalRows = pg_fetch_result($countResult, 0, 0);
    $totalPages = ceil($totalRows / $limit);
    ?>

    <div class="container">
        <div class="lk">
            <?php require 'php/lk/lk_menu.php'; ?>
            <div class="lk__profile">
                <div class="title1">Прошедшие мероприятия</div>
                <div class="search-form">
                    <form id="eventSearchForm" method="GET" action="">
                        <input type="text" id="title" name="title" placeholder="Название мероприятия" autocomplete="off"
                            value="<?= htmlspecialchars($_GET['title'] ?? '') ?>">
                        <input type="date" id="event_date" name="event_date"
                            value="<?= htmlspecialchars($_GET['event_date'] ?? '') ?>">
                        <button type="submit"><img src="./img/icons/search.svg" alt="Найти"></button>
                        <button type="button" id="resetButton"><img src="./img/icons/close.svg" alt="Сбросить"></button>
                    </form>
                </div>
                <div class="cards">
                    <?php
                    if ($resultGetEventUser && pg_num_rows($resultGetEventUser) > 0) {
                        while ($row = pg_fetch_assoc($resultGetEventUser)) {
                            require './php/card_report.php';
                        }
                    } else {
                        echo "<p>Мероприятий не найдено</p>";
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
    <script>
        document.getElementById('resetButton').addEventListener('click', function() {
            window.location.href = 'listPastEvent_admin.php';
        });
    </script>
</body>

</html>