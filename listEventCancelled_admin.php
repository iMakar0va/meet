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
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
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
$getEvents = "SELECT * FROM events WHERE is_active = false and is_approved = true  AND event_date >= CURRENT_DATE $whereClause ORDER BY event_date LIMIT $limit OFFSET $offset";
$resultGetEvents = pg_query_params($conn, $getEvents, $params);;

// Запрос для подсчёта всех записей (без лимита и оффсета)
$countQuery = "SELECT COUNT(*) FROM events WHERE is_active = false and is_approved = true  AND event_date >= CURRENT_DATE $whereClause";
$countResult = pg_query_params($conn, $countQuery, $params);
$totalRows = pg_fetch_result($countResult, 0, 0);
$totalPages = ceil($totalRows / $limit);
?>

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
                    <a href="./listEventActive_admin.php" class="no_active">Активные мероприятия</a>
                    <a href="./listEventCancelled_admin.php" class="active">Неактивные мероприятия</a>
                </div>
                <div class="cards" style="margin-top: 25px;">
                    <?php
                    if ($resultGetEvents && pg_num_rows($resultGetEvents) > 0) {
                        while ($row = pg_fetch_assoc($resultGetEvents)) {
                            require './php/card_active_event.php';
                        }
                    } else {
                        echo "<p>Мероприятий не найдено</p>";
                    }
                    ?>
                </div>
                <div id="commentModal" class="modal">
                    <div class="modal-content">
                        <span class="close">&times;</span> <!-- Крестик для закрытия -->
                        <h2>Название мероприятия:</h2>
                        <div id="modalTitle"></div> <!-- Название мероприятия -->
                        <br>
                        <h2>Комментарий:</h2>
                        <p id="modalComment"></p> <!-- Комментарий (причина отказа) -->
                    </div>
                </div>
                <!-- Пагинация -->
                <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?>">Назад</a>
                        <?php endif; ?>

                        <span>Страница <?php echo $page; ?> из <?php echo $totalPages; ?></span>

                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?php echo $page + 1; ?>">Вперед</a>
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
            window.location.href = 'listEventCancelled_admin.php';
        });
    </script>
    <script src="./scripts/custom‑dialogs.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Обработчик одобрения активных мероприятий
        document.addEventListener('DOMContentLoaded', function() {
            const toggleButtons = document.querySelectorAll('.toggle-event-button');

            // Одобрение мероприятия
            toggleButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const eventId = button.getAttribute('data-id');

                    // Используем кастомное подтверждение
                    customConfirm("Вы уверены, что хотите одобрить это мероприятие?", function(confirmed) {
                        if (!confirmed) {
                            return; // Если пользователь отменил, ничего не делаем
                        }

                        // Если пользователь подтвердил, отправляем запрос на одобрение мероприятия
                        fetch('./php/toggle_event.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded',
                                },
                                body: `event_id=${eventId}`
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    button.closest('.card').remove();
                                    customAlert("Мероприятие одобрено.");
                                } else {
                                    customAlert(data.message || 'Ошибка при изменении статуса.');
                                }
                            })
                            .catch(error => {
                                console.error('Ошибка сети:', error);
                                customAlert('Ошибка сети. Попробуйте позже.');
                            });
                    });
                });
            });
        });
    </script>
    <script>
        // Функция для открытия модального окна и загрузки данных
        function showComment(eventId) {
            // Отправка AJAX-запроса для получения данных
            $.ajax({
                url: 'php/get_comment.php', // Файл, который возвращает данные
                type: 'GET',
                data: {
                    event_id: eventId
                }, // Передаем ID мероприятия
                dataType: 'json', // Ожидаем JSON-ответ
                success: function(response) {
                    if (response.success) {
                        // Заполняем модальное окно данными
                        $('#modalTitle').text(response.title); // Название мероприятия
                        $('#modalComment').text(response.comment); // Комментарий
                        $('#commentModal').css('display', 'block'); // Показываем модальное окно
                    } else {
                        alert(response.message); // Показываем сообщение об ошибке
                    }
                },
                error: function(xhr, status, error) {
                    alert("Ошибка при загрузке данных: " + error); // Обработка ошибок AJAX
                }
            });
        }

        // Закрытие модального окна
        $(document).ready(function() {
            // Закрытие при клике на крестик
            $('.close').click(function() {
                $('#commentModal').css('display', 'none');
            });

            // Закрытие при клике вне модального окна
            $(window).click(function(event) {
                if (event.target.id === 'commentModal') {
                    $('#commentModal').css('display', 'none');
                }
            });
        });
    </script>
</body>

</html>