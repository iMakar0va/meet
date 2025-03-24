<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <!-- <link rel="stylesheet" href="styles/auth.css"> -->
    <link rel="stylesheet" href="styles/lk.css">
    <link rel="stylesheet" href="styles/events.css">
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

    $limit = 6; // Количество мероприятий на страницу
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $offset = ($page - 1) * $limit;

    // ..............
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
    // ..............

    // Запрос на получение мероприятий с пагинацией
    $getEvents = "SELECT * FROM events WHERE is_active = true and is_approved = true AND event_date >= CURRENT_DATE $whereClause ORDER BY event_date LIMIT $limit OFFSET $offset";
    $resultGetEvents = pg_query_params($conn, $getEvents, $params);;

    // Запрос для подсчёта всех записей (без лимита и оффсета)
    $countQuery = "SELECT COUNT(*) FROM events WHERE is_active = true and is_approved = true AND event_date >= CURRENT_DATE $whereClause";
    $countResult = pg_query_params($conn, $countQuery, $params);
    $totalRows = pg_fetch_result($countResult, 0, 0);
    $totalPages = ceil($totalRows / $limit);
    ?>

    <div class="container">
        <div class="lk">
            <?php require 'php/lk/lk_menu.php'; ?>
            <div class="lk__profile">
                <div class="title1">Список одобренных мероприятий</div>
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
                <div class="links">
                    <a href="./listEventActive_admin.php" class="active">Активные мероприятия</a>
                    <a href="./listEventCancelled_admin.php" class="no_active">Отмененные мероприятия</a>
                </div>
                <?php
                if (!$resultGetEvents) {
                        echo "<div class='no-results'>Ошибка при получении данных: " . pg_last_error() . "</div>";
                    } elseif (pg_num_rows($resultGetEvents) == 0) {
                        echo "<div class='no-results'><img src='./img/icons/not_found.svg' alt='not found'><div>Мероприятий не найдено</div></div>";
                    } else {
                ?>
                <div class="cards" style="margin-top: 25px;">
                    <?php
                        if ($resultGetEvents) {
                            while ($row = pg_fetch_assoc($resultGetEvents)) {
                                require './php/card_active_event.php';
                            }
                        } else {
                            echo "Ошибка при получении данных: " . pg_last_error();
                        }
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
    <script>
        document.getElementById('resetButton').addEventListener('click', function() {
            window.location.href = 'listEventActive_admin.php';
        });
    </script>
    <script>
        // Обработчик отмены активных мероприятий
        document.addEventListener('DOMContentLoaded', function() {
            const toggleButtons = document.querySelectorAll('.toggle-event-button');

            // Указание причины отмены
            toggleButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const eventId = button.getAttribute('data-id');
                    const isActive = button.getAttribute('data-status') === 't';

                    if (!isActive) {
                        updateEventStatus(eventId, null);
                    } else {
                        const reason = prompt('Укажите причину отмены мероприятия:');
                        if (reason !== null && reason.trim() !== '') {
                            updateEventStatus(eventId, reason);
                        }
                    }
                });
            });

            // Отмена мероприятия
            function updateEventStatus(eventId, reason) {
                fetch('./php/toggle_event.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `event_id=${eventId}&reason=${encodeURIComponent(reason || '')}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.querySelector(`.toggle-event-button[data-id="${eventId}"]`).closest('.card').remove();
                        } else {
                            alert(data.message || 'Ошибка при изменении статуса.');
                        }
                    })
                    .catch(error => {
                        console.error('Ошибка сети:', error);
                        alert('Ошибка сети. Попробуйте позже.');
                    });
            }
        });
    </script>
</body>

</html>