<?php
$months = [
    1 => 'января',
    'февраля',
    'марта',
    'апреля',
    'мая',
    'июня',
    'июля',
    'августа',
    'сентября',
    'октября',
    'ноября',
    'декабря'
];
$dateParts = explode('-', $row['event_date']);
$formattedDate = intval($dateParts[2]) . ' ' . $months[intval($dateParts[1])];

$imageSrc = !empty($row["image"])
    ? "data:image/jpeg;base64," . base64_encode(pg_unescape_bytea($row["image"]))
    : "img/profile.jpg"; ?>
<div class="card">
    <div class="card__img" style="background-image: url(<?= $imageSrc ?>)"></div>
    <div class="card__content">
        <div class="card__type"><?= htmlspecialchars($row["type"]) ?></div>
        <div class="card__date"><?= htmlspecialchars($formattedDate) ?></div>
        <div class="card__time">
            <?= substr($row["start_time"], 0, 5) . "-" . substr($row["end_time"], 0, 5) ?>
        </div>
        <div class="card__city"><?= htmlspecialchars($row["city"]) ?></div>
        <div class="card__title"><?= htmlspecialchars($row["title"]) ?></div>
    </div>
    <a href="event.php?event_id=<?= htmlspecialchars($row['event_id']) ?>" class="btn1">Подробнее</a>
</div>
<!-- /card -->