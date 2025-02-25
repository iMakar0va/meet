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
                <div class="title1">Список одобренных организаторов</div>
                <div class="links">
                    <a href="./listOrganizatorActive_admin.php" class="active">Активные организаторы</a>
                    <a href="./listOrganizatorCancelled_admin.php" class="no_active">Отмененные организаторы</a>
                </div>

                <div class="cards_line">
                    <?php
                    $getOrganizators = "select * from organizators where is_organizator = true;";

                    $resultGetOrganizators = pg_query($conn, $getOrganizators);

                    if ($resultGetOrganizators) {
                        while ($row = pg_fetch_assoc($resultGetOrganizators)) {
                            require './php/card_organizator_admin.php';
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
            document.querySelectorAll('.toggle-button').forEach(button => {
                button.addEventListener('click', function() {
                    const organizatorId = button.getAttribute('data-id');
                    const isApproved = button.getAttribute('data-status') === 'true';
                    let reason = '';

                    if (isApproved) {
                        reason = prompt('Укажите причину отмены статуса организатора:');
                        if (!reason || reason.trim() === '') {
                            alert('Причина обязательна.');
                            return;
                        }
                    }

                    fetch('./php/toggle_organizator.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: `organizator_id=${organizatorId}&action=${isApproved ? 'cancel' : 'approve'}&reason=${encodeURIComponent(reason)}`
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert("Статус организатора снят!");
                                document.querySelector(`.card_organizator[data-id="${organizatorId}"]`).remove();
                            } else {
                                alert(data.message || 'Ошибка при изменении статуса.');
                            }
                        })
                        .catch(error => {
                            console.error('Ошибка сети:', error);
                            // alert('Ошибка сети. Попробуйте позже.');
                        });
                });
            });
        });
    </script>


</body>

</html>