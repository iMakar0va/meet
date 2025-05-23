// Формат даты ДД/ММ/ГГ
document.addEventListener('DOMContentLoaded', () => {
    const date = document.getElementById('date_start_work');

    date.addEventListener('input', (e) => {
        let value = date.value.replace(/[^0-9]/g, '');
        if (value.length > 2) value = value.slice(0, 2) + '/' + value.slice(2);
        if (value.length > 5) value = value.slice(0, 5) + '/' + value.slice(5);
        date.value = value.slice(0, 10);
    });
});

//Формат телефона
document.addEventListener('DOMContentLoaded', () => {
    const phone = document.getElementById('phone_number');
    phone.addEventListener('input', (e) => {
        let value = phone.value.replace(/[^0-9]/g, '');
        if (value.length > 1) value = '+7 (' + value.slice(1);
        if (value.length > 7) value = value.slice(0, 7) + ') ' + value.slice(7);
        if (value.length > 12) value = value.slice(0, 12) + '-' + value.slice(12);
        if (value.length > 15) value = value.slice(0, 15) + '-' + value.slice(15);
        if (value.length > 18) value = value.slice(0, 18);
        phone.value = value;
    });
});
// Обработчик сохранения изменений профиля организатора
$(document).ready(function () {
    $("#changeRequestForm").on("submit", function (event) {
        event.preventDefault();

        let formData = new FormData(this);

        let isValid = true;
        let errorMessage = "";
        const errorBlock = document.getElementById('error');
        const dateStartWork = document.getElementById('date_start_work');
        const phone = document.getElementById('phone_number');

        // Очистка старых ошибок
        document.querySelectorAll('.error-border').forEach(input => {
            input.classList.remove('error-border');
        });
        errorBlock.style.display = 'none';
        errorBlock.textContent = '';

        // Проверка формата даты ДД/ММ/ГГГГ
        const datePattern = /^(0[1-9]|[12][0-9]|3[01])\/(0[1-9]|1[0-2])\/\d{4}$/;
        if (dateStartWork.value.trim() && !datePattern.test(dateStartWork.value)) {
            isValid = false;
            dateStartWork.classList.add('error-border');
            errorMessage += 'Пожалуйста, укажите дату в формате ДД/ММ/ГГГГ.\n';
        } else {
            // Проверка существования даты
            const [day, month, year] = dateStartWork.value.split('/').map(Number);
            const eventDate = new Date(year, month - 1, day);
            const today = new Date();
            today.setHours(0, 0, 0, 0); // Обнуляем время для корректного сравнения

            if (
                eventDate.getFullYear() !== year ||
                eventDate.getMonth() !== month - 1 ||
                eventDate.getDate() !== day
            ) {
                isValid = false;
                dateStartWork.classList.add('error-border');
                errorMessage += 'Некорректная дата. Такой даты не существует.\n';
            } else if (eventDate >= today) {
                isValid = false;
                dateStartWork.classList.add('error-border');
                errorMessage += 'Дата основания должна быть раньше сегодняшнего дня.\n';
            }
        }

        // Проверка телефона
        if (phone.value.length !== 18) {
            isValid = false;
            phone.classList.add('error-border');
            errorMessage += 'Телефон должен быть в формате +7 (XXX) XXX-XX-XX.\n';
        }

        // Вывод ошибок
        if (!isValid) {
            errorBlock.innerHTML = errorMessage.trim().replace(/\n/g, '<br>');
            errorBlock.style.display = 'block';
            return;
        }

        // Обработчик изменений профиля организатора
        $.ajax({
            url: "php/update_organizator.php",
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (response) {
                if (response.success) {
                    customAlert(response.message, function () {
                        window.location.href = "listOrganizatorActive_admin.php";
                    });
                } else {
                    customAlert("Ошибка: " + response.message);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                customAlert("Ошибка AJAX: " + textStatus + " - " + errorThrown);
                console.log(jqXHR.responseText); // Логирование ошибки в консоль
            }
        });
    });
});
