$(document).ready(function () {
    // Загружаем данные из базы и заполняем форму
    $.ajax({
        url: 'php/get_user.php',
        type: 'GET',
        success: function (response) {
            const res = JSON.parse(response);
            if (res.success) {
                const data = res.data;
                $('#first_name').val(data.first_name);
                $('#last_name').val(data.last_name);

                if (data.gender === 'мужской') {
                    $('#male').prop('checked', true);
                } else if (data.gender === 'женский') {
                    $('#female').prop('checked', true);
                }

                const birthDate = new Date(data.birth_date);
                const day = String(birthDate.getDate()).padStart(2, '0');
                const month = String(birthDate.getMonth() + 1).padStart(2, '0');
                const year = birthDate.getFullYear();

                const formattedDate = `${day}/${month}/${year}`;
                $('#birthDateInput').val(formattedDate);
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

        const formData = $(this).serialize();

        $.ajax({
            url: 'php/update_user.php',
            type: 'POST',
            data: formData,
            success: function (response) {
                const res = JSON.parse(response);
                if (res.success) {
                    alert(res.message);
                } else {
                    alert(res.message);
                }
            },
            error: function () {
                alert('Ошибка при сохранении данных.');
            }
        });
    });

    $('#profilePictureInput').on('change', function (e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                $('#profileImage').attr('src', e.target.result);
            };
            reader.readAsDataURL(file);
        }
    });

    $('#saveProfilePicture').on('click', function () {
        const fileInput = $('#profilePictureInput')[0];

        const formData = new FormData();
        formData.append('profile_picture', fileInput.files[0]);

        $.ajax({
            url: 'php/update_user.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                const res = JSON.parse(response);
                if (res.success) {
                    alert('Фото успешно обновлено.');
                } else {
                    alert(res.message);
                }
            },
            error: function () {
                alert('Ошибка загрузки изображения.');
            }
        });
    });

    $('.input-file input[type=file]').on('change', function () {
        let file = this.files[0];
        $(this).closest('.input-file').find('.input-file-text').html(file.name);
    });
});


// Формат даты
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
