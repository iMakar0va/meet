<!doctype html>
<html>

<head>
    <link rel="stylesheet" href="./styles/custom‑dialogs.css">
    <!-- <link rel="stylesheet" href="styles/custom-dialogs.css"> -->
    <script src="./scripts/custom‑dialogs.js"></script>
    <!-- <script src="scripts/custom-dialogs.js" defer></script> -->
</head>

<body>
    <button id="t">Проверка</button>
    <script>
        document.getElementById('t').onclick = async () => {
            const res = await confirm('Работает?');
            await alert('Ответ: ' + res);
        };
    </script>
</body>

</html>