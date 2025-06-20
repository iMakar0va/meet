<?php
$userId = $_SESSION['user_id'];

// Функция для получения данных пользователя
function fetchUserData($conn, $userId)
{
    $query = "SELECT * FROM users WHERE user_id = $1";
    return pg_query_params($conn, $query, array($userId));
}

// Функция для получения данных организатора
function fetchOrganizerData($conn, $userId)
{
    $query = "SELECT * FROM organizators WHERE organizator_id = $1";
    return pg_query_params($conn, $query, array($userId));
}

// Функция для получения статистики по событиям, в которых пользователь участвовал
function fetchEventStatistics($conn, $userId)
{
    $upcomingEventsQuery = "SELECT count(*) FROM events e
                            JOIN user_events ue ON ue.event_id = e.event_id
                            WHERE ue.user_id = $1 AND e.event_date >= CURRENT_DATE and ue.is_signed = true and e.is_active = true and e.is_approved = true";
    $pastEventsQuery = "SELECT count(*) FROM events e
                        JOIN user_events ue ON ue.event_id = e.event_id
                        WHERE ue.user_id = $1 AND e.event_date < CURRENT_DATE and ue.is_signed = true and e.is_active = true and e.is_approved = true";
    $popularTopicsQuery = "SELECT e.topic, COUNT(*) AS topic_count
                           FROM user_events ue
                           JOIN events e ON ue.event_id = e.event_id
                           WHERE ue.user_id = $1 and ue.is_signed = true
                           GROUP BY e.topic
                           ORDER BY topic_count DESC
                           LIMIT 3";

    return [
        'upcomingEventCount' => pg_fetch_result(pg_query_params($conn, $upcomingEventsQuery, array($userId)), 0, 0),
        'pastEventCount' => pg_fetch_result(pg_query_params($conn, $pastEventsQuery, array($userId)), 0, 0),
        'popularTopics' => pg_fetch_all(pg_query_params($conn, $popularTopicsQuery, array($userId))) ?: []
    ];
}

// Функция для получения статистики организатора
function fetchOrganizerStatistics($conn, $userId)
{
    // Количество предстоящих мероприятий
    $createdUpcomingEventsQuery = "
        SELECT COUNT(*)
        FROM events
        WHERE organizator_id = $1 AND is_active = true AND is_approved = true and event_date >= CURRENT_DATE
    ";

    // Количество прошедших мероприятий
    $createdPastEventsQuery = "
        SELECT COUNT(*)
        FROM events
        WHERE organizator_id = $1 AND is_active = true AND is_approved = true and event_date < CURRENT_DATE
    ";

    // Количество отменённых мероприятий
    $createdCancelledEventsQuery = "
        SELECT COUNT(*)
        FROM events
        WHERE organizator_id = $1 AND is_active = false AND is_approved = true
    ";

    // Популярные темы мероприятий
    $popularTopicsQuery = "
        SELECT e.topic, COUNT(*) AS topic_count
        FROM events e
        WHERE organizator_id = $1
        GROUP BY topic
        ORDER BY topic_count DESC
        LIMIT 3
    ";

    return [
        'upcomingEventCount' => pg_fetch_result(pg_query_params($conn, $createdUpcomingEventsQuery, array($userId)), 0, 0),
        'pastEventCount' => pg_fetch_result(pg_query_params($conn, $createdPastEventsQuery, array($userId)), 0, 0),
        'cancelledEventCount' => pg_fetch_result(pg_query_params($conn, $createdCancelledEventsQuery, array($userId)), 0, 0),
        'popularTopics' => pg_fetch_all(pg_query_params($conn, $popularTopicsQuery, array($userId))) ?: []
    ];
}


// Получаем данные пользователя
$userDataResult = fetchUserData($conn, $userId);
if ($userDataResult) {
    $user = pg_fetch_assoc($userDataResult);
?>
    <div class="lk__profile-top">
        <div id="qrcode"></div>
        <div class="lk-a">
            <a class="title2 setting" href="changeUser.php?user_id=<?= $user['user_id'] ?>">
                <img src="img/icons/setting.svg" alt="setting">Редактировать профиль
            </a>
            <div class="title0" style="margin-top: 15px;"><?= $user["last_name"] . " " . $user["first_name"] ?></div>
            <div class="title4" style="margin-top: 15px;">ID пользователя: <?= $user["user_id"] ?></div>
        </div>
    </div>

    <div>
        <div class="title1" style="margin-bottom:10px;">Личные данные участника</div>
        <div class="title2 lk__profile-data">
            <div>Дата рождения: <?= $user["birth_date"] ?></div>
            <div>Почта: <?= $user["email"] ?></div>
            <div>Пол: <?= $user["gender"] ?></div>
        </div>
    </div>

    <div>
        <div class="title1" style="margin-bottom:10px;">Анализ участника</div>
        <div class="title2 lk__profile-data">
            <?php $eventStatistics = fetchEventStatistics($conn, $userId); ?>
            <div>Текущих мероприятий: <?= $eventStatistics['upcomingEventCount'] ?></div>
            <div>Посещено мероприятий: <?= $eventStatistics['pastEventCount'] ?></div>
            <div>Популярные направления:
                <?= empty($eventStatistics['popularTopics']) ? "вы еще нигде не участвовали." :
                    implode(", ", array_column($eventStatistics['popularTopics'], 'topic')) . "." ?>
            </div>
        </div>
    </div>

    <?php
} else {
    echo "Ошибка при получении данных: " . pg_last_error();
}

// Проверяем, является ли пользователь организатором
$organizerDataResult = fetchOrganizerData($conn, $userId);
$organizerData = pg_fetch_assoc($organizerDataResult);

if ($organizerData) {
    $applicationQuery = "SELECT * FROM organizators WHERE organizator_id = $1 AND is_approved = FALSE";
    $applicationStatusResult = pg_query_params($conn, $applicationQuery, [$userId]);
    $applicationStatus = pg_fetch_assoc($applicationStatusResult);

    if ($applicationStatus) { ?>
        <div class="title1">Ваша заявка на рассмотрении</div>
    <?php
    } else { ?>
        <div>
            <div class="title1" style="margin-bottom:10px;">Личные данные организатора</div>
            <div class="title2 lk__profile-data">
                <div>Название: <?= $organizerData["name"] ?></div>
                <div>Номер телефона: <?= $organizerData["phone_number"] ?></div>
                <div>Почта: <?= $organizerData["email"] ?></div>
                <div>Дата начала работы: <?= $organizerData["date_start_work"] ?></div>
                <div>Описание деятельности: <?= $organizerData["description"] ?></div>
            </div>
        </div>


        <div>
            <div class="title1" style="margin-bottom:10px;">Анализ организатора</div>
            <div class="title2 lk__profile-data">
                <?php $organizerStatistics = fetchOrganizerStatistics($conn, $userId); ?>
                <div>Текущих мероприятий: <?= $organizerStatistics['upcomingEventCount'] ?></div>
                <div>Проведенных мероприятий: <?= $organizerStatistics['pastEventCount'] ?></div>
                <div>Отменённых мероприятий: <?= $organizerStatistics['cancelledEventCount'] ?></div>
                <div>Популярные направления:
                    <?= empty($organizerStatistics['popularTopics']) ? "вы еще не создавали мероприятия." :
                        implode(", ", array_column($organizerStatistics['popularTopics'], 'topic')) . "." ?>
                </div>
            </div>
        </div>
<?php
    }
}
