<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/event.css">
    <link rel="stylesheet" href="styles/media/media_event.css">
    <title>Афиша</title>
</head>

<body>
    <?php
    session_start();
    require './php/header.php';
    require './php/conn.php';

    if (isset($_GET['event_id'])) {
        $eventId = intval($_GET['event_id']);

        // Используем подготовленные запросы для безопасных операций
        $eventQuery = pg_prepare($conn, "get_event", "SELECT * FROM events WHERE event_id = $1;");
        $resultGetEvents = pg_execute($conn, "get_event", [$eventId]);

        $organizerQuery = pg_prepare($conn, "get_organizer", "SELECT * FROM organizators_events oe
                                                              JOIN organizators o ON o.organizator_id = oe.organizator_id
                                                              WHERE oe.event_id = $1;");
        $resultGetOrganizator = pg_execute($conn, "get_organizer", [$eventId]);
        $resGetOrganizator = pg_fetch_assoc($resultGetOrganizator);

        $countQuery = pg_prepare($conn, "count_people", "SELECT count(*) FROM user_events WHERE event_id = $1 and is_signed = true;");
        $resultCountPeople = pg_execute($conn, "count_people", [$eventId]);
        $countPeople = pg_fetch_result($resultCountPeople, 0, 0);

        $checkEventQuery = pg_prepare($conn, "check_event", "SELECT EXISTS (SELECT 1 FROM events WHERE event_id = $1 AND event_date >= CURRENT_DATE and is_active=true);");
        $resultCheckEvent = pg_execute($conn, "check_event", [$eventId]);
        $isFutureEvent = pg_fetch_result($resultCheckEvent, 0, 0) === 't';

        $isAuthed = isset($_SESSION['user_id']);
        if ($isAuthed) {
            $userId = $_SESSION['user_id'];
            $registrationQuery = pg_prepare($conn, "check_registration", "SELECT EXISTS(SELECT 1 FROM user_events WHERE user_id = $1 AND event_id = $2 and is_signed = true);");
            $resultCheckRegistration = pg_execute($conn, "check_registration", [$userId, $eventId]);
            $isRegistered = pg_fetch_result($resultCheckRegistration, 0) === 't';
        } else {
            $isRegistered = false;
        }

        if ($resultGetEvents) {
            $event = pg_fetch_assoc($resultGetEvents);
            if ($event) {
                $months = [
                    1 => 'января',
                    'февраля',
                    'марта',
                    'апреля',
                    'мая',
                    'июня',
                    'июля',
                    'августа',
                    'сентября',
                    'октября',
                    'ноября',
                    'декабря'
                ];
                $dateParts = explode('-', $event['event_date']);
                $formattedDate = intval($dateParts[2]) . ' ' . $months[intval($dateParts[1])] . ' ' . intval($dateParts[0]);

                $imageSrc = !empty($event["image"])
                    ? "data:image/jpeg;base64," . base64_encode(pg_unescape_bytea($event["image"]))
                    : "img/profile.jpg";
    ?>
                <section class="event-top" style="background-image: url(<?= $imageSrc ?>);">
                    <div class="container">
                        <div class="event__banner">
                            <div class="event__banner-block">
                                <div class="event-date title0"><?= htmlspecialchars($formattedDate) ?></div>
                                <div class="event-time title1">
                                    <?= substr($event['start_time'], 0, 5) . " - " . substr($event['end_time'], 0, 5) ?>
                                </div>
                                <div class="event-title title"><?= htmlspecialchars($event['title']) ?></div>
                                <div class="event-type title1">Тип мероприятия: <?= " " . htmlspecialchars($event['type']) ?></div>
                                <div class="event-topic title1">Направление мероприятия: <?= " " . htmlspecialchars($event['topic']) ?></div>
                                <button class="btn1 title2" id="registerButton" style="<?= $isRegistered ? 'background-color: var(--brown-color);' : 'background-color: var(--yellow-color);'  ?><?= $isFutureEvent ? 'display: block;' : 'display: none;'; ?>">
                                    <?= $isRegistered ? "Отписаться" : "Записаться"; ?>
                                </button>
                            </div>
                            <div class="event__banner-block title2">
                                <div class="event-place">
                                    <div class="_1 title1">Место проведения</div>
                                    <div class="_2 title2"><?= htmlspecialchars($event['place']) ?></div>
                                </div>
                                <div class="event-people">
                                    <img src="img/icons/people-event.svg" alt="people">
                                    <span id="participantsCount"><?= htmlspecialchars($countPeople) ?></span> человек(а)
                                </div>
                                <div class="event-organisator">
                                    <div class="_1 title1">Организатор</div>
                                    <!-- <div class="_2 title2"><?= htmlspecialchars($resGetOrganizator['name']) ?></div> -->
                                    <div class="_2 title2"><a href="organizatorEventNow.php?organizator_id=<?= htmlspecialchars($resGetOrganizator['organizator_id']) ?>"><?= htmlspecialchars($resGetOrganizator['name']) ?></a></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <div class="container">
                    <div class="event-bottom">
                        <div class="event-bottom-block">
                            <div class="title1">Описание мероприятия</div>
                            <p class="title2">
                                <?= htmlspecialchars($event['description']) ?>
                            </p>
                        </div>
                        <div class="event-bottom-block">
                            <div class="title1">Программа мероприятия</div>
                            <div class="contact">
                                <?php if ($event['program_file'] !== null): ?>
                                    <a class="download"
                                        href="php/download_program.php?event_id=<?= $eventId ?>">
                                        Скачать программу мероприятия <?= htmlspecialchars($event['title']) ?>
                                    </a>
                                <?php else: ?>
                                    <span class="title2">Файл не загружен</span>
                                <?php endif; ?>
                            </div>

                            <div class="title1">Контакты</div>
                            <div class="contact">
                                <div class="contact__item title2">
                                    <img src="img/icons/phone.svg" alt="phone">
                                    <?= htmlspecialchars($event['phone']) ?>
                                </div>
                                <div class="contact__item title2">
                                    <img src="img/icons/email-event.svg" alt="email">
                                    <?= htmlspecialchars($event['email']) ?>
                                </div>
                                <div class="contact__item title2">
                                    <img src="img/icons/place.svg" alt="place">
                                    <?= htmlspecialchars($event['address']) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
    <?php
            } else {
                echo "Мероприятие не найдено.";
            }
        } else {
            echo "Ошибка при получении данных: " . pg_last_error();
        }
    } else {
        echo "ID мероприятия не указан.";
    }
    ?>

    <?php require './php/footer.php'; ?>
    <script>
        // Обработчик записи на мероприятие
        document.addEventListener("DOMContentLoaded", function() {
            const registerButton = document.getElementById("registerButton");
            const participantsCount = document.getElementById("participantsCount");

            if (registerButton) {
                registerButton.addEventListener("click", function(event) {
                    event.preventDefault();

                    const isAuthed = <?= json_encode($isAuthed) ?>;
                    if (!isAuthed) {
                        window.location.href = "auth.php";
                        return;
                    }
                    const wantsToUnregister = registerButton.textContent.trim() === "Отписаться";
                    if (wantsToUnregister && !confirm("Вы уверены, что хотите отменить запись?")) {
                        return; // пользователь нажал «Отмена» – ничего не делаем
                    }
                    // Запись на мероприятие
                    fetch("php/register_for_event.php", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded"
                            },
                            body: `event_id=<?= $eventId ?>`
                        })
                        .then(response => response.json())
                        .then(data => {
                            alert(data.message);

                            if (data.success) {
                                if (data.action === "registered") {
                                    registerButton.textContent = "Отписаться";
                                    registerButton.style.backgroundColor = "var(--brown-color)";
                                    participantsCount.textContent = parseInt(participantsCount.textContent) + 1;
                                } else if (data.action === "unregistered") {
                                    registerButton.textContent = "Записаться";
                                    registerButton.style.backgroundColor = "var(--yellow-color)";
                                    participantsCount.textContent = parseInt(participantsCount.textContent) - 1;
                                }
                            }
                        })
                        .catch(error => console.error("Ошибка:", error));
                });
            }
        });
    </script>
</body>

</html>