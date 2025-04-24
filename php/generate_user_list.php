<?php
session_start();
require 'conn.php';
require_once('../tcpdf/tcpdf.php');

// Получение ID мероприятия
$eventId = isset($_GET['event_id']) ? intval($_GET['event_id']) : null;
if (!$eventId) {
    die('Не указан ID мероприятия.');
}

// Создание экземпляра TCPDF с горизонтальной ориентацией
$pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Организатор');
$pdf->SetTitle('Список участников мероприятия');
$pdf->SetSubject('Список участников');
$pdf->SetKeywords('PDF, отчет, мероприятие');
$pdf->SetFont('dejavusans', '', 10);
$pdf->AddPage();

// Получение данных о мероприятии
$queryEvent = "SELECT title, event_date FROM events WHERE event_id = $1";
$resultEvent = pg_query_params($conn, $queryEvent, [$eventId]);

if (!$resultEvent) {
    die('Ошибка при получении данных мероприятия: ' . pg_last_error($conn));
}

if ($event = pg_fetch_assoc($resultEvent)) {
    $html = '<h1>Список участников мероприятия: <br>' . htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8') . ' на ' . $event['event_date'] . '</h1>';
} else {
    die('Мероприятие не найдено.');
}

// Получение списка пользователей, записанных на мероприятие
$queryUsers = "SELECT u.user_id, u.first_name, u.last_name, u.email, u.gender, u.birth_date, ue.presense, ue.is_signed
               FROM users u
               JOIN user_events ue ON u.user_id = ue.user_id
               WHERE ue.event_id = $1";
$resultUsers = pg_query_params($conn, $queryUsers, [$eventId]);

if (!$resultUsers) {
    die('Ошибка при получении списка участников: ' . pg_last_error($conn));
}

$html .= '<h3 style="text-align: center;">Список записанных пользователей</h3>';
$html .= '<table border="1" cellpadding="5" cellspacing="0" style="width: 100%; text-align: center;">
    <thead>
        <tr style="background-color:rgb(177, 177, 177);">
            <th style="width: 40px;">ID</th>
            <th style="width: 180px;">Фамилия Имя</th>
            <th style="width: 180px;">Email</th>
            <th style="width: 40px;">Пол</th>
            <th>Дата рождения</th>
            <th>Статус присутствия</th>
            <th>Статус записи</th>
        </tr>
    </thead>
    <tbody>';

while ($user = pg_fetch_assoc($resultUsers)) {
    $presenceStatus = $user['presense'] == 't' ? '✓' : '✘'; // Галочка или крестик
    $signStatus = $user['is_signed'] == 't' ? '✓' : '✘'; // Галочка или крестик

    $html .= sprintf(
        '<tr>
            <td style="width: 40px;">%d</td>
            <td style="width: 180px;">%s</td>
            <td style="width: 180px;">%s</td>
            <td style="width: 40px;">%s</td>
            <td>%s</td>
            <td>%s</td>
            <td>%s</td>
        </tr>',
        $user['user_id'],
        htmlspecialchars($user['last_name'], ENT_QUOTES, 'UTF-8') . ' ' . htmlspecialchars($user['first_name'], ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($user['gender'] == 'M' ? 'Муж' : 'Жен', ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($user['birth_date'], ENT_QUOTES, 'UTF-8'),
        $presenceStatus,
        $signStatus
    );
}

$html .= '</tbody></table>';

// Запись содержимого в PDF
$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

// Отправка PDF-файла
$pdf->Output('participants_report.pdf', 'D'); // D - скачивание файла

// Закрытие соединения
pg_close($conn);
