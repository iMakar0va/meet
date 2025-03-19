<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <div id="qrcode"></div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let qrText = "2"; // Тут передавать user_id
            new QRCode(document.getElementById("qrcode"), {
                text: qrText,
                width: 128,
                height: 128
            });
        });
    </script>

</body>

</html>