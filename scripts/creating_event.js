// Сохранение формы мероприятия
document.getElementById('createForm').addEventListener('submit', function (e) {
    e.preventDefault();

    let isValid = true;
    let errorMessage = "";

    const formData = new FormData(this);
    const errorBlock = document.getElementById('error');
    const dateEvent = document.getElementById('date_event');
    const startTime = document.getElementById('start_time');
    const endTime = document.getElementById('end_time');

    // Очистка старых ошибок
    document.querySelectorAll('.error-border').forEach(input => {
        input.classList.remove('error-border');
    });
    errorBlock.style.display = 'none';
    errorBlock.textContent = '';

    // Проверка на пустые поля
    // [lastName, firstName, email, password, repeatPassword, dateEvent].forEach(input => {
    //     if (!input.value.trim()) {
    //         isValid = false;
    //         input.classList.add('error-border');
    //         errorMessage += `Поле "${input.placeholder || input.name}" не должно быть пустым.\n`;
    //     }
    // });

    // Проверка формата даты ДД/ММ/ГГГГ
    const datePattern = /^(0[1-9]|[12][0-9]|3[01])\/(0[1-9]|1[0-2])\/\d{4}$/;
    if (dateEvent.value.trim() && !datePattern.test(dateEvent.value)) {
        isValid = false;
        dateEvent.classList.add('error-border');
        errorMessage += 'Пожалуйста, укажите дату в формате ДД/ММ/ГГГГ.\n';
    } else {
        // Проверка, что дата больше текущей
        const [day, month, year] = dateEvent.value.split('/').map(Number);
        const eventDate = new Date(year, month - 1, day);
        const today = new Date();
        today.setHours(0, 0, 0, 0); // Обнуляем время для корректного сравнения

        if (eventDate < today) {
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

    // Вывод ошибок
    if (!isValid) {
        errorBlock.innerHTML = errorMessage.trim().replace(/\n/g, '<br>'); // Заменяем \n на <br>
        errorBlock.style.display = 'block';
        return;
    }

    fetch('./php/save_creating.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = './lk.php';
            } else {
                errorBlock.style.display = 'block';
                errorBlock.textContent = data.message || 'Ошибка создания. Попробуйте снова.';
            }
        })
        .catch(error => {
            errorBlock.style.display = 'block';
            errorBlock.textContent = error.message + 'Произошла ошибка. Проверьте подключение к интернету.';
        });
});
