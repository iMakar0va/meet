// Формат даты ДД/ММ/ГГ
document.addEventListener('DOMContentLoaded', () => {
    const date = document.getElementById('date_event');

    date.addEventListener('input', (e) => {
        let value = date.value.replace(/[^0-9]/g, '');
        if (value.length > 2) value = value.slice(0, 2) + '/' + value.slice(2);
        if (value.length > 5) value = value.slice(0, 5) + '/' + value.slice(5);
        date.value = value.slice(0, 10);
    });
});

// Формат время ЧЧ:ММ
document.addEventListener('DOMContentLoaded', () => {
    const time = document.getElementById('start_time');
    time.addEventListener('input', (e) => {
        let value = time.value.replace(/[^0-9]/g, '');
        if (value.length > 2) value = value.slice(0, 2) + ':' + value.slice(2);
        if (value.length > 5) value = value.slice(0, 5);
        time.value = value;
    });
});
document.addEventListener('DOMContentLoaded', () => {
    const time = document.getElementById('end_time');
    time.addEventListener('input', (e) => {
        let value = time.value.replace(/[^0-9]/g, '');
        if (value.length > 2) value = value.slice(0, 2) + ':' + value.slice(2);
        if (value.length > 5) value = value.slice(0, 5);
        time.value = value;
    });
});

//Формат телефона
document.addEventListener('DOMContentLoaded', () => {
    const phone = document.getElementById('phone');
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
// Обработчик сохранения изменений мероприятия
$(document).ready(function () {
    $("#editEventForm").on("submit", function (event) {
        event.preventDefault();

        let formData = new FormData(this);

        let isValid = true;
        let errorMessage = "";
        const errorBlock = document.getElementById('error');
        const dateEvent = document.getElementById('date_event');
        const startTime = document.getElementById('start_time');
        const endTime = document.getElementById('end_time');
        const phone = document.getElementById('phone');

        // Очистка старых ошибок
        document.querySelectorAll('.error-border').forEach(input => {
            input.classList.remove('error-border');
        });
        errorBlock.style.display = 'none';
        errorBlock.textContent = '';

        // Проверка формата даты ДД/ММ/ГГГГ
        const datePattern = /^(0[1-9]|[12][0-9]|3[01])\/(0[1-9]|1[0-2])\/\d{4}$/;
        if (dateEvent.value.trim() && !datePattern.test(dateEvent.value)) {
            isValid = false;
            dateEvent.classList.add('error-border');
            errorMessage += 'Пожалуйста, укажите дату в формате ДД/ММ/ГГГГ.\n';
        } else {
            // Проверка существования даты
            const [day, month, year] = dateEvent.value.split('/').map(Number);
            const eventDate = new Date(year, month - 1, day);
            const today = new Date();
            today.setHours(0, 0, 0, 0); // Обнуляем время для корректного сравнения

            if (
                eventDate.getFullYear() !== year ||
                eventDate.getMonth() !== month - 1 ||
                eventDate.getDate() !== day
            ) {
                isValid = false;
                dateEvent.classList.add('error-border');
                errorMessage += 'Некорректная дата. Такой даты не существует.\n';
            } else if (eventDate < today) {
                isValid = false;
                dateEvent.classList.add('error-border');
                errorMessage += 'Дата мероприятия должна быть позже сегодняшнего дня.\n';
            }
        }


        // Проверка формата времени
        const timePattern = /^(?:[01]\d|2[0-3]):[0-5]\d$/;
        if (startTime.value && !timePattern.test(startTime.value)) {
            isValid = false;
            startTime.classList.add('error-border');
            errorMessage += 'Время начала должно быть в формате ЧЧ:ММ (00:00 – 23:59).\n';
        }
        if (endTime.value && !timePattern.test(endTime.value)) {
            isValid = false;
            endTime.classList.add('error-border');
            errorMessage += 'Время окончания должно быть в формате ЧЧ:ММ (00:00 – 23:59).\n';
        }

        // Проверка, что start_time < end_time
        if (startTime.value && endTime.value && timePattern.test(startTime.value) && timePattern.test(endTime.value)) {
            const [startHours, startMinutes] = startTime.value.split(':').map(Number);
            const [endHours, endMinutes] = endTime.value.split(':').map(Number);
            if (startHours > endHours || (startHours === endHours && startMinutes >= endMinutes)) {
                isValid = false;
                startTime.classList.add('error-border');
                endTime.classList.add('error-border');
                errorMessage += 'Время начала должно быть раньше времени окончания.\n';
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
        // Обработчик изменения меропрития
        $.ajax({
            url: "php/update_event.php",
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (response) {
                if (response.success) {
                    alert(response.message);
                    window.location.href = "listEventActive_admin.php";
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