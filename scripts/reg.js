// document.getElementById('regForm').addEventListener('submit', function (e) {
//     e.preventDefault();

//     const formData = new FormData(this);
//     const errorBlock = document.getElementById('error');

//     fetch('./php/send_code.php', {
//         method: 'POST',
//         body: formData
//     })
//         .then(response => response.json())
//         .then(data => {
//             if (data.success) {
//                 window.location.href = './verify.php';
//             } else {
//                 errorBlock.style.display = 'block';
//                 errorBlock.textContent = data.message || 'Ошибка регистрации. Попробуйте снова.';
//             }
//         })
//         .catch(error => {
//             errorBlock.style.display = 'block';
//             errorBlock.textContent = error.message || 'Произошла ошибка. Проверьте подключение к интернету.';
//         });
// });
// // ...............
document.getElementById('regForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const lastName = document.getElementById('last_name');
    const firstName = document.getElementById('first_name');
    const email = document.getElementById('email');
    const password = document.getElementById('password_reg');
    const repeatPassword = document.getElementById('repeat_password');
    const birthDate = document.getElementById('birthDateInput');
    const errorBlock = document.getElementById('error');
    let isValid = true;
    let errorMessage = "";

    // Очистка старых ошибок
    document.querySelectorAll('.error-border').forEach(input => {
        input.classList.remove('error-border');
    });
    errorBlock.style.display = 'none';
    errorBlock.textContent = '';

    // Проверка на пустые поля
    [lastName, firstName, email, password, repeatPassword, birthDate].forEach(input => {
        if (!input.value.trim()) {
            isValid = false;
            input.classList.add('error-border');
            errorMessage += `Поле "${input.placeholder || input.name}" не должно быть пустым.\n`;
        }
    });

    // Проверка email
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (email.value.trim() && !emailPattern.test(email.value)) {
        isValid = false;
        email.classList.add('error-border');
        errorMessage += 'Некорректный email.\n';
    }

    // Проверка пароля
    if (password.value.trim() && repeatPassword.value.trim() && password.value !== repeatPassword.value) {
        isValid = false;
        password.classList.add('error-border');
        repeatPassword.classList.add('error-border');
        errorMessage += 'Пароли не совпадают.\n';
    }

    // Пароль меньше 6 символов
    if (password.value.length < 6) {
        isValid = false;
        password.classList.add('error-border');
        errorMessage += 'Длина пароля должна быть больше 6 символов.\n';
    }
    // Проверка на наличие хотя бы одной буквы
    if (!/[a-zA-Z]/.test(password.value)) {
        isValid = false;
        password.classList.add('error-border');
        errorMessage += 'Пароль должен содержать хотя бы одну букву.\n';
    }

    // Проверка на наличие хотя бы одной цифры
    if (!/[0-9]/.test(password.value)) {
        isValid = false;
        password.classList.add('error-border');
        errorMessage += 'Пароль должен содержать хотя бы одну цифру.\n';
    }

    // Проверка на наличие специальных символов
    if (!/[!@#$%^&*(),.?":{}|<>]/.test(password.value)) {
        isValid = false;
        password.classList.add('error-border');
        errorMessage += 'Пароль должен содержать хотя бы один специальный символ.\n';
    }

    // Проверка формата даты ДД/ММ/ГГГГ
    const datePattern = /^(0[1-9]|[12][0-9]|3[01])\/(0[1-9]|1[0-2])\/\d{4}$/;
    if (birthDate.value.trim() && !datePattern.test(birthDate.value)) {
        isValid = false;
        birthDate.classList.add('error-border');
        errorMessage += 'Пожалуйста, укажите дату в формате ДД/ММ/ГГГГ.\n';
    }

    if (!isValid) {
        errorBlock.innerHTML = errorMessage.trim().replace(/\n/g, '<br>'); // Заменяем \n на <br>
        errorBlock.style.display = 'block';
        return;
    }

    // Отправка формы, если валидация успешна
    const formData = new FormData(this);
    fetch('./php/send_code.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = './verify.php';
            } else {
                errorBlock.style.display = 'block';
                errorBlock.textContent = data.message || 'Ошибка регистрации. Попробуйте снова.';
            }
        })
        .catch(error => {
            errorBlock.style.display = 'block';
            errorBlock.textContent = 'Произошла ошибка. Проверьте подключение к интернету.';
        });
});

