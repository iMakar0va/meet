document.getElementById('authForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const errorBlock = document.getElementById('error');

    fetch('./php/check_auth.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ email, password }),
    })
        .then((response) => {
            if (!response.ok) {
                throw new Error('Ошибка на сервере');
            }
            return response.json();
        })
        .then((data) => {
            if (data.success) {
                window.location.href = './lk.php';  // Перенаправление после успешной авторизации
            } else {
                errorBlock.textContent = data.message;  // Вывод ошибки
                errorBlock.style.display = 'block';
            }
        })
        .catch((error) => {
            console.error('Ошибка:', error);
            errorBlock.textContent = 'Произошла ошибка. Попробуйте снова.';  // Обработка ошибок
            errorBlock.style.display = 'block';
        });
});

// Обработчик для ссылки "Восстановить пароль"
document.getElementById('resetPasswordLink').addEventListener('click', function (e) {
    e.preventDefault();
    window.location.href = './reset_password.php';  // Переход на страницу восстановления пароля
});
