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
                    $query = "SELECT * FROM organizators WHERE is_approved = false;";
                    $result = pg_query($conn, $query);

                    if ($result) {
                        while ($row = pg_fetch_assoc($result)) {
                            require './php/card_request.php';
                        }
                    } else {
                        echo "Ошибка при получении данных: " . pg_last_error();
                    }
                    ?>
                </div>
            </div>
        </div>
        <?php pg_close($conn); ?>
    </div>

    <?php require './php/footer.php'; ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.approve-button, .delete-button').forEach(button => {
            button.addEventListener('click', function() {
                const organizatorId = button.getAttribute('data-id');
                const action = button.classList.contains('approve-button') ? 'approve' : 'delete';
                let reason = '';

                if (action === 'delete') {
                    reason = prompt('Укажите причину отклонения заявки:');
                    if (!reason || reason.trim() === '') {
                        alert('Причина отклонения обязательна.');
                        return;
                    }
                }

                fetch('./php/toggle_request.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `organizator_id=${organizatorId}&action=${action}&reason=${encodeURIComponent(reason)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        button.closest('.card').remove();
                    } else {
                        alert(data.message || 'Ошибка при обработке запроса.');
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
