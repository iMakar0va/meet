document.getElementById('regForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const formData = new FormData(this);
    const errorBlock = document.getElementById('error');

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
            errorBlock.textContent = error.message || 'Произошла ошибка. Проверьте подключение к интернету.';
        });
});