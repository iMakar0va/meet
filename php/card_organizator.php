<?php
$organizerId = htmlspecialchars($row['organizator_id']);

// Получение количества текущих мероприятий
$currentEventsQuery = "
    SELECT COUNT(*)
    FROM events e
    JOIN organizators_events oe ON e.event_id = oe.event_id
    WHERE oe.organizator_id = $1 AND e.is_active = true AND e.is_approved = true";
$currentEventsResult = pg_query_params($conn, $currentEventsQuery, array($organizerId));
$currentEventsCount = pg_fetch_result($currentEventsResult, 0, 0);

// Получение количества проведенных мероприятий
$pastEventsQuery = "
    SELECT COUNT(*)
    FROM events e
    JOIN organizators_events oe ON e.event_id = oe.event_id
    WHERE oe.organizator_id = $1 AND e.is_active = false AND e.is_approved = true";
$pastEventsResult = pg_query_params($conn, $pastEventsQuery, array($organizerId));
$pastEventsCount = pg_fetch_result($pastEventsResult, 0, 0);

// Получение количества отмененных мероприятий
$canceledEventsQuery = "
    SELECT COUNT(*)
    FROM events e
    JOIN organizators_events oe ON e.event_id = oe.event_id
    WHERE oe.organizator_id = $1 AND e.is_active = false AND e.is_approved = false";
$canceledEventsResult = pg_query_params($conn, $canceledEventsQuery, array($organizerId));
$canceledEventsCount = pg_fetch_result($canceledEventsResult, 0, 0);

// Получение направлений мероприятий
$eventTypesQuery = "
    SELECT DISTINCT e.topic
    FROM events e
    JOIN organizators_events oe ON e.event_id = oe.event_id
    WHERE oe.organizator_id = $1";
$eventTypesResult = pg_query_params($conn, $eventTypesQuery, array($organizerId));

$eventTypes = [];
while ($rowType = pg_fetch_assoc($eventTypesResult)) {
    $eventTypes[] = $rowType['topic'];
}
$eventTypesList = implode(', ', $eventTypes);
if (empty($eventTypesList)) {
    $eventTypesList = "Организатор еще не создавал мероприятия";
}
?>
<div class="card_organizator" data-id="<?= htmlspecialchars($row['organizator_id']) ?>">
    <div class="card_blocks">
        <div class="card_blocks_right">
            <div class="card__content">
                <div class="card__item item__title"><?= htmlspecialchars($row["name"]) ?></div>
                <div class="card__item"><?= htmlspecialchars($row["email"]) ?></div>
                <div class="card__item"><?= htmlspecialchars($row["phone_number"]) ?></div>
                <div class="card__item">Дата регистрации: <?= htmlspecialchars($row["date_start_work"]) ?></div>
                <div class="card__item status" data-id="<?= htmlspecialchars($row['organizator_id']) ?>">
                    Статус: <?= $row["is_organizator"] === 't' ? 'Организатор' : 'Не организатор' ?>
                </div>
            </div>
            <div class="card__content">
                <div class="card__item">Текущих мероприятий: <?= $currentEventsCount ?></div>
                <div class="card__item">Проведено мероприятий: <?= $pastEventsCount ?></div>
                <div class="card__item">Отмененных мероприятий: <?= $canceledEventsCount ?></div>
                <div class="card__item">Направления мероприятий:
                    <br>
                    <?= $eventTypesList ?>
                </div>
            </div>
        </div>
        <div class="card_blocks_left">
            <button class="btn1 toggle-button">
                Подробнее
            </button>
        </div>
    </div>
    <div class="card__bottom">Описание деятельности: <?= htmlspecialchars($row["description"]) ?></div>
</div>