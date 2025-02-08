<?php
require 'conn.php';
require_once('../tcpdf/tcpdf.php');

// Получение ID мероприятия
$eventId = isset($_GET['event_id']) ? intval($_GET['event_id']) : null;
if (!$eventId) {
    die('Не указан ID мероприятия.');
}

// Создание экземпляра TCPDF
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Организатор');
$pdf->SetTitle('Отчет по мероприятию');
$pdf->SetSubject('Аналитический отчет');
$pdf->SetKeywords('PDF, отчет, мероприятие');
$pdf->SetFont('dejavusans', '', 12);
$pdf->AddPage();

$html = '<h1>Отчет по мероприятию</h1>';

// Получение данных о мероприятии
$queryEvent = "SELECT * FROM events WHERE event_id = $1";
$resultEvent = pg_query_params($conn, $queryEvent, [$eventId]);

if (!$resultEvent) {
    die('Ошибка при получении данных мероприятия: ' . pg_last_error($conn));
}

if ($event = pg_fetch_assoc($resultEvent)) {
    $html .= '<h3 style="text-align: center;">Информация о мероприятии</h3>';
    $html .= sprintf(
        '<p><b>Название:</b> %s</p>
         <p><b>Тип:</b> %s</p>
         <p><b>Направление:</b> %s</p>
         <p><b>Дата:</b> %s</p>
         <p><b>Время:</b> %s - %s</p>
         <p><b>Адрес:</b> %s, %s</p>
         <p><b>Телефон:</b> %s</p>
         <p><b>Email:</b> %s</p>',
        htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($event['type'], ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($event['topic'], ENT_QUOTES, 'UTF-8'),
        $event['event_date'],
        htmlspecialchars($event['start_time'], ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($event['end_time'], ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($event['city'], ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($event['address'], ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($event['phone'], ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($event['email'], ENT_QUOTES, 'UTF-8')
    );
} else {
    die('Мероприятие не найдено.');
}

// Получение данных об организаторе
$queryOrganizer = "SELECT o.name, o.phone_number, o.email
                   FROM organizators_events oe
                   JOIN organizators o ON o.organizator_id = oe.organizator_id
                   WHERE oe.event_id = $1";

$resultOrganizer = pg_query_params($conn, $queryOrganizer, [$eventId]);

if (!$resultOrganizer) {
    die('Ошибка при получении данных организатора: ' . pg_last_error($conn));
}

if ($organizer = pg_fetch_assoc($resultOrganizer)) {
    $html .= '<h3 style="text-align: center;">Организатор</h3>';
    $html .= sprintf(
        '<p><b>Имя:</b> %s</p>
         <p><b>Телефон:</b> %s</p>
         <p><b>Email:</b> %s</p>',
        htmlspecialchars($organizer['name'], ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($organizer['phone_number'], ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($organizer['email'], ENT_QUOTES, 'UTF-8')
    );
}

// Получение количества участников
$queryParticipants = "SELECT COUNT(*) FROM user_events WHERE event_id = $1";
$resultParticipants = pg_query_params($conn, $queryParticipants, [$eventId]);
$countParticipants = pg_fetch_result($resultParticipants, 0, 0);

$html .= '<h3 style="text-align: center;">Статистика участников</h3>';
$html .= "<p><b>Количество участников:</b> $countParticipants</p>";

// Если участники есть, получаем гендерное и возрастное распределение
if ($countParticipants > 0) {
    // Гендерное распределение
    $queryGender = "SELECT gender, COUNT(*) * 100.0 / $1 AS percentage
                    FROM users u
                    JOIN user_events ue ON ue.user_id = u.user_id
                    WHERE ue.event_id = $2
                    GROUP BY gender";

    $resultGender = pg_query_params($conn, $queryGender, [$countParticipants, $eventId]);

    while ($genderData = pg_fetch_assoc($resultGender)) {
        $html .= sprintf(
            '<p><b>%s:</b> %.0f%%</p>',
            htmlspecialchars(ucfirst($genderData['gender']), ENT_QUOTES, 'UTF-8'),
            round($genderData['percentage'])
        );
    }

    // Возрастное распределение
    $ageRanges = [
        'Младше 18' => '< 18',
        'От 18 до 25' => 'BETWEEN 18 AND 25',
        'От 26 до 45' => 'BETWEEN 26 AND 45',
        'От 46 до 65' => 'BETWEEN 46 AND 65',
        'Старше 65' => '> 65'
    ];

    $html .= '<p><b>Распределение по возрасту:</b></p>';

    foreach ($ageRanges as $label => $condition) {
        $queryAge = "SELECT COUNT(*) * 100.0 / $1
                     FROM users u
                     JOIN user_events ue ON ue.user_id = u.user_id
                     WHERE ue.event_id = $2
                     AND EXTRACT(YEAR FROM AGE(CURRENT_DATE, birth_date)) $condition";

        $resultAge = pg_query_params($conn, $queryAge, [$countParticipants, $eventId]);
        $agePercentage = round(pg_fetch_result($resultAge, 0, 0));

        $html .= sprintf('<p>   %s: %d%%</p>', htmlspecialchars($label, ENT_QUOTES, 'UTF-8'), $agePercentage);
    }
}

// Запись содержимого в PDF
$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

// Отправка PDF-файла
$pdf->Output('event_report.pdf', 'D'); // D - скачивание файла

// Закрытие соединения
pg_close($conn);
