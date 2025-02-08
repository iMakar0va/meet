<div class="card">
    <div class="card__content">
        <div class="card__text"><?= htmlspecialchars($row["name"]) ?></div>
        <div class="card__text"><?= htmlspecialchars($row["phone_number"]) ?></div>
        <div class="card__text"><?= htmlspecialchars($row["email"]) ?></div>
        <br>
        <?php
        $organizatorId = $row["organizator_id"];

        // Параметризованный запрос для предотвращения SQL инъекций
        $getInfo = "
            SELECT COUNT(*) as current_events_count
            FROM organizators o
            JOIN organizators_events oe ON o.organizator_id = oe.organizator_id
            JOIN events e ON oe.event_id = e.event_id
            WHERE o.organizator_id = $1 AND e.event_date > CURRENT_DATE
        ";

        $resultGetInfo = pg_query_params($conn, $getInfo, [$organizatorId]);

        if ($resultGetInfo) {
            // Получаем единственный результат
            $data = pg_fetch_assoc($resultGetInfo);
            if ($data) {
                echo "<div class='card__text'>Текущих мероприятий: " . htmlspecialchars($data["current_events_count"]) . "</div>";
            }
        } else {
            // Более общая ошибка для продакшн среды
            echo "<div class='card__text'>Не удалось получить данные о мероприятиях</div>";
        }
        ?>
    </div>
</div>
<!-- /card -->