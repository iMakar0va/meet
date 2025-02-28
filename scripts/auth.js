// Скрыть показать пароль
function show_hide_password(target) {
    var input = document.getElementById('password');
    if (input.getAttribute('type') == 'password') {
        target.classList.add('view');
        input.setAttribute('type', 'text');
    } else {
        target.classList.remove('view');
        input.setAttribute('type', 'password');
    }
    return false;
}

// Обработчик авторизации
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
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                window.location.href = './lk.php';
            } else {
                errorBlock.textContent = data.message;
                errorBlock.style.display = 'block';
            }
        })
        .catch((error) => {
            console.error('Ошибка:', error);
            errorBlock.textContent = error.message || 'Произошла ошибка. Попробуйте снова.';
            errorBlock.style.display = 'block';
        });
});

// Обработчик для ссылки "Восстановить пароль"
document.getElementById('resetPasswordLink').addEventListener('click', function (e) {
    e.preventDefault();
    window.location.href = './reset_password.php';
});