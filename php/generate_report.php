<?php
session_start();
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
$pdf->SetFont('dejavusans', '', 10);
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

// Получение количества всех участников
$queryParticipants = "SELECT COUNT(*) FROM user_events WHERE event_id = $1";
$resultParticipants = pg_query_params($conn, $queryParticipants, [$eventId]);
$countParticipants = pg_fetch_result($resultParticipants, 0, 0);

// Получение количества посетивших участников
$queryParticipantsPast = "SELECT COUNT(*) FROM user_events WHERE event_id = $1 and presense = true and is_signed = true";
$resultParticipantsPast = pg_query_params($conn, $queryParticipantsPast, [$eventId]);
$countParticipantsPast = pg_fetch_result($resultParticipantsPast, 0, 0);

// Получение количества отмененных участников
$queryParticipantsCancelled = "SELECT COUNT(*) FROM user_events WHERE event_id = $1 and is_signed = false";
$resultParticipantsCancelled = pg_query_params($conn, $queryParticipantsCancelled, [$eventId]);
$countParticipantsCancelled = pg_fetch_result($resultParticipantsCancelled, 0, 0);

// Получение количества непришедших участников
$queryParticipantsNo = "SELECT COUNT(*) FROM user_events WHERE event_id = $1 and is_signed = true and presense = false";
$resultParticipantsNo = pg_query_params($conn, $queryParticipantsNo, [$eventId]);
$countParticipantsNo = pg_fetch_result($resultParticipantsNo, 0, 0);

$html .= '<h3 style="text-align: center;">Статистика участников</h3>';
$html .= "<p><b>Количество записанных участников на мероприятие:</b> $countParticipants человек(а)</p>";
$html .= "<p><b>Количество посетивших участников мероприятие:</b> $countParticipantsPast человек(а)</p>";
$html .= "<p><b>Количество участников непрешедших на мероприятие:</b> $countParticipantsNo человек(а)</p>";
$html .= "<p><b>Количество участников передумавших идти:</b> $countParticipantsCancelled человек(а)</p>";

// Если участники есть, получаем гендерное и возрастное распределение
if ($countParticipantsPast > 0) {
    // Гендерное распределение
    $queryGender = "SELECT gender, COUNT(*) * 100.0 / $1 AS percentage
                    FROM users u
                    JOIN user_events ue ON ue.user_id = u.user_id
                    WHERE ue.event_id = $2 and presense = true and is_signed = true
                    GROUP BY gender";

    $resultGender = pg_query_params($conn, $queryGender, [$countParticipantsPast, $eventId]);

    $html .= '<h3 style="text-align: center;">Гендерное распределение</h3>';
    $html .= '<table border="1" cellpadding="4" cellspacing="0" style="width: 100%; text-align: center;">
        <thead>
            <tr style="background-color:rgb(177, 177, 177);">
                <th>Пол</th>
                <th>Процент участников</th>
            </tr>
        </thead>
        <tbody>';

    while ($genderData = pg_fetch_assoc($resultGender)) {
        $html .= sprintf(
            '<tr>
            <td>%s</td>
            <td>%.0f%%</td>
        </tr>',
            htmlspecialchars(ucfirst($genderData['gender']), ENT_QUOTES, 'UTF-8'),
            round($genderData['percentage'])
        );
    }

    $html .= '</tbody></table>';


    // Возрастное распределение
    $ageRanges = [
        'От 18 до 25' => 'BETWEEN 18 AND 25',
        'От 26 до 45' => 'BETWEEN 26 AND 45',
        'От 46 до 65' => 'BETWEEN 46 AND 65',
        'Старше 65' => '> 65'
    ];

    $ageData = [];

    foreach ($ageRanges as $label => $condition) {
        $queryAge = "SELECT COUNT(*) * 100.0 / $1
                 FROM users u
                 JOIN user_events ue ON ue.user_id = u.user_id
                 WHERE ue.event_id = $2 and presense = true and is_signed = true
                 AND EXTRACT(YEAR FROM AGE(CURRENT_DATE, birth_date)) $condition";

        $resultAge = pg_query_params($conn, $queryAge, [$countParticipantsPast, $eventId]);
        $agePercentage = round(pg_fetch_result($resultAge, 0, 0));

        $ageData[] = [
            'label' => $label,
            'percentage' => $agePercentage
        ];
    }

    // Определяем максимальное значение для выделения
    $maxPercentage = max(array_column($ageData, 'percentage'));

    $html .= '<h3 style="text-align: center;">Возрастное распределение</h3>';
    $html .= '<table border="1" cellpadding="5" cellspacing="0" style="width: 100%; text-align: center;">
            <thead>
                <tr style="background-color:rgb(177, 177, 177);">
                    <th>Возрастная категория</th>
                    <th>Процент участников</th>
                </tr>
            </thead>
            <tbody>';

    foreach ($ageData as $row) {
        $highlight = ($row['percentage'] == $maxPercentage) ? 'background-color:rgb(255, 255, 106);' : '';
        $html .= sprintf(
            '<tr style="%s">
            <td>%s</td>
            <td>%d%%</td>
        </tr>',
            $highlight,
            htmlspecialchars($row['label'], ENT_QUOTES, 'UTF-8'),
            $row['percentage']
        );
    }

    $html .= '</tbody></table>';
}

// Запись содержимого в PDF
$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

// Отправка PDF-файла
$pdf->Output('event_report.pdf', 'D'); // D - скачивание файла

// Закрытие соединения
pg_close($conn);
