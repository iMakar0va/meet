// Формат даты ДД/ММ/ГГ
document.addEventListener('DOMContentLoaded', () => {
    const date = document.getElementById('birth_date');

    date.addEventListener('input', (e) => {
        let value = date.value.replace(/[^0-9]/g, '');
        if (value.length > 2) value = value.slice(0, 2) + '/' + value.slice(2);
        if (value.length > 5) value = value.slice(0, 5) + '/' + value.slice(5);
        date.value = value.slice(0, 10);
    });
});
// Скрыть/показать пароль
function show_hide_password(target, inputId) {
    var input = document.getElementById(inputId);
    if (input.getAttribute('type') == 'password') {
        target.classList.add('view');
        input.setAttribute('type', 'text');
    } else {
        target.classList.remove('view');
        input.setAttribute('type', 'password');
    }
    return false;
}
// Обработчик сохранения изменений мероприятия
$(document).ready(function () {
    $("#editUserForm").on("submit", function (event) {
        event.preventDefault();

        let formData = new FormData(this);

        let isValid = true;
        let errorMessage = "";
        const errorBlock = document.getElementById('error');
        const birthDate = document.getElementById('birth_date');

        const oldPassword = document.getElementById('old_password');
        const newPassword = document.getElementById('new_password');
        const repeatPassword = document.getElementById('repeat_password');

        // Очистка старых ошибок
        document.querySelectorAll('.error-border').forEach(input => {
            input.classList.remove('error-border');
        });
        errorBlock.style.display = 'none';
        errorBlock.textContent = '';

        // Проверка смены пароля (если хотя бы одно поле заполнено)
        if (oldPassword.value.trim() || newPassword.value.trim() || repeatPassword.value.trim()) {
            // Проверяем, что все три поля заполнены
            if (!oldPassword.value.trim() || !newPassword.value.trim() || !repeatPassword.value.trim()) {
                isValid = false;
                oldPassword.classList.add('error-border');
                newPassword.classList.add('error-border');
                repeatPassword.classList.add('error-border');
                errorMessage += 'Ошибка: необходимо заполнить все поля для смены пароля.\n';
            }

            // Проверка нового пароля
            if (newPassword.value !== repeatPassword.value) {
                isValid = false;
                newPassword.classList.add('error-border');
                repeatPassword.classList.add('error-border');
                errorMessage += 'Пароли не совпадают.\n';
            }

            if (newPassword.value.length < 6) {
                isValid = false;
                newPassword.classList.add('error-border');
                errorMessage += 'Длина пароля должна быть не менее 6 символов.\n';
            }

            if (!/[a-zA-Z]/.test(newPassword.value)) {
                isValid = false;
                newPassword.classList.add('error-border');
                errorMessage += 'Пароль должен содержать хотя бы одну букву.\n';
            }

            if (!/[0-9]/.test(newPassword.value)) {
                isValid = false;
                newPassword.classList.add('error-border');
                errorMessage += 'Пароль должен содержать хотя бы одну цифру.\n';
            }

            if (!/[!@#$%^&*(),.?":{}|<>]/.test(newPassword.value)) {
                isValid = false;
                newPassword.classList.add('error-border');
                errorMessage += 'Пароль должен содержать хотя бы один специальный символ.\n';
            }
        }
        // Проверка возраста
        const CURRENT_DATE = new Date(); // Получаем текущую дату
        if (birthDate.value.trim()) {
            const [day, month, year] = birthDate.value.split('/').map(Number); // Разбираем строку в день, месяц, год
            const birthDateValue = new Date(year, month - 1, day); // Создаем объект Date с правильным форматом

            // Проверка существования даты
            if (
                birthDateValue.getFullYear() !== year ||
                birthDateValue.getMonth() !== month - 1 ||
                birthDateValue.getDate() !== day
            ) {
                isValid = false;
                birthDate.classList.add('error-border');
                errorMessage += 'Некорректная дата. Такой даты не существует.\n';
            } else if (birthDateValue > CURRENT_DATE) {
                isValid = false;
                birthDate.classList.add('error-border');
                errorMessage += 'Дата рождения должна быть раньше сегодняшнего дня.\n';
            } else {
                // Вычисление возраста
                let age = CURRENT_DATE.getFullYear() - birthDateValue.getFullYear();
                const monthDiff = CURRENT_DATE.getMonth() - birthDateValue.getMonth();

                // Если месяц меньше или равно, но день больше
                if (monthDiff < 0 || (monthDiff === 0 && CURRENT_DATE.getDate() < birthDateValue.getDate())) {
                    age--; // Если еще не достиг дня рождения в этом году
                }

                // Проверка на возраст
                if (age < 18) {
                    isValid = false;
                    birthDate.classList.add('error-border');
                    errorMessage += 'Пользователи младше 18 лет не доступны.\n';
                }
            }
        }

        if (!isValid) {
            errorBlock.innerHTML = errorMessage.trim().replace(/\n/g, '<br>');
            errorBlock.style.display = 'block';
            return;
        }
        // Обработчик изменения меропрития
        $.ajax({
            url: "php/update_user.php",
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (response) {
                if (response.success) {
                    alert(response.message);
                    window.history.back();
                    // window.location.href = "listUser_admin.php";
                } else {
                    alert("Ошибка: " + response.message);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                alert("Ошибка AJAX: " + textStatus + " - " + errorThrown);
                console.log(jqXHR.responseText); // Логирование ошибки в консоль
            }
        });
    });
});


// Отображение фото меропрития
var dt = new DataTransfer();

$('.input-file input[type=file]').on('change', function () {
    let $files_list = $(this).closest('.input-file').next();
    $files_list.empty();
    $('#removeImageField').val('0');

    for (var i = 0; i < this.files.length; i++) {
        let file = this.files.item(i);
        dt.items.add(file);

        let reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onloadend = function () {
            let new_file_input = '<div class="input-file-list-item">' +
                '<img class="input-file-list-img" src="' + reader.result + '">' +
                '<a href="#" onclick="removeFilesItem(this); return false;" class="input-file-list-remove">x</a>' +
                '</div>';
            $files_list.append(new_file_input);
        };
    }
    this.files = dt.files;
});
// Удаление фото меропрития
function removeFilesItem(target) {
    let input = $(target).closest('.input-file-row').find('input[type=file]');
    $(target).closest('.input-file-list-item').remove();
    dt.items.clear();
    input[0].files = dt.files;

    $('#removeImageField').val('1');
}
