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
                <div class="title1">Список заявок организаторов</div>
                <div class="cards_line">
                    <?php
                    $query = "SELECT * FROM organizators WHERE is_approved = false;";
                    $result = pg_query($conn, $query);

                    if ($result && pg_num_rows($result) > 0) {
                        while ($row = pg_fetch_assoc($result)) {
                            require './php/card_request.php';
                        }
                    } else {
                        echo "<p style='text-align:center;'>Нет заявок на рассмотрение.</p>";
                    }
                    ?>
                </div>
            </div>
        </div>
        <?php pg_close($conn); ?>
    </div>

    <?php require './php/footer.php'; ?>
    <script src="./scripts/custom‑dialogs.js"></script>
    <script>
        // Обработчик запросов организаторов
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.approve-button, .delete-button').forEach(button => {
                button.addEventListener('click', function() {
                    const organizatorId = button.getAttribute('data-id');
                    const action = button.classList.contains('approve-button') ? 'approve' : 'delete';
                    let reason = '';

                    // Обработка отклонения заявки
                    if (action === 'delete') {
                        customPrompt('Укажите причину отклонения заявки:', function(inputReason) {
                            reason = inputReason;
                            if (!reason || reason.trim() === '') {
                                return;
                            }

                            // Отправка запроса на отклонение
                            handleRequest(action, organizatorId, reason, button);
                        });
                    }

                    // Обработка одобрения заявки
                    if (action === 'approve') {
                        customConfirm('Вы уверены, что хотите одобрить заявку?', function(confirmed) {
                            if (!confirmed) {
                                return; // Если пользователь отменил, ничего не делаем
                            }

                            // Отправка запроса на одобрение
                            handleRequest(action, organizatorId, reason, button);
                        });
                    }
                });
            });
        });

        // Функция для отправки запросов на одобрение или отклонение
        function handleRequest(action, organizatorId, reason, button) {
            fetch('./php/toggle_request.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `organizator_id=${organizatorId}&action=${action}&reason=${encodeURIComponent(reason)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        button.closest('.card_organizator').remove();
                        customAlert(data.message || 'Запрос обработан успешно.');
                    } else {
                        customAlert(data.message || 'Ошибка при обработке запроса.');
                    }
                })
                .catch(error => {
                    console.error('Ошибка сети:', error);
                    customAlert('Ошибка сети. Попробуйте позже.');
                });
        }
    </script>

</body>

</html>