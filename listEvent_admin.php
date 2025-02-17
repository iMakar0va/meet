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
                <div class="title1">Список заявок организаторов</div>
                <div class="cards_line">
                    <?php
                    $getOrganizators = "select * from events ORDER BY is_active ASC;";

                    $resultGetOrganizators = pg_query($conn, $getOrganizators);

                    if ($resultGetOrganizators) {
                        while ($row = pg_fetch_assoc($resultGetOrganizators)) {
                            require './php/card_request_event.php';
                        }
                    } else {
                        echo "Ошибка при получении данных: " . pg_last_error();
                    } ?>
                </div>
                <!-- /cards -->
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
                                // Обновляем текст статуса и кнопки на странице
                                if (data.newStatus === 'true') {
                                    statusElement.textContent = '✅ Одобрено';
                                    button.textContent = 'Отклонить';
                                    button.setAttribute('data-status', 't');
                                } else {
                                    statusElement.textContent = '❌ Ожидает подтверждения';
                                    button.textContent = 'Одобрить';
                                    button.setAttribute('data-status', 'f');
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