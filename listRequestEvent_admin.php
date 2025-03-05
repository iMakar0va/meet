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
            </div>
        </div>
        <?php pg_close($conn); ?>
    </div>

    <?php require './php/footer.php'; ?>
    <script>
        // Обработчик запросов мероприятия
        function approveEvent(eventId, eventTitle) {
            if (confirm(`Одобрить мероприятие "${eventTitle}"?`)) {
                fetch('php/process_event_request.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: 'event_id=' + eventId + '&action=approve'
                    })
                    .then(response => response.json())
                    .then(data => {
                        alert(data.message);
                        if (data.success) {
                            const eventCard = document.querySelector(`#event-${eventId}`);
                            if (eventCard) eventCard.remove();
                        }
                    })
                    .catch(error => console.error('Ошибка:', error));
            }
        }

        // Указание причины откза
        function rejectEvent(eventId, eventTitle) {
            let reason = prompt(`Укажите причину отклонения мероприятия "${eventTitle}":`);
            if (!reason) return;

            fetch('php/process_event_request.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'event_id=' + eventId + '&action=reject&reason=' + encodeURIComponent(reason)
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) {
                        const eventCard = document.querySelector(`#event-${eventId}`);
                        if (eventCard) eventCard.remove();
                    }
                })
                .catch(error => console.error('Ошибка:', error));
        }
    </script>
</body>

</html>