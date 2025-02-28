<?php
session_start();
require 'php/conn.php';

if (!isset($_GET['organizator_id'])) {
    die("Ошибка: организатор не найден.");
}

$organizatorId = intval($_GET['organizator_id']);
$_SESSION['organizator_id'] = $organizatorId;

// Получаем данные мероприятия
$query = "SELECT * FROM organizators WHERE organizator_id = $1";
$result = pg_query_params($conn, $query, [$organizatorId]);
$organizator = pg_fetch_assoc($result);

if (!$organizator) {
    die("Ошибка: мероприятие не найдено.");
}

$dateFormatted = date("d/m/Y", strtotime($organizator['date_start_work']));
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <link rel="stylesheet" href="styles/lk.css">
    <link rel="stylesheet" href="styles/forma.css">
    <link rel="stylesheet" href="styles/media/media_lk.css">
    <title>Личный кабинет</title>
    <!-- <script src="scripts/handler_event.js" defer></script> -->
    <style>
        .error-border {
            border: 3px solid rgb(202, 32, 17);
        }
    </style>
</head>

<body>
    <?php
    require './php/header.php';
    require './php/conn.php';
    $organizatorId = intval($_GET['organizator_id']);
    $_SESSION['organizator_id'] = $organizatorId;
    ?>
    <div class="container">
        <div class="lk">
            <?php require 'php/lk/lk_menu.php'; ?>
            <div class="lk__profile">
                <div class="title1">Редактирование профиля организатора</div>
                <form id="changeRequestForm">
                    <input type="hidden" name="organizator_id" value="<?= $organizatorId ?>">
                    <div class="form__group">
                        <input id="name" name="name" class="input title2" type="text" value="<?= htmlspecialchars($organizator['name']) ?>" placeholder=" " required>
                        <label class="label title2" for="">Название организации</label>
                    </div>
                    <div class="form__group">
                        <input id="phone_number" name="phone_number" class="input title2" type="text" value="<?= htmlspecialchars($organizator['phone_number']) ?>" placeholder=" " required>
                        <label class="label title2" for="">Номер телефона</label>
                    </div>
                    <div class="form__group">
                        <input id="date_start_work" name="date_start_work" class="input title2" type="text" value="<?= $dateFormatted ?>" placeholder=" " required>
                        <label class="label title2" for="">Дата начала деятельности</label>
                    </div>
                    <div class="form__group">
                        <textarea class="input textarea title2" id="description" name="description" rows="4" placeholder="Описание деятельности организации"><?= htmlspecialchars($organizator['description']) ?></textarea>
                    </div>
                    <div id="error" class="error title2" style="display: none;"></div>
                    <button class="btn1 title2" type="submit">Сохранить изменения</button>
                </form>
            </div>
            <!-- /lk__profile -->
        </div>
        <!-- /lk -->
        <?php
        pg_close($conn);
        ?>
    </div>
    <!-- /container -->


    <?php
    require './php/footer.php';
    ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
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

        $(document).ready(function() {
            $("#changeRequestForm").on("submit", function(event) {
                event.preventDefault();

                let formData = new FormData(this);

                let isValid = true;
                let errorMessage = "";
                const errorBlock = document.getElementById('error');
                const dateEvent = document.getElementById('date_start_work');

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
                    } else if (eventDate >= today) {
                        isValid = false;
                        dateEvent.classList.add('error-border');
                        errorMessage += 'Дата регистрации должна быть раньше сегодняшнего дня.\n';
                    }
                }

                // Вывод ошибок
                if (!isValid) {
                    errorBlock.innerHTML = errorMessage.trim().replace(/\n/g, '<br>'); // Заменяем \n на <br>
                    errorBlock.style.display = 'block';
                    return;
                }
                $.ajax({
                    url: "php/update_organizator.php",
                    type: "POST",
                    data: formData,
                    contentType: false,
                    processData: false,
                    dataType: "json", // Добавьте это, чтобы автоматически парсить JSON
                    success: function(response) {
                        if (response.success) {
                            alert(response.message);
                            window.location.href = "listOrganizatorActive_admin.php";
                        } else {
                            alert("Ошибка: " + response.message);
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        alert("Ошибка AJAX: " + textStatus + " - " + errorThrown);
                        console.log(jqXHR.responseText); // Логирование ошибки в консоль
                    }
                });
            });
        });
    </script>
</body>

</html>