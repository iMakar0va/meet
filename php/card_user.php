<div class="card_organizator">
    <?php
    // Объединение имени и фамилии с безопасной обработкой
    $fullName = htmlspecialchars($row["last_name"] . " " . $row["first_name"]);

    // Форматирование даты с использованием DateTime
    $birthDate = new DateTime($row["birth_date"]);
    $formattedBirthDate = $birthDate->format('d F Y'); // Например, "02 февраля 1990"

    // Массив с русскими месяцами
    $months = [
        1 => 'января',
        2 => 'февраля',
        3 => 'марта',
        4 => 'апреля',
        5 => 'мая',
        6 => 'июня',
        7 => 'июля',
        8 => 'августа',
        9 => 'сентября',
        10 => 'октября',
        11 => 'ноября',
        12 => 'декабря'
    ];

    // Извлекаем месяц и день из даты рождения
    $day = $birthDate->format('d');
    $month = $birthDate->format('m'); // Месяц в числовом формате
    $year = $birthDate->format('Y');

    // Получаем название месяца на русском языке
    $formattedMonth = $months[intval($month)];

    // Собираем окончательный формат даты
    $formattedBirthDate = $day . ' ' . $formattedMonth . ' ' . $year;

    // Обработка email
    $email = htmlspecialchars($row["email"]);
    $userId = htmlspecialchars($row["user_id"]);

    // Получаем статистику по событиям
    $eventStatistics = fetchEventStatistics($conn, $row["user_id"]);
    $countNowEvent = $eventStatistics['upcomingEventCount']; // Текущие мероприятия
    $countPastEvent = $eventStatistics['pastEventCount']; // Пройденные мероприятия
    ?>

    <div class="card__title"><?= $fullName ?></div>
    <div class="card__blocks">
        <div class="card__block">
            <div class="card__item">ID пользователя: <?= $userId ?></div>
            <div class="card__item"><?= $formattedBirthDate ?></div>
            <div class="card__item"><?= $email ?></div>
            <div class="card__item">Текущих мероприятий: <?= $countNowEvent ?></div>
            <div class="card__item">Пройденных мероприятий: <?= $countPastEvent ?></div>
        </div>
    </div>
    <a href="changeUser.php?user_id=<?= $row['user_id'] ?>" class="btn1">Изменить данные</a>
</div>
<!-- /card -->