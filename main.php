<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- <link rel="icon" type="image/png" href="./img/icons/logo.svg">
    <meta name="theme-color" content="#000"> -->
    <link rel="stylesheet" href="styles/main.css">
    <link rel="stylesheet" href="styles/media/media_main.css">
    <title>Главная</title>
</head>

<body>
    <?php
    session_start();
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
                    $getUsers = "select count(*) from users;";
                    $resultGetUsers = pg_query($conn, $getUsers);
                    $row = pg_fetch_row($resultGetUsers);
                    $count = $row[0];
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
                    $getUsers = "select count(*) from events where is_active = true and is_approved = true and event_date < CURRENT_DATE;";
                    $resultGetUsers = pg_query($conn, $getUsers);
                    $row = pg_fetch_row($resultGetUsers);
                    $count = $row[0];
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
            $getEvents = "SELECT e.event_id,e.image,e.title,e.type,e.topic,e.description, e.start_time, e.end_time, e.event_date, e.city, e.address, e.organizer, e.place, e.phone, e.email, COUNT(ue.user_id) AS participants_count FROM events e LEFT JOIN user_events ue ON e.event_id = ue.event_id WHERE e.event_date >= CURRENT_DATE and e.is_active = true and e.is_approved = true GROUP BY e.event_id ORDER BY participants_count DESC LIMIT 3;";
            $resultGetEvents = pg_query($conn, $getEvents);
            if ($resultGetEvents) {
                while ($row = pg_fetch_assoc($resultGetEvents)) {
                    require './php/card.php';
                }
            } else {
                echo "Ошибка при получении данных: " . pg_last_error();
            }
            pg_close($conn);
            ?>
        </div>
        <!-- /cards -->

        <div class="creation">
            <div class="creation__block">
                <div class="creation-title">Создайте своё мероприятие</div>
                <div class="creation-text">
                    Создавайте уникальные события, наполненные знаниями, творчеством и общением. Приглашайте друзей и
                    единомышленников, делитесь своими идеями и воплощайте их в жизнь вместе с нами!
                </div>
                <a href="php/auth_or_creating.php" class="btn1">Создать мероприятие</a>
            </div>
            <img src="img/create.svg" alt="create" style="width: 250px;">
        </div>
        <!-- /creation -->

        <div class="faq-container">
            <div class="faq-title">Часто задаваемые вопросы</div>
            <div class="faq-content">
                <div class="faq-item">
                    <div class="faq-question">Как стать организатором? <span class="faq-icon">+</span></div>
                    <div class="faq-answer">Чтобы стать организатором, сначала зарегистрируйтесь на сайте как участник. Затем в личном кабинете нажмите кнопку "Стать организатором", заполните необходимые данные формы и отправьте заявку. После этого останется только дождаться решения администратора.</div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">Как создать мероприятие? <span class="faq-icon">+</span></div>
                    <div class="faq-answer">Став организатором, вы сможете создавать мероприятия прямо в личном кабинете. Просто выберите "Создать мероприятие" в меню и заполните все необходимые данные. Учтите, что перед публикацией каждое мероприятие проходит проверку администратора.</div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">Как изменить данные организатора? <span class="faq-icon">+</span></div>
                    <div class="faq-answer">Редактирование данных организатора доступно только администратору. Если вам необходимо внести изменения, свяжитесь с нами по электронной почте: <b>eno7i@yandex.com</b> .</div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">Я был организатором, но теперь функция создания мероприятия недоступна. Почему? <span class="faq-icon">+</span></div>
                    <div class="faq-answer">Если доступ к функциям организатора был ограничен, это могло произойти по решению администрации. В таком случае вам должно прийти уведомление с объяснением причины. Если у вас остались вопросы, свяжитесь с нами.</div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">Как отметить свое присутствие на мероприятии? <span class="faq-icon">+</span></div>
                    <div class="faq-answer">
                        Для того чтобы отметить свое присутствие на мероприятии, вы можете воспользоваться одним из следующих методов: <br>
                        1) Вы можете сканировать свой <b>QR-код</b>, который будет доступен в вашем личном кабинете<br>
                        2) Если у вас нет возможности использовать QR-код, предоставьте свой <b>ID пользователя</b>, и организаторы смогут вручную отметить ваше присутствие на мероприятии. <br>
                        3) Также возможно использовать ваш <b>email</b>, если это предусмотрено организатором мероприятия, для автоматической отметки присутствия. <br>
                        Важно: ваше присутствие будет засчитано только после подтверждения со стороны организатора или системы. <br>
                    </div>
                </div>


            </div>
        </div>

    </div>
    <!-- /container -->
    <?php
    require './php/footer.php';
    ?>

    <script src="scripts/main.js"></script>
</body>

</html>