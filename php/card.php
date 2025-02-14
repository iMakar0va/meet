<?php
// Массив для месяцев
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

// Предполагаем, что $row содержит данные из базы, полученные с использованием PDO
$dateParts = explode('-', $row['event_date']);
$formattedDate = intval($dateParts[2]) . ' ' . $months[intval($dateParts[1])];

// Обработка изображения (если оно существует)
$imageSrc = "img/profile.jpg"; // Значение по умолчанию

if (!empty($row["image"])) {
    // Убедимся, что изображение — это бинарные данные
    $imageData = stream_get_contents($row["image"]);
    if ($imageData !== false) {
        $imageSrc = "data:image/jpeg;base64," . base64_encode($imageData);
    }
}

?>

<div class="card">
    <div class="card__img" style="background-image: url(<?= htmlspecialchars($imageSrc) ?>)"></div>
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