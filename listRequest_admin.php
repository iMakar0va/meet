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
                    $getOrganizators = "select * from organizators ORDER BY is_organizator ASC;";

                    $resultGetOrganizators = pg_query($conn, $getOrganizators);

                    if ($resultGetOrganizators) {
                        while ($row = pg_fetch_assoc($resultGetOrganizators)) {
                            require './php/card_request.php';
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
            const toggleButtons = document.querySelectorAll('.toggle-button');

            toggleButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const organizatorId = button.getAttribute('data-id');
                    const currentStatus = button.getAttribute('data-status');
                    const statusElement = document.querySelector(`.status[data-id="${organizatorId}"]`);

                    fetch('./php/toggle_organizator.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `organizator_id=${organizatorId}`
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Меняем текст статуса и кнопки на странице
                                if (data.newStatus === 'true') {
                                    statusElement.textContent = '✅ Организатор';
                                    button.textContent = 'Снять права';
                                    button.setAttribute('data-status', 't');
                                } else {
                                    statusElement.textContent = '❌ Не организатор';
                                    button.textContent = 'Назначить организатором';
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