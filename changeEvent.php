<?php
session_start();
require 'php/conn.php';

if (!isset($_GET['event_id'])) {
    die("Ошибка: мероприятие не найдено.");
}

$eventId = intval($_GET['event_id']);
$_SESSION['event_id'] = $eventId;

// Получаем данные мероприятия
$query = "SELECT * FROM events WHERE event_id = $1";
$result = pg_query_params($conn, $query, [$eventId]);
$event = pg_fetch_assoc($result);

if (!$event) {
    die("Ошибка: мероприятие не найдено.");
}

// Определяем изображение
$imageSrc = !empty($event["image"])
    ? "data:image/jpeg;base64," . base64_encode(pg_unescape_bytea($event["image"]))
    : "img/default.jpg";

$dateFormatted = date("d/m/Y", strtotime($event['event_date']));
$startTimeFormatted = date("H:i", strtotime($event['start_time']));
$endTimeFormatted = date("H:i", strtotime($event['end_time']));

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
    $eventId = intval($_GET['event_id']);
    $_SESSION['event_id'] = $eventId;
    ?>
    <div class="container">
        <div class="lk">
            <?php require 'php/lk/lk_menu.php'; ?>
            <div class="lk__profile">
                <div class="title1">Редактирование мероприятие</div>
                <form id="editEventForm" enctype="multipart/form-data">
                    <input type="hidden" name="event_id" value="<?= $eventId ?>">
                    <div class="input-file-row">
                        <label class="input-file">
                            <input type="file" name="file" multiple accept="image/*" id="profilePictureInput">
                            <span class="title2">Выберите фото для профиля</span>
                        </label>
                        <div class="input-file-list">
                            <div class="input-file-list-item">
                                <img class="input-file-list-img" src="<?= $imageSrc ?>" alt="event_image">
                                <a href="#" onclick="removeFilesItem(this); return false;" class="input-file-list-remove">x</a>
                            </div>
                        </div>
                    </div>

                    <div class="form__group">
                        <input id="title_event" name="title_event" class="input title2" type="text" value="<?= htmlspecialchars($event['title']) ?>" placeholder=" " required>
                        <label class="label title2" for="">Название мероприятия</label>
                    </div>
                    <div class="text-field__icon">
                        <select class="text-field__input title2" id="type_event" name="type_event" required>
                            <option value="" disabled selected hidden>Выберите тип мероприятия:</option>
                            <option value="Конференция" <?= ($event['type'] == 'Конференция') ? 'selected' : '' ?>>Конференция</option>
                            <option value="Выставка" <?= ($event['type'] == 'Выставка') ? 'selected' : '' ?>>Выставка</option>
                            <option value="Презентация" <?= ($event['type'] == 'Презентация') ? 'selected' : '' ?>>Презентация</option>
                            <option value="Мастер-класс" <?= ($event['type'] == 'Мастер-класс') ? 'selected' : '' ?>>Мастер-класс</option>
                            <option value="Соревнования" <?= ($event['type'] == 'Соревнования') ? 'selected' : '' ?>>Соревнования</option>
                            <option value="Семинар" <?= ($event['type'] == 'Семинар') ? 'selected' : '' ?>>Семинар</option>
                            <option value="Тренинг" <?= ($event['type'] == 'Тренинг') ? 'selected' : '' ?>>Тренинг</option>
                            <option value="Форум" <?= ($event['type'] == 'Форум') ? 'selected' : '' ?>>Форум</option>
                            <option value="Экскурсия" <?= ($event['type'] == 'Экскурсия') ? 'selected' : '' ?>>Экскурсия</option>
                            <option value="Кинопаказ" <?= ($event['type'] == 'Кинопаказ') ? 'selected' : '' ?>>Кинопаказ</option>
                            <option value="Лекция" <?= ($event['type'] == 'Лекция') ? 'selected' : '' ?>>Лекция</option>
                            <option value="Клуб" <?= ($event['type'] == 'Клуб') ? 'selected' : '' ?>>Клуб</option>
                            <option value="Творческий вечер" <?= ($event['type'] == 'Творческий вечер') ? 'selected' : '' ?>>Творческий вечер</option>
                            <option value="Ярмарка" <?= ($event['type'] == 'Ярмарка') ? 'selected' : '' ?>>Ярмарка</option>
                            <option value="Курсы" <?= ($event['type'] == 'Курсы') ? 'selected' : '' ?>>Курсы</option>
                        </select>
                    </div>
                    <div class="text-field__icon">
                        <select class="text-field__input title2" id="topic_event" name="topic_event" required>
                            <option value="" disabled selected hidden>Выберите направление мероприятия:</option>
                            <option value="технологии и инновации" <?= ($event['topic'] == 'технологии и инновации') ? 'selected' : '' ?>>технологии и инновации</option>
                            <option value="бизнес и финансы" <?= ($event['topic'] == 'бизнес и финансы') ? 'selected' : '' ?>>бизнес и финансы</option>
                            <option value="здоровье и фитнес" <?= ($event['topic'] == 'здоровье и фитнес') ? 'selected' : '' ?>>здоровье и фитнес</option>
                            <option value="кулинария и питание" <?= ($event['topic'] == 'кулинария и питание') ? 'selected' : '' ?>>кулинария и питание</option>
                            <option value="путешествие и туризм" <?= ($event['topic'] == 'путешествие и туризм') ? 'selected' : '' ?>>путешествие и туризм</option>
                            <option value="искусство и культура" <?= ($event['topic'] == 'искусство и культура') ? 'selected' : '' ?>>искусство и культура</option>
                            <option value="языки и образование" <?= ($event['topic'] == 'языки и образование') ? 'selected' : '' ?>>языки и образование</option>
                            <option value="семейные отношения" <?= ($event['topic'] == 'семейные отношения') ? 'selected' : '' ?>>семейные отношения</option>
                            <option value="хобби и развлечения" <?= ($event['topic'] == 'хобби и развлечения') ? 'selected' : '' ?>>хобби и развлечения</option>
                        </select>
                    </div>
                    <div class="form__group">
                        <textarea class="input textarea title2" id="desc_event" name="desc_event" rows="4" placeholder="Описание мероприятия"><?= htmlspecialchars($event['description']) ?></textarea>
                    </div>
                    <div class="form__group">
                        <input id="date_event" name="date_event" class="input title2" type="text" value="<?= $dateFormatted ?>" placeholder="ЧЧ/ММ/ГГ" required>
                        <label class="label title2" for="">Дата мероприятия</label>
                    </div>
                    <div class="form__group">
                        <input id="start_time" name="start_time" class="input title2" type="text" value="<?= $startTimeFormatted ?>" placeholder="ЧЧ:ММ" required>
                        <label class="label title2" for="">Время начала</label>
                    </div>
                    <div class="form__group">
                        <input id="end_time" name="end_time" class="input title2" type="text" value="<?= $endTimeFormatted ?>" placeholder="ЧЧ:ММ" required>
                        <label class="label title2" for="">Время окончания</label>
                    </div>
                    <div class="form__group">
                        <input id="city_event" name="city_event" class="input title2" type="text" value="<?= htmlspecialchars($event['city']) ?>" placeholder=" " required>
                        <label class="label title2" for="">Город</label>
                    </div>
                    <div class="form__group">
                        <input id="place_event" name="place_event" class="input title2" type="text" value="<?= htmlspecialchars($event['place']) ?>" placeholder=" " required>
                        <label class="label title2" for="">Место проведения</label>
                    </div>
                    <div class="form__group">
                        <input id="address_event" name="address_event" class="input title2" type="text" value="<?= htmlspecialchars($event['address']) ?>" placeholder=" " required>
                        <label class="label title2" for="">Адрес</label>
                    </div>
                    <div class="form__group">
                        <input id="phone" name="phone" class="input title2" type="text" value="<?= htmlspecialchars($event['phone']) ?>" placeholder="+7 XXX XXX XX XX" required>
                        <label class="label title2" for="">Номер телефона</label>
                    </div>
                    <div class="form__group">
                        <input id="email" name="email" class="input title2" type="email" value="<?= htmlspecialchars($event['email']) ?>" placeholder=" " required>
                        <label class="label title2" for="">Эл.почта</label>
                    </div>
                    <div id="error" class="error title2" style="display: none;">Ошибка!</div>
                    <input type="hidden" name="remove_image" id="removeImageField" value="0">
                    <button class="btn1 title2" id="saveProfile" type="submit">Сохранить изменения</button>
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

        $(document).ready(function() {
            $("#editEventForm").on("submit", function(event) {
                event.preventDefault();

                let formData = new FormData(this);

                let isValid = true;
                let errorMessage = "";
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

                // Вывод ошибок
                if (!isValid) {
                    errorBlock.innerHTML = errorMessage.trim().replace(/\n/g, '<br>'); // Заменяем \n на <br>
                    errorBlock.style.display = 'block';
                    return;
                }
                $.ajax({
                    url: "php/update_event.php",
                    type: "POST",
                    data: formData,
                    contentType: false,
                    processData: false,
                    dataType: "json", // Добавьте это, чтобы автоматически парсить JSON
                    success: function(response) {
                        if (response.success) {
                            alert(response.message);
                            window.location.href = "listEventActive_admin.php";
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
    <script>
        // отображение/удаление фото профиля
        var dt = new DataTransfer();

        $('.input-file input[type=file]').on('change', function() {
            let $files_list = $(this).closest('.input-file').next();
            $files_list.empty();
            $('#removeImageField').val('0');

            for (var i = 0; i < this.files.length; i++) {
                let file = this.files.item(i);
                dt.items.add(file);

                let reader = new FileReader();
                reader.readAsDataURL(file);
                reader.onloadend = function() {
                    let new_file_input = '<div class="input-file-list-item">' +
                        '<img class="input-file-list-img" src="' + reader.result + '">' +
                        '<a href="#" onclick="removeFilesItem(this); return false;" class="input-file-list-remove">x</a>' +
                        '</div>';
                    $files_list.append(new_file_input);
                };
            }
            this.files = dt.files;
        });

        function removeFilesItem(target) {
            let input = $(target).closest('.input-file-row').find('input[type=file]');
            $(target).closest('.input-file-list-item').remove();
            dt.items.clear();
            input[0].files = dt.files;

            $('#removeImageField').val('1');
        }
    </script>
</body>

</html>