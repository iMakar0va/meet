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

// Функция для получения статистики по событиям
function fetchEventStatistics($conn, $userId)
{
    // Количество предстоящих событий
    $upcomingEventsQuery = "SELECT count(ue.event_id) FROM events e
                             JOIN user_events ue ON ue.event_id = e.event_id
                             WHERE ue.user_id = $1 AND e.event_date > CURRENT_DATE";
    $upcomingEventsResult = pg_query_params($conn, $upcomingEventsQuery, array($userId));
    $upcomingEventCount = pg_fetch_row($upcomingEventsResult);

    // Количество прошедших событий
    $pastEventsQuery = "SELECT count(ue.event_id) FROM events e
                        JOIN user_events ue ON ue.event_id = e.event_id
                        WHERE ue.user_id = $1 AND e.event_date < CURRENT_DATE";
    $pastEventsResult = pg_query_params($conn, $pastEventsQuery, array($userId));
    $pastEventCount = pg_fetch_row($pastEventsResult);

    // Популярные темы
    $popularTopicsQuery = "SELECT e.topic, COUNT(*) AS topic_count
                           FROM public.user_events ue
                           JOIN public.events e ON ue.event_id = e.event_id
                           WHERE ue.user_id = $1
                           GROUP BY e.topic
                           ORDER BY topic_count DESC
                           LIMIT 3";
    $popularTopicsResult = pg_query_params($conn, $popularTopicsQuery, array($userId));
    $popularTopics = pg_fetch_all($popularTopicsResult);

    return [
        'upcomingEventCount' => $upcomingEventCount[0],
        'pastEventCount' => $pastEventCount[0],
        'popularTopics' => $popularTopics
    ];
}

// Получаем данные пользователя
$userDataResult = fetchUserData($conn, $userId);

if ($userDataResult) {
    $user = pg_fetch_assoc($userDataResult);
    $profileImageSrc = !empty($user["image"]) ? "data:image/jpeg;base64," . base64_encode(pg_unescape_bytea($user["image"])) : "img/profile.jpg";
    $_SESSION['user_image'] = $user['image'];
?>
    <div class="lk__profile-top">
        <div class="lk__profile-img">
            <img src="<?= $profileImageSrc ?>" alt="Профильное изображение">
        </div>
        <div>
            <div class="title2 setting" id="editProfileButton" onclick="toggleForms('lkSetting')"><img src="img/icons/setting.svg" alt="setting">Редактировать профиль</div>
            <div class="title0" style="margin-top: 15px;"><?= $user["last_name"] . " " . $user["first_name"] ?></div>
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
        <div class="title1" style="margin-bottom:10px;">Анализ</div>
        <div class="title2 lk__profile-data">
            <?php
            $eventStatistics = fetchEventStatistics($conn, $userId);
            ?>
            <div>Посещено мероприятий: <?= $eventStatistics['pastEventCount'] ?></div>
            <div>Текущих мероприятий: <?= $eventStatistics['upcomingEventCount'] ?></div>
            <div>Популярные направления:
                <?php
                if (empty($eventStatistics['popularTopics'])) {
                    echo "вы еще нигде не участвовали.";
                } else {
                    $lastTopic = end($eventStatistics['popularTopics']);
                    foreach ($eventStatistics['popularTopics'] as $index => $topic) {
                        echo $topic['topic'] . ($topic === $lastTopic ? '.' : ', ');
                    }
                }
                ?>
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
    $organizerApplicationQuery = pg_prepare($conn, "get_application_status", "SELECT o.* FROM organizators o WHERE o.organizator_id = $1 and is_organizator = false;");
    $applicationStatusResult = pg_execute($conn, "get_application_status", [$userId]);
    $applicationStatus = pg_fetch_assoc($applicationStatusResult);
    if ($applicationStatus) { ?>
        <div class="title1">Ваша заявка на рассмотрении</div>
    <?php
    } else { ?>
        <div class="title1" style="margin-bottom:10px;">Личные данные организатора</div>
        <div class="title2 lk__profile-data">
            <div>Название: <?= $organizerData["name"] ?></div>
            <div>Номер телефона: <?= $organizerData["phone_number"] ?></div>
            <div>Почта: <?= $organizerData["email"] ?></div>
        </div>
    <?php
    }
} else { ?>
    <div class="title1">Хочешь стать организатором?</div>
    <div class="title2">
        Организуйте своё мероприятие легко и быстро!
        Планируете провести вебинар, мастер-класс или лекцию? Этот сервис создан специально для того, чтобы помочь вам в этом. Выберите формат, установите дату и время, добавьте описание и фото – и ваше мероприятие готово к запуску.
        Интерфейс прост и удобен, а инструменты позволяют быстро привлекать участников. Начните организовывать свои мероприятия уже сегодня!
    </div>
    <button class="btn1"><a href="./request.php">Стать организатором</a></button>
<?php } ?>