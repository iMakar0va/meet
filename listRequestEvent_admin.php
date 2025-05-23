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
                <div class="title1">Список заявок мероприятий</div>
                <div class="cards">
                    <?php
                    $getPendingEvents = "SELECT * FROM events WHERE is_approved = false and is_active = false ORDER BY event_date ASC;";
                    $result = pg_query($conn, $getPendingEvents);

                    if ($result && pg_num_rows($result) > 0) {
                        while ($row = pg_fetch_assoc($result)) {
                            require './php/card_request_event.php';
                        }
                    } else {
                        echo "<p>Нет заявок на рассмотрение.</p>";
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
            </div>
        </div>
        <?php pg_close($conn); ?>
    </div>

    <?php require './php/footer.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="./scripts/custom‑dialogs.js"></script>
    <script>
        // Обработчик запросов мероприятия
        function approveEvent(eventId, eventTitle) {
            customConfirm(`Вы уверены, что хотите одобрить мероприятие "${eventTitle}"?`, function(confirmed) {
                if (!confirmed) {
                    return; // Если пользователь отменил, ничего не делаем
                }

                // Отправка запроса на одобрение мероприятия
                fetch('php/process_event_request.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: 'event_id=' + eventId + '&action=approve'
                    })
                    .then(response => response.json())
                    .then(data => {
                        customAlert(data.message); // Показываем кастомное сообщение
                        if (data.success) {
                            const eventCard = document.querySelector(`#event-${eventId}`);
                            if (eventCard) eventCard.remove();
                        }
                    })
                    .catch(error => {
                        console.error('Ошибка:', error);
                        customAlert('Произошла ошибка при обработке запроса.');
                    });
            });
        }

        // Указание причины откоза
        function rejectEvent(eventId, eventTitle) {
            customPrompt(`Укажите причину отклонения мероприятия "${eventTitle}":`, function(reason) {
                if (!reason) {
                    return; // Если причина не указана, ничего не делаем
                }

                // Отправка запроса на отклонение мероприятия с причиной
                fetch('php/process_event_request.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: 'event_id=' + eventId + '&action=reject&reason=' + encodeURIComponent(reason)
                    })
                    .then(response => response.json())
                    .then(data => {
                        customAlert(data.message); // Показываем кастомное сообщение
                        if (data.success) {
                            const eventCard = document.querySelector(`#event-${eventId}`);
                            if (eventCard) eventCard.remove();
                        }
                    })
                    .catch(error => {
                        console.error('Ошибка:', error);
                        customAlert('Произошла ошибка при обработке запроса.');
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