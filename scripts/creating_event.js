// Сохранение формы мероприятия
document.getElementById('createForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const formData = new FormData(this);
    const errorBlock = document.getElementById('error');

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
