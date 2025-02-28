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
                            <span class="title2">Выберите фото для мероприятия</span>
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
    <script src="./scripts/change_event.js"></script>
</body>

</html>