// Функция для переключения между блоками информации
function toggleForms(formName) {
    const profile = document.querySelector('.lk__profile');
    const lkSetting = document.querySelector('.lk__setting');

    switch (formName) {
        case 'profile':
            profile.style.display = 'block';
            lkSetting.style.display = 'none';
            break;
        case 'lkSetting':
            profile.style.display = 'none';
            lkSetting.style.display = 'block';
        default:
            console.error('Неизвестный блок информации.');
    }
}

// Обновление профиля
document.querySelector('#saveProfile').addEventListener('click', function (e) {
    e.preventDefault();
    const formData = new FormData(document.querySelector('form'));

    fetch('./php/update_user.php', {
        method: 'POST',
        body: formData,
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                window.location.href = './lk.php';
                alert(data.message);
            } else {
                window.location.href = './lk.php';
                alert(data.message);
            }
        })
        .catch((error) => console.error('Ошибка:', error));
});

