<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/main.css">
    <link rel="stylesheet" href="styles/media/media_main.css">
    <title>Главная</title>
</head>

<body>
    <?php
    require './php/header.php';
    require './php/conn.php';
    ?>

    <section class="banner">
        <div class="container">
            <div class="banner__wrapper">
                <div class="banner__block">
                    <div class="banner-title">
                        Твой мир событий –
                        от идеи до участия!
                    </div>
                    <div class="banner-desc">
                        Здесь каждый сможет найти событие, которое поможет раскрыть новые горизонты знаний, получить полезные навыки или просто насладиться интересным временем. Мы делаем организацию твоих мероприятий простой и удобной, чтобы ты мог сосредоточиться на самом важном!
                    </div>
                    <a href="events.php" class="btn1">Присоединиться</a>
                </div>
                <!-- /banner__block -->
                <div class="banner__block">
                    <div class="banner__block-item" style="background-image: url(img/banner1.png);">
                    </div>
                    <div class="banner__block-item" style="background-image: url(img/banner2.png);">
                    </div>
                    <div class="banner__block-item" style="background-image: url(img/banner3.png);">
                    </div>
                </div>
                <!-- /banner__block -->
            </div>
            <!-- /banner__wrapper -->
        </div>
        <!-- /container -->
    </section>
    <!-- /banner -->

    <div class="carousel">
        <div class="hash">
            <div class="hash__item title3"><img src="img/icons/hash/tech.svg" alt="">Технологии и инновации</div>
            <div class="hash__item title3"><img src="img/icons/hash/money.svg" alt="">Бизнес и финансы</div>
            <div class="hash__item title3"><img src="img/icons/hash/bike.svg" alt="">Здоровье и фитнес</div>
            <div class="hash__item title3"><img src="img/icons/hash/picture.svg" alt="">Искусство и культура</div>
            <div class="hash__item title3"><img src="img/icons/hash/cooking.svg" alt="">Кулинария и питание</div>
            <div class="hash__item title3"><img src="img/icons/hash/lang.svg" alt="">Языки и образование</div>
            <div class="hash__item title3"><img src="img/icons/hash/mountain.svg" alt="">Путешествия и туризм</div>
            <div class="hash__item title3"><img src="img/icons/hash/family.svg" alt="">Семейные отношения</div>
            <div class="hash__item title3"><img src="img/icons/hash/arrow.svg" alt="">Личностное развитие</div>
            <div class="hash__item title3"><img src="img/icons/hash/joystick.svg" alt="">Хобби и увлечения</div>
        </div>
        <!-- /hash -->
        <div aria-hidden class="hash">
            <div class="hash__item title3"><img src="img/icons/hash/tech.svg" alt="">Технологии и инновации</div>
            <div class="hash__item title3"><img src="img/icons/hash/money.svg" alt="">Бизнес и финансы</div>
            <div class="hash__item title3"><img src="img/icons/hash/bike.svg" alt="">Здоровье и фитнес</div>
            <div class="hash__item title3"><img src="img/icons/hash/picture.svg" alt="">Искусство и культура</div>
            <div class="hash__item title3"><img src="img/icons/hash/cooking.svg" alt="">Кулинария и питание</div>
            <div class="hash__item title3"><img src="img/icons/hash/lang.svg" alt="">Языки и образование</div>
            <div class="hash__item title3"><img src="img/icons/hash/mountain.svg" alt="">Путешествия и туризм</div>
            <div class="hash__item title3"><img src="img/icons/hash/family.svg" alt="">Семейные отношения</div>
            <div class="hash__item title3"><img src="img/icons/hash/arrow.svg" alt="">Личностное развитие</div>
            <div class="hash__item title3"><img src="img/icons/hash/joystick.svg" alt="">Хобби и увлечения</div>
        </div>
        <!-- /hash -->
    </div>
    <!-- /carousel -->

    <div class="container">
        <section class="info">
            <div class="info__block">
                <div class="title2">
                    Пользователей в системе
                </div>
                <div class="info__number title0">
                    <?php
                    // Получение количества пользователей
                    $getUsers = "SELECT count(*) FROM users;";
                    $resultGetUsers = $conn->query($getUsers);
                    $row = $resultGetUsers->fetch(PDO::FETCH_ASSOC);
                    $count = $row['count'];
                    echo $count;
                    ?>
                    <img src="img/icons/people.svg" alt="people">
                </div>
            </div>
            <!-- /info__block -->
            <div class="info__block">
                <div class="title2">
                    Проведено мероприятий
                </div>
                <div class="info__number title0">
                    <?php
                    // Получение количества мероприятий
                    $getEvents = "SELECT count(*) FROM events;";
                    $resultGetEvents = $conn->query($getEvents);
                    $row = $resultGetEvents->fetch(PDO::FETCH_ASSOC);
                    $count = $row['count'];
                    echo $count;
                    ?>
                    <img src="img/icons/calendar.svg" alt="calendar">
                </div>
            </div>
            <!-- /info__block -->
        </section>
        <!-- /info -->

        <div class="h2 title0">Популярные мероприятия</div>
        <div class="cards">
            <?php
            // Запрос на выборку популярных мероприятий
            $getPopularEvents = "
                SELECT e.event_id, e.image, e.title, e.type, e.topic, e.description, e.start_time, e.end_time, e.event_date, e.city, e.address, e.organizer, e.place, e.phone, e.email,
                       COUNT(ue.user_id) AS participants_count
                FROM events e
                LEFT JOIN user_events ue ON e.event_id = ue.event_id
                WHERE e.event_date > CURRENT_DATE
                GROUP BY e.event_id
                ORDER BY participants_count DESC
                LIMIT 3;
            ";
            $resultGetPopularEvents = $conn->query($getPopularEvents);
            if ($resultGetPopularEvents) {
                while ($row = $resultGetPopularEvents->fetch(PDO::FETCH_ASSOC)) {
                    require './php/card.php'; // выводим карточки мероприятия
                }
            } else {
                echo "Ошибка при получении данных: " . $conn->errorInfo();
            }
            ?>
        </div>
        <!-- /cards -->

        <div class="creation">
            <div class="creation-title">Создайте своё мероприятие</div>
            <div class="creation-text">
                Создавайте уникальные события, наполненные знаниями, творчеством и общением. Приглашайте друзей и
                единомышленников, делитесь своими идеями и воплощайте их в жизнь вместе с нами!
            </div>
            <a href="php/auth_or_creating.php" class="btn1">Создать мероприятие</a>
        </div>
        <!-- /creation -->
    </div>
    <!-- /container -->
    <?php
    require './php/footer.php';
    ?>

    <script src="scripts/carousel.js"></script>
</body>

</html>
