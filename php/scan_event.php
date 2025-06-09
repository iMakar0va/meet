<?php
session_start();
require '../php/conn.php';

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header("Location: ../lk.php");
    exit();
}

$userId = $_SESSION['user_id'];

$eventId = isset($_GET['event_id']) ? intval($_GET['event_id']) : null;
if (!$eventId) {
    die("Ошибка: event_id не указан.");
}

// Проверка, является ли пользователь организатором мероприятия
$queryOrganizer = "SELECT 1 FROM events WHERE event_id = $1 AND organizator_id = $2";
$resultOrganizer = pg_query_params($conn, $queryOrganizer, [$eventId, $userId]);

if (!$resultOrganizer || pg_num_rows($resultOrganizer) === 0) {
    // Если пользователь не организатор этого мероприятия
    header("Location: ../lk.php");
    exit();
}

$getTitle = "select * from events where event_id = $1";
$getTitleResult = pg_query_params($conn, $getTitle, [$eventId]);
$row = pg_fetch_assoc($getTitleResult);
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Сканер QR-кодов</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 20px;
        }

        #reader {
            width: 100%;
            max-width: 400px;
            margin: auto;
        }

        #result {
            font-size: 20px;
            margin-top: 20px;
            font-weight: bold;
        }

        .back-btn,
        .scan-next-btn {
            margin-top: 20px;
            padding: 10px 20px;
            font-size: 18px;
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
        }

        .scan-next-btn {
            background-color: rgb(0, 83, 173);
        }
    </style>
</head>

<body>

    <h2>Название мероприятия: <?= htmlspecialchars($row['title']) ?></h2>
    <h2>Сканируйте QR-код</h2>
    <div id="reader"></div>
    <div id="result">Ожидание сканирования...</div>
    <button class="back-btn" onclick="window.history.back();">Выход</button>
    <!-- Кнопка для перезагрузки страницы и повторного сканирования -->
    <button class="scan-next-btn" onclick="reloadScanner();">Сканировать следующий</button>

    <script>
        function reloadScanner() {
            location.reload(); // Просто перезагружаем страницу
        }
    </script>

    <script>
        function onScanSuccess(decodedText, decodedResult) {
            document.getElementById("result").innerText = "Отсканировано: " + decodedText;

            // AJAX-запрос для отметки присутствия
            $.post("mark_attendance.php", {
                user_id: decodedText,
                event_id: <?= $eventId ?>
            }, function(response) {
                $("#result").html(response);
            }).fail(function() {
                $("#result").html("Ошибка при отправке данных!");
            });

            // Остановить сканирование после успешного чтения
            html5QrCode.stop().then(() => {
                console.log("Сканирование остановлено.");
            }).catch(err => console.error("Ошибка остановки:", err));
        }

        function onScanError(errorMessage) {
            console.warn("Ошибка сканирования:", errorMessage);
        }

        // Запускаем камеру и сканер
        let html5QrCode = new Html5Qrcode("reader");
        html5QrCode.start({
                facingMode: "environment"
            }, // Использует основную камеру (сзади)
            {
                fps: 10,
                qrbox: {
                    width: 250,
                    height: 250
                }
            },
            onScanSuccess,
            onScanError
        );
    </script>

</body>

</html>