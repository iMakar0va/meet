<?php
// Массив месяцев
$months = [
    1 => 'января', 'февраля', 'марта', 'апреля', 'мая', 'июня',
    'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'
];

// Используем DateTime для парсинга и форматирования даты
$date = new DateTime($row['event_date']);
$formattedDate = $date->format('j') . ' ' . $months[intval($date->format('n'))]; // Форматирование даты

// Работа с изображением
$imageSrc = !empty($row["image"])
    ? 'data:image/jpeg;base64,' . base64_encode(pg_unescape_bytea($row["image"]))
    : 'img/profile.jpg';

?>
<div class="card">
    <!-- Использование безопасного URL для изображения -->
    <div class="card__img" style="background-image: url(<?= htmlspecialchars($imageSrc) ?>)"></div>
    <div class="card__content">
        <div class="card__type"><?= htmlspecialchars($row["type"]) ?></div>
        <div class="card__date"><?= htmlspecialchars($formattedDate) ?></div>
        <div class="card__time">
            <?php
            // Использование DateTime для времени
            $startTime = new DateTime($row["start_time"]);
            $endTime = new DateTime($row["end_time"]);
            echo $startTime->format('H:i') . ' - ' . $endTime->format('H:i');
            ?>
        </div>
        <div class="card__city"><?= htmlspecialchars($row["city"]) ?></div>
        <div class="card__title"><?= htmlspecialchars($row["title"]) ?></div>
    </div>
    <button class="btn1" onclick="window.location.href='./php/generate_report.php?event_id=<?= $row['event_id'] ?>'">Получить отчет</button>
    <a href="event.php?event_id=<?= htmlspecialchars($row['event_id']) ?>" class="btn1">Подробнее</a>
</div>
<!-- /card -->
