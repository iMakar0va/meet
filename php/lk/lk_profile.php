<?php
$userId = $_SESSION['user_id'];

// Функция для получения данных пользователя
function fetchUserData($conn, $userId)
{
    $query = "SELECT * FROM users WHERE user_id = :userId";
    $stmt = $conn->prepare($query);
    $stmt->execute(['userId' => $userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Функция для получения данных организатора
function fetchOrganizerData($conn, $userId)
{
    $query = "SELECT * FROM organizators WHERE organizator_id = :userId";
    $stmt = $conn->prepare($query);
    $stmt->execute(['userId' => $userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Функция для получения статистики по событиям
function fetchEventStatistics($conn, $userId)
{
    // Количество предстоящих событий
    $upcomingEventsQuery = "SELECT count(ue.event_id) FROM events e
                             JOIN user_events ue ON ue.event_id = e.event_id
                             WHERE ue.user_id = :userId AND e.event_date > CURRENT_DATE";
    $upcomingEventsStmt = $conn->prepare($upcomingEventsQuery);
    $upcomingEventsStmt->execute(['userId' => $userId]);
    $upcomingEventCount = $upcomingEventsStmt->fetchColumn();

    // Количество прошедших событий
    $pastEventsQuery = "SELECT count(ue.event_id) FROM events e
                        JOIN user_events ue ON ue.event_id = e.event_id
                        WHERE ue.user_id = :userId AND e.event_date < CURRENT_DATE";
    $pastEventsStmt = $conn->prepare($pastEventsQuery);
    $pastEventsStmt->execute(['userId' => $userId]);
    $pastEventCount = $pastEventsStmt->fetchColumn();

    // Популярные темы
    $popularTopicsQuery = "SELECT e.topic, COUNT(*) AS topic_count
                           FROM user_events ue
                           JOIN events e ON ue.event_id = e.event_id
                           WHERE ue.user_id = :userId
                           GROUP BY e.topic
                           ORDER BY topic_count DESC
                           LIMIT 3";
    $popularTopicsStmt = $conn->prepare($popularTopicsQuery);
    $popularTopicsStmt->execute(['userId' => $userId]);
    $popularTopics = $popularTopicsStmt->fetchAll(PDO::FETCH_ASSOC);

    return [
        'upcomingEventCount' => $upcomingEventCount,
        'pastEventCount' => $pastEventCount,
        'popularTopics' => $popularTopics
    ];
}

// Получаем данные пользователя
$userData = fetchUserData($conn, $userId);

if ($userData) {
    // Проверяем, что поле "image" содержит байтовые данные, а не ресурс
    $imageData = $userData['image'];

    // Если изображение существует, кодируем его в Base64
    if (!empty($imageData)) {
        // Убедимся, что данные изображение не является ресурсом
        if (is_resource($imageData)) {
            // Если это ресурс, извлекаем его содержимое
            $imageData = stream_get_contents($imageData);
        }

        // Кодируем изображение в base64
        $profileImageSrc = "data:image/jpeg;base64," . base64_encode($imageData);
    } else {
        // Если изображения нет, ставим путь к дефолтному изображению
        $profileImageSrc = "img/profile.jpg";
    }

    $_SESSION['user_image'] = $userData['image'];
?>
    <div class="lk__profile-top">
        <div class="lk__profile-img">
            <img src="<?= $profileImageSrc ?>" alt="Профильное изображение">
        </div>
        <div>
            <div class="title2 setting" id="editProfileButton" onclick="toggleForms('lkSetting')"><img src="img/icons/setting.svg" alt="setting">Редактировать профиль</div>
            <div class="title0" style="margin-top: 15px;"><?= $userData["last_name"] . " " . $userData["first_name"] ?></div>
        </div>
    </div>

    <div>
        <div class="title1" style="margin-bottom:10px;">Личные данные участника</div>
        <div class="title2 lk__profile-data">
            <div>Дата рождения: <?= $userData["birth_date"] ?></div>
            <div>Почта: <?= $userData["email"] ?></div>
            <div>Пол: <?= $userData["gender"] ?></div>
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
    echo "Ошибка при получении данных.";
}

// Проверяем, является ли пользователь организатором
$organizerData = fetchOrganizerData($conn, $userId);

if ($organizerData) {
    $applicationStatusQuery = "SELECT o.* FROM organizators o WHERE o.organizator_id = :userId and isorganizator = false";
    $stmt = $conn->prepare($applicationStatusQuery);
    $stmt->execute(['userId' => $userId]);
    $applicationStatus = $stmt->fetch(PDO::FETCH_ASSOC);

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
