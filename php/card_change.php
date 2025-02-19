<?php
// Массив месяцев
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

// Используем DateTime для парсинга и форматирования даты
$date = new DateTime($row['event_date']);
$formattedDate = $date->format('j') . ' ' . $months[intval($date->format('n'))]; // Форматирование даты

// Работа с изображением (если оно есть, то возвращаем base64, иначе — путь к изображению)
$imageSrc = !empty($row['image'])
    ? 'data:image/jpeg;base64,' . base64_encode(pg_unescape_bytea($row['image']))
    : 'img/profile.jpg';

?>
<div class="card">
    <div class="card__img" style="background-image: url(<?= htmlspecialchars($imageSrc) ?>)"></div>
    <div class="card__content">
        <div class="card__type"><?= htmlspecialchars($row["type"]) ?></div>
        <div class="card__date"><?= htmlspecialchars($formattedDate) ?></div>
        <div class="card__time">
            <?php
            // Форматируем время с помощью DateTime
            $startTime = new DateTime($row["start_time"]);
            $endTime = new DateTime($row["end_time"]);
            echo $startTime->format('H:i') . '-' . $endTime->format('H:i');
            ?>
        </div>
        <div class="card__city"><?= htmlspecialchars($row["city"]) ?></div>
        <div class="card__title"><?= htmlspecialchars($row["title"]) ?></div>
    </div>
    <a href="event.php?event_id=<?= htmlspecialchars($row['event_id']) ?>" class="btn1">Подробнее</a>
    <a href="#" class="btn1" onclick="confirmCancelEvent(<?= htmlspecialchars($row['event_id']) ?>, '<?= addslashes(htmlspecialchars($row['title'])) ?>')">Отменить мероприятие</a>

    <script>
        function confirmCancelEvent(eventId, eventTitle) {
            if (confirm(`Вы уверены, что хотите отменить мероприятие "${eventTitle}"?`)) {
                fetch('php/cancelEvent.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: 'event_id=' + eventId
                    })
                    .then(response => response.json())
                    .then(data => {
                        alert(data.message);
                        if (data.success) {
                            location.reload(); // Перезагрузка страницы после отмены
                        }
                    })
                    .catch(error => console.error('Ошибка:', error));
            }
        }
    </script>

</div>
<!-- /card -->