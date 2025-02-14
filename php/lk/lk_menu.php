<div class="lk__menu">
    <?php
    // Получение данных пользователя о статусе
    $userId = $_SESSION['user_id'];
    $userStatusQuery = "
        SELECT
            users.isadmin,
            organizators.isorganizator
        FROM
            users
        LEFT JOIN
            organizators ON users.user_id = organizators.organizator_id
        WHERE
            users.user_id = $1;
    ";
    $result = pg_query_params($conn, $userStatusQuery, array($userId));
    $userStatus = pg_fetch_assoc($result);

    // Функция для рендеринга элементов меню
    function renderMenuItems($title, $links) {
        echo "<div class='title1 lk__menu-title'>$title</div>";
        echo "<div class='lk__menu-items'>";
        foreach ($links as $link => $text) {
            echo "<a href='$link' class='title2'>$text</a>";
        }
        echo "</div>";
    }

    // Меню профиля
    echo "<div class='title1 lk__menu-title' onclick=\"toggleForms('profile')\"><img src='img/icons/person.svg' alt='person'><a href='./lk.php'>Профиль</a></div>";

    // Меню участника
    renderMenuItems('Участник', [
        'nowEvent_participant.php' => 'Предстоящие события',
        'pastEvent_participant.php' => 'История'
    ]);

    // Проверка, если пользователь является организатором
    if ($userStatus['isorganizator'] === 't') { // Проверка на true для isorganizator
        renderMenuItems('Организатор', [
            'nowEvent_organizer.php' => 'Предстоящие события',
            'pastEvent_organizer.php' => 'История',
            'creatingEvent.php' => 'Создать мероприятие'
        ]);
    }

    // Проверка, если пользователь является администратором
    if ($userStatus['isadmin'] === 't') { // Проверка на true для isadmin
        renderMenuItems('Администратор', [
            'listPastEvent_admin.php' => 'Список прошедших мероприятий',
            'listUser_admin.php' => 'Список пользователей',
            'listOrganizator_admin.php' => 'Список организаторов',
            'listRequest_admin.php' => 'Заявки'
        ]);
    }
    ?>

    <!-- Выход и удаление аккаунта -->
    <div class="lk__menu-footer">
        <a href="#!" onclick="logout()" class="title2 lk__menu-title">Выйти<img src="img/icons/exit.svg" alt="exit"></a>
        <a href="#!" onclick="deleteAccount()" class="title2 lk__menu-title">Удалить аккаунт</a>
    </div>
</div>
<!-- /lk__menu -->

