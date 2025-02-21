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
    ?>

    <div class="container">
        <div class="lk">
            <?php require 'php/lk/lk_menu.php'; ?>
            <div class="lk__profile">
                <div class="title1">Список одобренных мероприятий</div>
                <div class="links">
                    <a href="./listEventActive_admin.php" class="no_active">Активные мероприятия</a>
                    <a href="./listEventCancelled_admin.php" class="active">Отмененные мероприятия</a>
                </div>
                <div class="cards">
                    <?php
                    $getOrganizators = "select * from events where is_active = false and event_date > CURRENT_DATE ORDER BY event_date;";
                    $resultGetOrganizators = pg_query($conn, $getOrganizators);
                    if ($resultGetOrganizators) {
                        while ($row = pg_fetch_assoc($resultGetOrganizators)) {
                            require './php/card_active_event.php';
                        }
                    } else {
                        echo "Ошибка при получении данных: " . pg_last_error();
                    } ?>
                </div>
            </div>
        </div>
        <?php pg_close($conn); ?>
    </div>

    <?php require './php/footer.php'; ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleButtons = document.querySelectorAll('.toggle-event-button');

            toggleButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const eventId = button.getAttribute('data-id');
                    const statusElement = document.querySelector(`.status[data-id="${eventId}"]`);

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
                                // if (data.newStatus === 'true') {
                                //     statusElement.textContent = '✅ Одобрено';
                                //     button.textContent = 'Отклонить';
                                //     button.setAttribute('data-status', 't');
                                // } else {
                                //     statusElement.textContent = '❌ Отменено';
                                //     button.textContent = 'Одобрить';
                                //     button.setAttribute('data-status', 'f');
                                // }
                                // Удаление мероприятия с интерфейса
                                if (data.newStatus === 'true') {
                                    button.closest('.card').remove();
                                }
                            } else {
                                alert(data.message || 'Ошибка при изменении статуса.');
                            }
                        })
                        .catch(error => {
                            console.error('Ошибка сети:', error);
                            alert('Ошибка сети. Попробуйте позже.');
                        });
                });
            });
        });
    </script>
</body>

</html>