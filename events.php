<?php
session_start();
require './php/header.php';
require './php/conn.php';

$limit = 8; // Количество мероприятий на страницу
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

$whereClauses = [];
$params = [];

if (!empty($_GET['city'])) {
    $whereClauses[] = "city LIKE '%' || $" . (count($params) + 1) . " || '%'";
    $params[] = $_GET['city'];
}

if (!empty($_GET['type'])) {
    $whereClauses[] = "type = $" . (count($params) + 1);
    $params[] = $_GET['type'];
}

if (!empty($_GET['topic'])) {
    $whereClauses[] = "topic = $" . (count($params) + 1);
    $params[] = $_GET['topic'];
}

if (!empty($_GET['event_date'])) {
    $whereClauses[] = "event_date = $" . (count($params) + 1);
    $params[] = $_GET['event_date'];
}

$whereClause = count($whereClauses) > 0 ? " AND " . implode(" AND ", $whereClauses) : "";

// Основной запрос с пагинацией
$getEvents = "SELECT * FROM events WHERE event_date >= CURRENT_DATE AND is_active = true $whereClause ORDER BY event_date ASC LIMIT $limit OFFSET $offset";
$resultGetEvents = pg_query_params($conn, $getEvents, $params);

// Запрос для подсчёта всех записей (без лимита и оффсета)
$countQuery = "SELECT COUNT(*) FROM events WHERE event_date >= CURRENT_DATE AND is_active = true $whereClause";
$countResult = pg_query_params($conn, $countQuery, $params);
$totalRows = pg_fetch_result($countResult, 0, 0);
$totalPages = ceil($totalRows / $limit);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/events.css">
    <title>Афиша</title>
</head>

<body>
    <div class="container">
        <div class="search-form">
            <form id="eventSearchForm" method="GET" action="">
                <input type="text" id="city" name="city" placeholder="Город" autocomplete="off" value="<?= htmlspecialchars($_GET['city'] ?? '') ?>">
                <select id="type" name="type">
                    <option value="" disabled selected hidden>Выберите тип:</option>
                    <?php
                    $types = ["Конференция", "Выставка", "Презентация", "Мастер-класс", "Соревнования", "Семинар", "Тренинг", "Форум", "Экскурсия", "Кинопоказ", "Лекция", "Клуб", "Творческий вечер", "Ярмарка", "Курсы"];
                    foreach ($types as $type) {
                        $selected = (isset($_GET['type']) && $_GET['type'] === $type) ? 'selected' : '';
                        echo "<option value='$type' $selected>$type</option>";
                    }
                    ?>
                </select>
                <select id="topic" name="topic">
                    <option value="" disabled selected hidden>Выберите направление:</option>
                    <?php
                    $topics = ["технологии и инновации", "бизнес и финансы", "здоровье и фитнес", "кулинария и питание", "путешествие и туризм", "искусство и культура", "языки и образование", "семейные отношения", "хобби и развлечения"];
                    foreach ($topics as $topic) {
                        $selected = (isset($_GET['topic']) && $_GET['topic'] === $topic) ? 'selected' : '';
                        echo "<option value='$topic' $selected>$topic</option>";
                    }
                    ?>
                </select>
                <input type="date" id="event_date" name="event_date" value="<?= htmlspecialchars($_GET['event_date'] ?? '') ?>">
                <button type="submit"><img src="./img/icons/search.svg" alt="Найти"></button>
                <button type="button" id="resetButton"><img src="./img/icons/close.svg" alt="Сбросить"></button>
            </form>
        </div>

        <div class="cards" style="margin-top: 25px;">
            <?php
            if (!$resultGetEvents) {
                echo "<div class='no-results'>Ошибка при получении данных: " . pg_last_error() . "</div>";
            } elseif (pg_num_rows($resultGetEvents) == 0) {
                echo "<div class='no-results'><img src='./img/icons/not_found.svg' alt='not found'><div>Мероприятий не найдено</div></div>";
            } else {
                while ($row = pg_fetch_assoc($resultGetEvents)) {
                    require './php/card.php';
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
        <?php endif;

        ?>
    </div>
    <?php
    require './php/footer.php';
    pg_close($conn);
    ?>

    <script>
        document.getElementById('resetButton').addEventListener('click', function() {
            window.location.href = 'events.php';
        });
    </script>

</body>

</html>