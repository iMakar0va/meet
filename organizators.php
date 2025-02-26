<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/media/media_general.css">
    <link rel="stylesheet" href="styles/events.css">
    <link rel="stylesheet" href="styles/lk.css">

    <title>Организаторы</title>
</head>

<body>
    <?php
    session_start();
    require './php/header.php';
    require './php/conn.php';
    ?>
    <div class="container">
        <div class="search-form">
            <form id="organizerSearchForm" method="GET" action="">
                <input type="text" id="city" name="city" placeholder="Город" autocomplete="off" oninput="capitalizeCity()">
                <input type="text" id="organization_name" name="organization_name" placeholder="Название организации">
                <button type="submit"><img src="./img/icons/search.svg" alt="Найти"></button>
                <button type="button" id="resetButton"><img src="./img/icons/close.svg" alt="Сбросить"></button>
            </form>
        </div>
        <div class="cards" style="margin-top: 25px;">
            <?php

            // Параметризованный запрос для поиска организаторов
            $whereClauses = [];
            $params = [];

            // Поиск по городу
            if (isset($_GET['city']) && !empty($_GET['city'])) {
                $whereClauses[] = "city LIKE '%' || $" . (count($params) + 1) . " || '%'";
                $params[] = $_GET['city'];
            }

            // Поиск по названию организации
            if (isset($_GET['organization_name']) && !empty($_GET['organization_name'])) {
                $whereClauses[] = "name LIKE '%' || $" . (count($params) + 1) . " || '%'";
                $params[] = $_GET['organization_name'];
            }

            // Формирование условия WHERE
            $whereClause =  count($whereClauses) > 0 ? " WHERE " . implode(" AND ", $whereClauses) : "";

            // Запрос на получение организаторов
            $getOrganizers = "SELECT * FROM organizators" . $whereClause;

            $resultGetOrganizers = pg_query_params($conn, $getOrganizers, $params);

            if (!$resultGetOrganizers) {
                echo "Ошибка при получении данных: " . pg_last_error();
            } elseif (pg_num_rows($resultGetOrganizers) == 0) {
            ?>
                <div class="no-results">
                    <img src="./img/icons/not_found.svg" alt="not found">
                    <div>Организаторы не найдены</div>
                </div>
            <?php
            } else {
                while ($row = pg_fetch_assoc($resultGetOrganizers)) {
                    require './php/card_organizator.php'; // Включает шаблон карточки организатора
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
            document.getElementById('organizerSearchForm').reset();
            window.location.href = window.location.pathname;
        });
    </script>
</body>

</html>
