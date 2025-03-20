<div class="lk__menu">
    <?php
    // Получение данных пользователя о статусе
    $userId = $_SESSION['user_id'];
    $userStatusQuery = "
        SELECT
            users.is_admin,
            organizators.is_approved,
            organizators.is_organizator
        FROM
            users
        LEFT JOIN
            organizators ON users.user_id = organizators.organizator_id
        WHERE
            users.user_id = $1;
    ";
    $result = pg_query_params($conn, $userStatusQuery, array($userId));
    $userStatus = pg_fetch_assoc($result);

    // Функция для рендеринга элементов меню с количеством
    function renderMenuItemsWithCount($title, $links, $counts)
    {
        echo "<div class='title1 lk__menu-title'>$title</div>";
        echo "<div class='lk__menu-items'>";
        foreach ($links as $link => $text) {
            $count = isset($counts[$link]) ? $counts[$link] : 0;
            echo "<a href='$link' class='title2'>$text";
            if ($count > 0) {
                echo "<span class='badge'>$count</span>";
            }
            echo "</a>";
        }
        echo "</div>";
    }

    /// Подсчет количества мероприятий, ожидающих одобрения
    $pendingEventsQuery = "
                            SELECT COUNT(*)
                            FROM events e
                            JOIN organizators_events oe ON e.event_id = oe.event_id
                            WHERE e.is_approved = false
                            AND oe.organizator_id = $1;
                        ";

    $pendingEventsResult = pg_query_params($conn, $pendingEventsQuery, [$userId]);
    $pendingEventsCount = pg_fetch_result($pendingEventsResult, 0, 0);

    // Подсчет количества заявок организаторов
    $orgRequestsQuery = "SELECT COUNT(*) FROM organizators WHERE is_approved = false";
    $orgRequestsResult = pg_query_params($conn, $orgRequestsQuery, []);
    $orgRequestsCount = pg_fetch_result($orgRequestsResult, 0, 0);

    // Подсчет количества заявок мероприятий
    $eventRequestsQuery = "SELECT COUNT(*) FROM events WHERE is_approved = false";
    $eventRequestsResult = pg_query_params($conn, $eventRequestsQuery, []);
    $eventRequestsCount = pg_fetch_result($eventRequestsResult, 0, 0);

    // Меню профиля
    echo "<div class='title1 lk__menu-title' onclick=\"toggleForms('profile')\"><img src='img/icons/person.svg' alt='person'><a href='./lk.php'>Профиль</a></div>";

    // Меню участника
    renderMenuItemsWithCount('Участник', [
        'nowEvent_participant.php' => 'Предстоящие события',
        'pastEvent_participant.php' => 'История'
    ], []);

    // Проверка, если пользователь является организатором
    if ($userStatus['is_approved'] === 't') {
        if ($userStatus['is_organizator'] === 't') {
            renderMenuItemsWithCount('Организатор', [
                'futureEvent_organizer.php' => 'События на одобрении',
                'nowEventActive_organizer.php' => 'Предстоящие события',
                'scanEvent_organizer.php' => 'Сканирование событий',
                'pastEvent_organizer.php' => 'История',
                'createEvent.php' => 'Создать мероприятие'
            ], [
                'futureEvent_organizer.php' => $pendingEventsCount
            ]);
        } else {
            renderMenuItemsWithCount('Организатор', [
                'nowEventActive_organizer.php' => 'Предстоящие события',
                'pastEvent_organizer.php' => 'История'
            ], []);
        }
    }

    // Проверка, если пользователь является администратором
    if ($userStatus['is_admin'] === 't') {
        renderMenuItemsWithCount('Администратор', [
            'listUser_admin.php' => 'Список пользователей',
            'listOrganizatorActive_admin.php' => 'Список организаторов',
            'listRequestOrganizator_admin.php' => 'Заявки организаторов',
            'listEventActive_admin.php' => 'Список текущих мероприятий',
            'listPastEvent_admin.php' => 'Список прошедших мероприятий',
            'listRequestEvent_admin.php' => 'Заявки мероприятий'
        ], [
            'listRequestOrganizator_admin.php' => $orgRequestsCount,
            'listRequestEvent_admin.php' => $eventRequestsCount
        ]);
    }
    ?>

    <!-- Выход и удаление аккаунта -->
    <div class="lk__menu-footer">
        <a href="#!" onclick="logout()" class="title2 lk__menu-title">Выйти<img src="img/icons/exit.svg" alt="exit"></a>
    </div>
</div>
<!-- /lk__menu -->