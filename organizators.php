<?php
session_start();
require './php/header.php';
require './php/conn.php';

$limit = 6; // Количество организаторов на страницу
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

$whereClauses = [];
$params = [];

// Поиск по названию организации
if (isset($_GET['organization_name']) && !empty($_GET['organization_name'])) {
    $whereClauses[] = "name LIKE '%' || $" . (count($params) + 1) . " || '%'";
    $params[] = $_GET['organization_name'];
}

$whereClause = count($whereClauses) > 0 ? " WHERE " . implode(" AND ", $whereClauses) : "";

// Запрос на получение организаторов с пагинацией
$getOrganizers = "SELECT * FROM organizators" . $whereClause . " LIMIT $limit OFFSET $offset";
$resultGetOrganizers = pg_query_params($conn, $getOrganizers, $params);

// Запрос для подсчета всех записей
$countQuery = "SELECT COUNT(*) FROM organizators" . $whereClause;
$countResult = pg_query_params($conn, $countQuery, $params);
$totalRows = pg_fetch_result($countResult, 0, 0);
$totalPages = ceil($totalRows / $limit);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/lk.css">
    <link rel="stylesheet" href="styles/events.css">
    <link rel="stylesheet" href="styles/media/media_general.css">
    <title>Организаторы</title>
</head>

<body>
    <div class="container">
        <div class="search-form">
            <form id="organizerSearchForm" method="GET" action="">
                <input type="text" id="organization_name" name="organization_name" placeholder="Название организации" value="<?= htmlspecialchars($_GET['organization_name'] ?? '') ?>">
                <button type="submit"><img src="./img/icons/search.svg" alt="Найти"></button>
                <button type="button" id="resetButton"><img src="./img/icons/close.svg" alt="Сбросить"></button>
            </form>
        </div>
        <div class="cards" style="margin-top: 25px;">
            <?php
            if (!$resultGetOrganizers) {
                echo "<div class='no-results'>Ошибка при получении данных: " . pg_last_error() . "</div>";
            } elseif (pg_num_rows($resultGetOrganizers) == 0) {
                echo "<div class='no-results'><img src='./img/icons/not_found.svg' alt='not found'><div>Организаторы не найдены</div></div>";
            } else {
                while ($row = pg_fetch_assoc($resultGetOrganizers)) {
                    require './php/card_organizator.php';
                }
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

    <?php require './php/footer.php'; ?>

    <script>
        document.getElementById('resetButton').addEventListener('click', function() {
            window.location.href = 'organizators.php';
        });
    </script>
</body>

</html>

<?php pg_close($conn); ?>