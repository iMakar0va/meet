<div class="lk__setting" style="display: none;">
    <div class="title1">Редактирование профиля</div>
    <form>
        <div class="text-field__icon text-field__icon_person">
            <input id="last_name" name="last_name" class="text-field__input title2" type="text" placeholder="Фамилия" required>
        </div>
        <div class="text-field__icon text-field__icon_person">
            <input id="first_name" name="first_name" class="text-field__input title2" type="text" placeholder="Имя" required>
        </div>
        <div class="input-file-row">
            <label class="input-file">
                <input type="file" name="file" multiple accept="image/*" id="profilePictureInput">
                <span class="title2">Выберите фото для профиля</span>
            </label>
            <div class="input-file-list">
                <?php
                if (!empty($_SESSION['user_image'])) { ?>
                    <div class="input-file-list-item">
                        <?php
                        echo '<img class="input-file-list-img" src="data:image/jpeg;base64,' . base64_encode(pg_unescape_bytea($_SESSION['user_image'])) . '">'; ?>
                        <a href="#" onclick="removeFilesItem(this); return false;" class="input-file-list-remove">x</a>
                    </div>
                <?php
                }
                ?>
            </div>
        </div>
        <!-- </div> -->
        <div class="gender title2">
            <div class="custom">
                <input type="radio" id="male" name="gender" value="мужской" required>
                <label for="male">Мужчина</label>
            </div>
            <div class="custom">
                <input type="radio" id="female" name="gender" value="женский" required>
                <label for="female">Женщина</label>
            </div>
        </div>
        <div class="text-field__icon text-field__icon_password">
            <input id="password" name="password" class="text-field__input title2" type="text" placeholder="Новый пароль">
        </div>
        <div class="text-field__icon text-field__icon_calendar">
            <input
                class="text-field__input title2"
                type="text"
                name="birth_date"
                placeholder="ДД/ММ/ГГГГ"
                maxlength="10"
                required
                id="birthDateInput">
        </div>
        <!-- Флаг для удаления изображения??????????????????????-->
        <input type="hidden" name="remove_image" id="removeImageField" value="0">
        <button class="btn1 title2" id="saveProfile" type="submit">Сохранить изменения</button>
    </form>
</div>