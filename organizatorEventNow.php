<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/lk.css">
    <link rel="stylesheet" href="styles/events.css">
    <link rel="stylesheet" href="styles/media/media_events.css">

    <title>Профиль организатора</title>
    <style>
        section {
            background-color: var(--very-light-blue-color);
            padding: 35px;
            margin: 35px auto;
            border-radius: 15px;
            display: flex;
            flex-direction: column;
            gap: 25px;
        }

        .card_organizator {
            margin-right: 0;
        }
    </style>
    <link rel="stylesheet" href="styles/media/media_organizators.css">
</head>

<body>
    <?php
    session_start();
    require './php/header.php';
    require './php/conn.php';


    $organizerId = intval($_GET['organizator_id']);

    $query = "SELECT * FROM organizators WHERE organizator_id = $1";
    $result = pg_query_params($conn, $query, [$organizerId]);

    if (!$result || pg_num_rows($result) === 0) {
        echo "<p>Организатор не найден.</p>";
        require './php/footer.php';
        exit;
    }

    $row = pg_fetch_assoc($result);


    // Запросы для подсчета мероприятий
    $statsQuery = "
        SELECT
            COUNT(CASE WHEN e.is_active = true AND e.is_approved = true and e.event_date >= CURRENT_DATE THEN 1 END) AS current_events,
            COUNT(CASE WHEN e.is_active = true AND e.is_approved = true and e.event_date < CURRENT_DATE THEN 1 END) AS past_events,
            COUNT(CASE WHEN e.is_active = false AND e.is_approved = true THEN 1 END) AS canceled_events
        FROM events e
        JOIN organizators_events oe ON e.event_id = oe.event_id
        WHERE oe.organizator_id = $1
    ";
    $statsResult = pg_query_params($conn, $statsQuery, [$organizerId]);
    $stats = pg_fetch_assoc($statsResult);

    // Получение направлений мероприятий
    $eventTypesQuery = "
        SELECT DISTINCT e.topic
        FROM events e
        JOIN organizators_events oe ON e.event_id = oe.event_id
        WHERE oe.organizator_id = $1";
    $eventTypesResult = pg_query_params($conn, $eventTypesQuery, [$organizerId]);

    $eventTypes = [];
    while ($rowType = pg_fetch_assoc($eventTypesResult)) {
        $eventTypes[] = htmlspecialchars($rowType['topic']);
    }
    $eventTypesList = empty($eventTypes) ? "Организатор еще не создавал мероприятия" : implode(', ', $eventTypes);
    ?>

    <div class="container">
        <section>
            <div class="card_organizator title3">
                <h2 class="card__title title2"> <?= htmlspecialchars($row["name"]) ?> </h2>
                <div class="card__blocks">
                    <div class="card__block">
                        <div class="card__item">Email: <?= htmlspecialchars($row["email"]) ?></div>
                        <div class="card__item">Телефон: <?= htmlspecialchars($row["phone_number"]) ?></div>
                        <div class="card__item">Дата основания: <?= htmlspecialchars($row["date_start_work"]) ?></div>
                        <div class="card__item">Статус: <?= $row["is_organizator"] === 't' ? 'Организатор' : 'Не организатор' ?></div>
                    </div>
                    <div class="card__block">
                        <div class="card__item">Текущие мероприятия: <?= $stats['current_events'] ?></div>
                        <div class="card__item">Прошедшие мероприятия: <?= $stats['past_events'] ?></div>
                        <div class="card__item">Отмененные мероприятия: <?= $stats['canceled_events'] ?></div>
                    </div>
                </div>
                <div class="card__item"><b>Направления мероприятий:</b> <?= $eventTypesList ?></div>
                <div class="card__item"><b>Описание деятельности:</b> <?= htmlspecialchars($row["description"]) ?></div>
            </div>

            <div class="links title3">
                <a href="./organizatorEventNow.php?organizator_id=<?= htmlspecialchars($row['organizator_id']) ?>" class="active">Текущие мероприятия</a>
                <a href="./organizatorEventPast.php?organizator_id=<?= htmlspecialchars($row['organizator_id']) ?>" class="no_active">Прошедшие мероприятия</a>
            </div>

            <div class="cards">
                <?php
                $limit = 6; // Количество мероприятий на страницу
                $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
                $offset = ($page - 1) * $limit;

                // Получение списка мероприятий с пагинацией
                $getEvents = "SELECT e.* FROM events e
                JOIN organizators_events oe ON e.event_id = oe.event_id
                WHERE oe.organizator_id = $1 AND e.is_active = true
                AND e.is_approved = true AND e.event_date >= CURRENT_DATE
                ORDER BY e.event_date LIMIT $limit OFFSET $offset";

                $resultGetEvents = pg_query_params($conn, $getEvents, [$organizerId]);

                // Подсчет общего количества мероприятий
                $countQuery = "SELECT COUNT(*) FROM events e
                JOIN organizators_events oe ON e.event_id = oe.event_id
                WHERE oe.organizator_id = $1 AND e.is_active = true
                AND e.is_approved = true AND e.event_date >= CURRENT_DATE;";

                $countResult = pg_query_params($conn, $countQuery, [$organizerId]);
                $totalRows = pg_fetch_result($countResult, 0, 0);
                $totalPages = ($totalRows > 0) ? ceil($totalRows / $limit) : 1;

                if ($resultGetEvents && pg_num_rows($resultGetEvents) > 0) {
                    while ($row = pg_fetch_assoc($resultGetEvents)) {
                        require './php/card.php';
                    }
                } else {
                    echo "<p>Нет текущих мероприятий для отображения.</p>";
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
        </section>
    </div>

    <?php require './php/footer.php'; ?>
</body>

</html>