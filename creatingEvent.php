<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <link rel="stylesheet" href="styles/lk.css">
    <link rel="stylesheet" href="styles/forma.css">
    <link rel="stylesheet" href="styles/media/media_lk.css">
    <title>Личный кабинет</title>
</head>

<body>
    <?php
    session_start();
    require './php/header.php';
    require './php/conn.php';
    ?>

    <div class="container">
        <div class="lk">
            <?php require 'php/lk/lk_menu.php'; ?>
            <div class="lk__profile">
                <div class="title1">Создание событий</div>
                <form id="createForm">
                    <div class="input-file-row">
                        <label class="input-file">
                            <input type="file" name="file" multiple accept="image/*">
                            <span class="title2">Выберите фото для мероприятия</span>
                        </label>
                        <div class="input-file-list"></div>
                    </div>
                    <div class="form__group">
                        <input id="title_event" name="title_event" class="input title2" type="text" placeholder=" " required>
                        <label class="label title2" for="">Название мероприятия*</label>
                    </div>
                    <div class="text-field__icon">
                        <select class="text-field__input title2" id="type_event" name="type_event" required>
                            <option value="" disabled selected hidden>Выберите тип мероприятия:*</option>
                            <option value="Конференция">Конференция</option>
                            <option value="Выставка">Выставка</option>
                            <option value="Презентация">Презентация</option>
                            <option value="Мастер-класс">Мастер-класс</option>
                            <option value="Соревнования">Соревнования</option>
                            <option value="Семинар">Семинар</option>
                            <option value="Тренинг">Тренинг</option>
                            <option value="Форум">Форум</option>
                            <option value="Экскурсия">Экскурсия</option>
                            <option value="Кинопоказ">Кинопоказ</option>
                            <option value="Лекция">Лекция</option>
                            <option value="Клуб">Клуб</option>
                            <option value="Творческий вечер">Творческий вечер</option>
                            <option value="Ярмарка">Ярмарка</option>
                            <option value="Курсы">Курсы</option>
                        </select>
                    </div>
                    <div class="text-field__icon">
                        <select class="text-field__input title2" id="topic_event" name="topic_event" required>
                            <option value="" disabled selected hidden>Выберите направление мероприятия:*</option>
                            <option value="технологии и инновации">технологии и инновации</option>
                            <option value="бизнес и финансы">бизнес и финансы</option>
                            <option value="здоровье и фитнес">здоровье и фитнес</option>
                            <option value="кулинария и питание">кулинария и питание</option>
                            <option value="путешествие и туризм">путешествие и туризм</option>
                            <option value="искусство и культура">искусство и культура</option>
                            <option value="языки и образование">языки и образование</option>
                            <option value="семейные отношения">семейные отношения</option>
                            <option value="хобби и развлечения">хобби и развлечения</option>
                        </select>
                    </div>
                    <div class="form__group">
                        <textarea class="input textarea title2" id="desc_event" name="desc_event" rows="4" placeholder="Описание мероприятия*"></textarea>
                    </div>
                    <div class="form__group">
                        <input id="date_event" name="date_event" class="input title2" type="text" placeholder="ЧЧ/ММ/ГГ" required>
                        <label class="label title2" for="">Дата мероприятия*</label>
                    </div>
                    <div class="form__group">
                        <input id="start_time" name="start_time" class="input title2" type="text" placeholder="ЧЧ:ММ" required>
                        <label class="label title2" for="">Время начала*</label>
                    </div>
                    <div class="form__group">
                        <input id="end_time" name="end_time" class="input title2" type="text" placeholder="ЧЧ:ММ" required>
                        <label class="label title2" for="">Время окончания*</label>
                    </div>
                    <div class="form__group">
                        <input id="city_event" name="city_event" class="input title2" type="text" placeholder=" " required>
                        <label class="label title2" for="">Город*</label>
                    </div>
                    <div class="form__group">
                        <input id="place_event" name="place_event" class="input title2" type="text" placeholder=" " required>
                        <label class="label title2" for="">Место проведения*</label>
                    </div>
                    <div class="form__group">
                        <input id="address_event" name="address_event" class="input title2" type="text" placeholder=" " required>
                        <label class="label title2" for="">Адрес*</label>
                    </div>
                    <div class="form__group">
                        <input id="phone" name="phone" class="input title2" type="text" placeholder="+7 XXX XXX XX XX" required>
                        <label class="label title2" for="">Номер телефона*</label>
                    </div>
                    <div class="" style="color: black;">*-поля для обязательного заполения</div>
                    <div id="error" class="error title2" style="display: none;">Пользователь с такой почтой уже зарегистрирован!</div>
                    <button class="btn1 title2" type="submit">Создать мероприятие</button>
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

    <script src="./scripts/creating_event.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Сохранение фото пользователя
        var dt = new DataTransfer();

        $('.input-file input[type=file]').on('change', function() {
            let $files_list = $(this).closest('.input-file').next();
            $files_list.empty();

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
                }
            };
            this.files = dt.files;
        });

        function removeFilesItem(target) {
            let name = $(target).prev().text();
            let input = $(target).closest('.input-file-row').find('input[type=file]');
            $(target).closest('.input-file-list-item').remove();
            for (let i = 0; i < dt.items.length; i++) {
                if (name === dt.items[i].getAsFile().name) {
                    dt.items.remove(i);
                }
            }
            input[0].files = dt.files;
        }
    </script>
</body>

</html>