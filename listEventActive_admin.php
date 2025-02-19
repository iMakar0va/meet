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
                <a href="./listEventActive_admin.php">Активные мероприятия</a>
                <a href="./listEventCancelled_admin.php">Отмененные мероприятия</a>
                <div class="cards">
                    <?php
                    $getOrganizators = "select * from events where is_active = true and event_date > CURRENT_DATE ORDER BY event_date;";
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
                    const isActive = button.getAttribute('data-status') === 't';

                    if (!isActive) {
                        // Если мероприятие уже отменено, просто одобряем его без причины
                        updateEventStatus(eventId, null);
                    } else {
                        // Если мероприятие активно, запрашиваем причину отмены
                        const reason = prompt('Укажите причину отмены мероприятия:');
                        if (reason !== null && reason.trim() !== '') {
                            updateEventStatus(eventId, reason);
                        }
                    }
                });
            });

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