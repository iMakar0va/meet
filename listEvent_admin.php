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
                    $getOrganizators = "select * from events where is_active = false;";

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
            const approveButtons = document.querySelectorAll('.approve-button');

            approveButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const organizatorId = this.getAttribute('data-id');
                    const card = this.closest('.card'); // Находим карточку

                    fetch('./php/approve_organizator.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded', // Заголовок для обычных POST-форм
                            },
                            body: `organizator_id=${organizatorId}`
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                if (card) {
                                    card.remove(); // Если все ок - удаляем карточку
                                }
                            } else {
                                alert(data.message || 'Ошибка при одобрении заявки.');
                            }
                        })
                        .catch(error => {
                            console.error('Ошибка сети:', error);
                            alert('Произошла ошибка сети. Попробуйте позже.');
                        });
                });
            });
        });

        ///////////////////////////////
        document.addEventListener('DOMContentLoaded', function() {
            const deleteButtons = document.querySelectorAll('.delete-button');

            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const organizatorId = this.getAttribute('data-id');
                    const card = this.closest('.card'); // Находим карточку

                    fetch('./php/delete_organizator.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded', // Заголовок для обычных POST-форм
                            },
                            body: `organizator_id=${organizatorId}`
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                if (card) {
                                    card.remove();
                                }
                            } else {
                                alert(data.message || 'Ошибка при удалении заявки.');
                            }
                        })
                        .catch(error => {
                            console.error('Ошибка сети:', error);
                            alert('Произошла ошибка сети. Попробуйте позже.');
                        });
                });
            });
        });
    </script>
</body>

</html>