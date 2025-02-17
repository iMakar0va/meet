<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/events.css">
    <link rel="stylesheet" href="styles/media/media_general.css">
    <title>Афиша</title>
</head>

<body>
    <?php
    session_start();
    require './php/header.php';
    require './php/conn.php';
    ?>
    <div class="container">
        <div class="search-form">
            <form id="eventSearchForm" method="GET" action="">
                <input type="text" id="city" name="city" placeholder="Город" autocomplete="off" oninput="capitalizeCity()">
                <select id="type" name="type">
                    <option value="" disabled selected hidden>Выберите тип:</option>
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
                <select id="topic" name="topic">
                    <option value="" disabled selected hidden>Выберите направление:</option>
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
                <input type="date" id="event_date" name="event_date">
                <button type="submit"><img src="./img/icons/search.svg" alt="Найти"></button>
                <button type="button" id="resetButton"><img src="./img/icons/close.svg" alt="Сбросить"></button>
            </form>
        </div>
        <div class="cards" style="margin-top: 25px;">
            <?php
            $whereClauses = [];
            $params = [];

            if (isset($_GET['city']) && !empty($_GET['city'])) {
                $whereClauses[] = "city LIKE '%' || $" . (count($params) + 1) . " || '%'";
                $params[] = $_GET['city'];
            }

            if (isset($_GET['type']) && !empty($_GET['type'])) {
                $whereClauses[] = "type = $" . (count($params) + 1);
                $params[] = $_GET['type'];
            }

            if (isset($_GET['topic']) && !empty($_GET['topic'])) {
                $whereClauses[] = "topic = $" . (count($params) + 1);
                $params[] = $_GET['topic'];
            }

            if (isset($_GET['event_date']) && !empty($_GET['event_date'])) {
                $whereClauses[] = "event_date = $" . (count($params) + 1);
                $params[] = $_GET['event_date'];
            }

            $whereClause =  count($whereClauses) > 0 ? " AND " . implode(" AND ", $whereClauses) : "";
            $getEvents = "SELECT * FROM events WHERE event_date >= CURRENT_DATE and is_active = true"  . $whereClause;

            $resultGetEvents = pg_query_params($conn, $getEvents, $params);

            if (!$resultGetEvents) {
                echo "Ошибка при получении данных: " . pg_last_error();
            } elseif (pg_num_rows($resultGetEvents) == 0) {
            ?>
                <div class="no-results">
                    <img src="./img/icons/not_found.svg" alt="not found">
                    <div>Мероприятий не найдено</div>
                </div>
            <?php
            } else {
                while ($row = pg_fetch_assoc($resultGetEvents)) {
                    require './php/card.php'; // Включает шаблон карточки мероприятия
                }
            }
            pg_close($conn);
            ?>
        </div>
    </div>

    <?php require './php/footer.php'; ?>

    <script>
        // Функция капитализации первой буквы
        function capitalizeCity() {
            const cityInput = document.getElementById('city');
            cityInput.value = cityInput.value.charAt(0).toUpperCase() + cityInput.value.slice(1);
        }

        // Сброс формы поиска
        document.getElementById('resetButton').addEventListener('click', function() {
            document.getElementById('eventSearchForm').reset();
            window.location.href = window.location.pathname;
        });
    </script>
</body>

</html>
