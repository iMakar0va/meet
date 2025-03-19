<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Сканер QR-кодов</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js"></script>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 20px; }
        #reader { width: 100%; max-width: 400px; margin: auto; }
        #result { font-size: 20px; margin-top: 20px; font-weight: bold; }
    </style>
</head>
<body>

    <h2>Сканируйте QR-код</h2>
    <div id="reader"></div>
    <div id="result">Ожидание сканирования...</div>

    <script>
        function onScanSuccess(decodedText, decodedResult) {
            document.getElementById("result").innerText = "Ваш user_id: " + decodedText;
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
        html5QrCode.start(
            { facingMode: "environment" }, // Использует основную камеру (сзади)
            { fps: 10, qrbox: { width: 250, height: 250 } },
            onScanSuccess,
            onScanError
        );
    </script>

</body>
</html>
