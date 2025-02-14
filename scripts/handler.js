$(document).ready(function () {
    // Загружаем данные из базы и заполняем форму
    $.ajax({
        url: 'php/get_user.php',
        type: 'GET',
        success: function (response) {
            const res = JSON.parse(response);
            if (res.success) {
                const data = res.data;
                // Заполняем поля формы значениями из базы данных
                $('#first_name').val(data.first_name);
                $('#last_name').val(data.last_name);

                // Устанавливаем выбранный пол
                if (data.gender === 'мужской') {
                    $('#male').prop('checked', true);
                } else if (data.gender === 'женский') {
                    $('#female').prop('checked', true);
                }

                // Форматируем и отображаем дату рождения
                const birthDate = new Date(data.birth_date);
                const day = String(birthDate.getDate()).padStart(2, '0');
                const month = String(birthDate.getMonth() + 1).padStart(2, '0');
                const year = birthDate.getFullYear();

                const formattedDate = `${day}/${month}/${year}`;
                $('#birthDateInput').val(formattedDate);

                // Отображаем картинку, если она есть
                if (data.image) {
                    const imageSrc = 'data:image/jpeg;base64,' + btoa(String.fromCharCode.apply(null, new Uint8Array(data.image.data)));
                    $('#profileImage').attr('src', imageSrc);
                }
            } else {
                alert(res.message);
            }
        },
        error: function () {
            alert('Ошибка при загрузке данных.');
        }
    });

    // Сохранение изменений в базу данных
    $('form').on('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(this);

        $.ajax({
            url: 'php/update_user.php',
            type: 'POST',
            data: formData,
            processData: false,  // Чтобы jQuery не пытался преобразовать FormData
            contentType: false,  // Чтобы браузер сам определял правильный content-type
            success: function (response) {
                const res = JSON.parse(response);
                if (res.success) {
                    alert(res.message);
                    // Обновляем страницу для применения изменений
                    window.location.reload();
                } else {
                    alert(res.message);
                }
            },
            error: function () {
                alert('Ошибка при сохранении данных.');
            }
        });
    });

    // Обработчик для выбора фото профиля
    $('#profilePictureInput').on('change', function (e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                $('#profileImage').attr('src', e.target.result);  // Показываем выбранное изображение
            };
            reader.readAsDataURL(file);
        }
    });

    // Обработчик для отображения имени выбранного файла
    $('.input-file input[type=file]').on('change', function () {
        let file = this.files[0];
        $(this).closest('.input-file').find('.input-file-text').html(file.name);
    });
});

// Формат даты в поле ввода
document.addEventListener('DOMContentLoaded', () => {
    const birthDateInput = document.getElementById('birthDateInput');

    birthDateInput.addEventListener('input', (e) => {
        let value = birthDateInput.value.replace(/[^0-9]/g, '');
        if (value.length > 2) value = value.slice(0, 2) + '/' + value.slice(2);
        if (value.length > 5) value = value.slice(0, 5) + '/' + value.slice(5);
        birthDateInput.value = value.slice(0, 10);
    });

    birthDateInput.addEventListener('blur', () => {
        const regex = /^(0[1-9]|[12][0-9]|3[01])\/(0[1-9]|1[0-2])\/\d{4}$/;
        if (!regex.test(birthDateInput.value)) {
            birthDateInput.value = '';
        }
    });
});
