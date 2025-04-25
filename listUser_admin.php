<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <!-- <link rel="stylesheet" href="styles/auth.css"> -->
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

    $limit = 6; // Количество пользователей на страницу
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $offset = ($page - 1) * $limit;

    $whereClauses = [];
    $params = [];

    if (!empty($_GET['user_id'])) {
        $whereClauses[] = "user_id = $" . (count($params) + 1);
        $params[] = intval($_GET['user_id']); // Приводим к числу для безопасности
    }
    if (!empty($_GET['last_name'])) {
        $whereClauses[] = "last_name ILIKE $" . (count($params) + 1);
        $params[] = '%' . $_GET['last_name'] . '%';
    }
    if (!empty($_GET['first_name'])) {
        $whereClauses[] = "first_name ILIKE $" . (count($params) + 1);
        $params[] = '%' . $_GET['first_name'] . '%';
    }
    if (!empty($_GET['email'])) {
        $whereClauses[] = "email ILIKE $" . (count($params) + 1);
        $params[] = '%' . $_GET['email'] . '%';
    }

    $whereClause = count($whereClauses) > 0 ? " WHERE " . implode(" AND ", $whereClauses) : "";

    // Запрос на получение пользователей с учетом фильтрации
    $getUsers = "SELECT * FROM users $whereClause ORDER BY user_id LIMIT $limit OFFSET $offset";
    $resultGetUsers = pg_query_params($conn, $getUsers, $params);

    // Запрос для подсчета всех записей (без лимита и оффсета)
    $countQuery = "SELECT COUNT(*) FROM users $whereClause";
    $countResult = pg_query_params($conn, $countQuery, $params);
    $totalRows = pg_fetch_result($countResult, 0, 0);
    $totalPages = ($totalRows > 0) ? ceil($totalRows / $limit) : 1;

    ?>

    <div class="container">
        <div class="lk">
            <?php require 'php/lk/lk_menu.php'; ?>
            <div class="lk__profile">
                <div class="title1">Список пользователей</div>
                <div class="search-form">
                    <form id="userSearchForm" method="GET" action="">
                        <input type="text" id="user_id" name="user_id" placeholder="ID" autocomplete="off"
                            value="<?= htmlspecialchars($_GET['user_id'] ?? '') ?>">
                        <input type="text" name="last_name" id="last_name" placeholder="Фамилия" autocomplete="off" value="<?= htmlspecialchars($_GET['last_name'] ?? '') ?>">
                        <input type="text" name="first_name" id="first_name" placeholder="Имя" autocomplete="off" value="<?= htmlspecialchars($_GET['first_name'] ?? '') ?>">
                        <input type="text" id="email" name="email" placeholder="Почта" autocomplete="off"
                            value="<?= htmlspecialchars($_GET['email'] ?? '') ?>">
                        <button type="submit"><img src="./img/icons/search.svg" alt="Найти"></button>
                        <button type="button" id="resetButton"><img src="./img/icons/close.svg" alt="Сбросить"></button>
                    </form>
                </div>
                <div class="cards">
                    <?php
                    // Функция для получения статистики по мероприятиям
                    function fetchEventStatistics($conn, $userId)
                    {
                        $upcomingEventsQuery = "SELECT count(*) FROM events e
                                    JOIN user_events ue ON ue.event_id = e.event_id
                                    WHERE ue.user_id = $1 AND e.event_date >= CURRENT_DATE and e.is_active = true and e.is_approved = true and ue.is_signed = true";
                        $pastEventsQuery = "SELECT count(*) FROM events e
                                JOIN user_events ue ON ue.event_id = e.event_id
                                WHERE ue.user_id = $1 AND e.event_date < CURRENT_DATE and e.is_active = true and e.is_approved = true and ue.is_signed = true";

                        // Выполнение запросов с защитой от SQL инъекций
                        $upcomingEventCount = pg_fetch_result(pg_query_params($conn, $upcomingEventsQuery, array($userId)), 0, 0);
                        $pastEventCount = pg_fetch_result(pg_query_params($conn, $pastEventsQuery, array($userId)), 0, 0);

                        // Обработка возможных ошибок (если запросы не возвращают результаты)
                        return [
                            'upcomingEventCount' => $upcomingEventCount !== false ? $upcomingEventCount : 0,
                            'pastEventCount' => $pastEventCount !== false ? $pastEventCount : 0
                        ];
                    }

                    // $userId = $_SESSION['user_id'];

                    if ($resultGetUsers && pg_num_rows($resultGetUsers) > 0) {
                        while ($row = pg_fetch_assoc($resultGetUsers)) {
                            require './php/card_user.php';
                        }
                    } else {
                        echo "<p>Пользователей не найдено</p>";
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
            window.location.href = 'listUser_admin.php';
        });
    </script>
</body>

</html>