<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/lk.css">
    <link rel="stylesheet" href="styles/event.css">
    <link rel="stylesheet" href="styles/media/media_event.css">
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
            COUNT(CASE WHEN e.is_active = true AND e.is_approved = true THEN 1 END) AS current_events,
            COUNT(CASE WHEN e.is_active = false AND e.is_approved = true THEN 1 END) AS past_events,
            COUNT(CASE WHEN e.is_active = false AND e.is_approved = false THEN 1 END) AS canceled_events
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
            <div class="card_organizator">
                <h2 class="card__title"> <?= htmlspecialchars($row["name"]) ?> </h2>
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

            <div class="links">
                <a href="./organizatorEventNow.php?organizator_id=<?= htmlspecialchars($row['organizator_id']) ?>" class="active">Текущие мероприятия</a>
                <a href="./organizatorEventPast.php?organizator_id=<?= htmlspecialchars($row['organizator_id']) ?>" class="no_active">Пройденные мероприятия</a>
            </div>

            <div class="cards">
                <?php
                $getEventUser = "
                SELECT * FROM events e
                JOIN organizators_events oe ON e.event_id = oe.event_id
                WHERE oe.organizator_id = $1 AND e.event_date > CURRENT_DATE";
                $resultGetEventUser = pg_query_params($conn, $getEventUser, [$organizerId]);

                if ($resultGetEventUser && pg_num_rows($resultGetEventUser) > 0) {
                    while ($row = pg_fetch_assoc($resultGetEventUser)) {
                        require './php/card.php';
                    }
                } else {
                    echo "<p>Нет прошедших мероприятий для отображения.</p>";
                }
                ?>
            </div>
        </section>
    </div>

    <?php require './php/footer.php'; ?>
</body>

</html>