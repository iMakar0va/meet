<div class="card">
    <div class="card__content">
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
        ?>

        <!-- Отображение имени -->
        <div class="card__item"><?= $fullName ?></div>

        <!-- Отображение даты рождения с месяцем на русском -->
        <div class="card__item"><?= $formattedBirthDate ?></div>

        <!-- Отображение email -->
        <div class="card__item"><?= $email ?></div>
    </div>
    <!-- <a href="event.php?event_id=<?= $row['event_id'] ?>" class="btn1">Подробнее</a> -->
</div>
<!-- /card -->
