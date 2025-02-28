<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=400px, initial-scale=1.0">
    <link rel="stylesheet" href="styles/auth.css">
    <title>Восстановление пароля</title>
</head>

<body>
    <?php
    session_start();
    require './php/header.php';
    ?>
    <div class="form">
        <div class="form-title title0">Восстановление пароля</div>
        <form id="resetPasswordForm">
            <div class="text-field__icon text-field__icon_email">
                <input id="email" name="email" class="text-field__input title2" type="email" placeholder="Почта" required>
            </div>
            <button type="submit" class="btn1">Отправить</button>
            <div id="error" class="error title2"></div>
            <div id="success" class="success title2" style="color: green; display: none;"></div>
            <a href="./auth.php" class="title3" id="link-auth">Вернуться назад</a>
        </form>
    </div>
    <?php
    require './php/footer.php';
    ?>
    <script>
        // Обработчик сброса пароля
        document.getElementById('resetPasswordForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const email = document.getElementById('email').value;
            const errorBlock = document.getElementById('error');
            const successBlock = document.getElementById('success');
            fetch('./php/reset_password_process.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        email
                    }),
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        successBlock.textContent = data.message;
                        successBlock.style.display = 'block';
                    } else {
                        errorBlock.textContent = data.message;
                        errorBlock.style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Ошибка:', error);
                    errorBlock.textContent = 'Произошла ошибка. Попробуйте снова.';
                    errorBlock.style.display = 'block';
                });
        });
    </script>
</body>

</html>