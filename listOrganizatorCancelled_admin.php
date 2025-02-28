<?php
session_start();
require './php/header.php';
require './php/conn.php';

$limit = 1; // Количество организаторов на страницу
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Основной запрос с пагинацией
$getOrganizators = "SELECT * FROM organizators WHERE is_organizator = false LIMIT $limit OFFSET $offset";
$resultGetOrganizators = pg_query($conn, $getOrganizators);

// Запрос для подсчёта всех записей (без лимита и оффсета)
$countQuery = "SELECT COUNT(*) FROM organizators WHERE is_organizator = false";
$countResult = pg_query($conn, $countQuery);
$totalRows = pg_fetch_result($countResult, 0, 0);
$totalPages = ceil($totalRows / $limit);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <link rel="stylesheet" href="styles/auth.css">
    <link rel="stylesheet" href="styles/lk.css">
    <!-- <link rel="stylesheet" href="styles/events.css"> -->
    <link rel="stylesheet" href="styles/media/media_auth.css">
    <link rel="stylesheet" href="styles/media/media_lk.css">
    <title>Личный кабинет</title>
</head>

<body>
    <div class="container">
        <div class="lk">
            <?php require 'php/lk/lk_menu.php'; ?>
            <div class="lk__profile">
                <div class="title1">Список отмененных организаторов</div>
                <div class="links">
                    <a href="./listOrganizatorActive_admin.php" class="no_active">Активные организаторы</a>
                    <a href="./listOrganizatorCancelled_admin.php" class="active">Отмененные организаторы</a>
                </div>
                <div class="cards">
                    <?php
                    if ($resultGetOrganizators && pg_num_rows($resultGetOrganizators) > 0) {
                        while ($row = pg_fetch_assoc($resultGetOrganizators)) {
                            require './php/card_organizator_admin.php';
                        }
                    } else {
                        echo "<p>Нет отмененных организаторов для отображения.</p>";
                    }
                    ?>
                </div>

                <!-- Пагинация -->
                <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">Назад</a>
                        <?php endif; ?>

                        <span>Страница <?= $page ?> из <?= $totalPages ?></span>

                        <?php if ($page < $totalPages): ?>
                            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">Вперед</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php pg_close($conn); ?>
    <?php require './php/footer.php'; ?>

    <script>
        // Обработчик одобрения активных организаторов
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.toggle-button').forEach(button => {
                button.addEventListener('click', function() {
                    const organizatorId = button.getAttribute('data-id');

                    // Одобрение активных организаторов
                    fetch('./php/toggle_organizator.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: `organizator_id=${organizatorId}&action=approve`
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert("Статус организатора присвоен!");
                                document.querySelector(`.card_organizator[data-id="${organizatorId}"]`).remove();
                            } else {
                                alert(data.message || 'Ошибка при изменении статуса.');
                            }
                        })
                        .catch(error => {
                            console.error('Ошибка сети:', error);
                        });
                });
            });
        });
    </script>
</body>

</html>