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

    $limit = 4; // Количество мероприятий на страницу
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $offset = ($page - 1) * $limit;

    // Запрос на получение мероприятий с пагинацией
    $getEvents = "SELECT * FROM organizators o
                     JOIN events e ON e.organizator_id = o.organizator_id
                     WHERE o.organizator_id = $userId AND e.event_date >= CURRENT_DATE AND is_active = false AND e.is_approved = false
                     ORDER BY event_date LIMIT $limit OFFSET $offset";

    $resultGetEvents = pg_query($conn, $getEvents);

    // Запрос для подсчёта всех записей (без лимита и оффсета)
    $countQuery = "SELECT COUNT(*) FROM organizators o
                   JOIN events e ON e.organizator_id = o.organizator_id
                   WHERE o.organizator_id = $userId AND e.event_date >= CURRENT_DATE AND is_active = false and e.is_approved = false;";

    $countResult = pg_query($conn, $countQuery);
    $totalRows = pg_fetch_result($countResult, 0, 0);
    $totalPages = ceil($totalRows / $limit);
    ?>

    <div class="container">
        <div class="lk">
            <?php require 'php/lk/lk_menu.php'; ?>
            <div class="lk__profile">
                <div class="title1">Список мероприятий, ожидающие одобрения</div>
                <div class="cards">
                    <?php
                    if ($resultGetEvents && pg_num_rows($resultGetEvents) > 0) {
                        while ($row = pg_fetch_assoc($resultGetEvents)) {
                            require './php/card_future_event.php';
                        }
                    } else {
                        echo "<p>Нет текущих мероприятий для отображения.</p>";
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="./scripts/custom‑dialogs.js"></script>
    <script>
        // Функция для удаления мероприятия
        function deleteEvent(eventId) {
            // Подтверждение удаления через кастомное окно
            customConfirm("Вы уверены, что хотите удалить это мероприятие?", function(isConfirmed) {
                if (!isConfirmed) {
                    return; // Если пользователь отменил, ничего не делаем
                }

                // Отправка AJAX-запроса
                $.ajax({
                    url: 'php/delete_event.php', // Файл, который обрабатывает удаление
                    type: 'GET',
                    data: {
                        event_id: eventId
                    }, // Передаем ID мероприятия
                    dataType: 'json', // Ожидаем JSON-ответ
                    success: function(response) {
                        if (response.success) {
                            // Удаляем карточку мероприятия со страницы
                            $(`#event-${eventId}`).remove(); // Удаляем элемент с ID event-{eventId}
                            customAlert(response.message); // Показываем сообщение об успехе
                        } else {
                            customAlert(response.message); // Показываем сообщение об ошибке
                        }
                    },
                    error: function(xhr, status, error) {
                        customAlert("Ошибка при отправке запроса: " + error); // Обработка ошибок AJAX
                    }
                });
            });
        }
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
                        customAlert(response.message); // Показываем сообщение об ошибке через кастомное окно
                    }
                },
                error: function(xhr, status, error) {
                    customAlert("Ошибка при загрузке данных: " + error); // Обработка ошибок AJAX через кастомное окно
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