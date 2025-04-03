<?php
session_start();
require './php/header.php';
require './php/conn.php';

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit();
}

$userId = $_SESSION['user_id'];

// Проверка, является ли пользователь администратором
$queryAdmin = "SELECT 1 FROM users WHERE user_id = $1 AND is_admin = true";
$resultAdmin = pg_query_params($conn, $queryAdmin, [$userId]);

if (!$resultAdmin || pg_num_rows($resultAdmin) == 0) {
    header("Location: lk.php");
    exit();
}

// Получаем ID мероприятия из GET-параметров
$eventId = $_GET['event_id'] ?? null;
if (!$eventId) {
    die("Ошибка: отсутствует идентификатор мероприятия.");
}

// Фильтрация по ID пользователя и email
$whereClauses = ["ue.event_id = $1", "ue.is_signed = true"]; // Обязательные условия
$params = [$eventId];

if (!empty($_GET['user_id'])) {
    $whereClauses[] = "u.user_id = $" . (count($params) + 1);
    $params[] = intval($_GET['user_id']);
}
if (!empty($_GET['email'])) {
    $whereClauses[] = "u.email ILIKE $" . (count($params) + 1);
    $params[] = '%' . $_GET['email'] . '%';
}

$whereClause = " WHERE " . implode(" AND ", $whereClauses);

// Запрос на получение пользователей, записанных на мероприятие
$getUsersQuery = "
    SELECT u.user_id, u.email, ue.presense
    FROM users u
    JOIN user_events ue ON u.user_id = ue.user_id
    $whereClause
    ORDER BY u.user_id
";
$resultUsers = pg_query_params($conn, $getUsersQuery, $params);
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Список участников</title>
    <link rel="stylesheet" href="styles/lk.css">
    <link rel="stylesheet" href="styles/events.css">
    <link rel="stylesheet" href="styles/media/media_auth.css">
    <link rel="stylesheet" href="styles/media/media_lk.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        table {
            color: black;
        }

        table button {
            width: 100%;
        }

        thead {
            color: white;
            background-color: var(--blue-color);
        }

        th:first-child,
        td:first-child {
            width: 10%;
            text-align: center;
        }

        th:last-child,
        td:last-child {
            width: 30%;
            text-align: center;
        }

        tr:hover td {
            background-color: rgb(166, 213, 230);
        }

        td:last-child:hover {
            color: var(--blue-color);
        }

        th {
            padding: 5px;
        }

        tbody tr,
        tbody th,
        tbody td {
            text-align: center;
            padding: 5px;
            border-bottom: 3px solid var(--blue-color);
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="lk">
            <?php require 'php/lk/lk_menu.php'; ?>
            <div class="lk__profile">
                <div class="title1">Список участников мероприятия</div>
                <div class="search-form">
                    <form id="userSearchForm" method="GET">
                        <input type="hidden" name="event_id" value="<?= htmlspecialchars($eventId) ?>">
                        <input type="text" name="user_id" placeholder="ID пользователя" value="<?= htmlspecialchars($_GET['user_id'] ?? '') ?>">
                        <input type="text" name="email" placeholder="Почта пользователя" value="<?= htmlspecialchars($_GET['email'] ?? '') ?>">
                        <button type="submit"><img src="./img/icons/search.svg" alt="Найти"></button>
                        <button type="button" id="resetButton"><img src="./img/icons/close.svg" alt="Сбросить"></button>
                    </form>
                </div>

                <table border="1" class="user-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Email</th>
                            <th>Статус присутствия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($resultUsers && pg_num_rows($resultUsers) > 0) {
                            while ($row = pg_fetch_assoc($resultUsers)) {
                                $presense = $row['presense'] == 't' ? 'Присутствует' : 'Не присутствует';
                                echo "<tr>";
                                echo "<td>{$row['user_id']}</td>";
                                echo "<td>{$row['email']}</td>";
                                echo "<td>
                                <button class='presense-button' data-user='{$row['user_id']}' data-presense='{$row['presense']}'>
                                    {$presense}
                                </button>
                            </td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='3'>Записанных пользователей нет</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('.presense-button').click(function() {
                let userId = $(this).data('user');
                let currentPresense = $(this).data('presense');
                let newPresense = currentPresense === 'true' ? 'false' : 'true';
                let newText = newPresense === 'true' ? 'Присутствует' : 'Не присутствует';

                $(this).text(newText);
                $(this).data('presense', newPresense);

                $.ajax({
                    url: 'update_presense.php',
                    type: 'POST',
                    data: {
                        user_id: userId,
                        event_id: <?= json_encode($eventId) ?>,
                        presense: newPresense
                    },
                    success: function(response) {
                        console.log(response);
                    },
                    error: function() {
                        alert("Ошибка обновления статуса");
                    }
                });
            });

            $('#resetButton').click(function() {
                window.location.href = "listUserEvent.php?event_id=<?= htmlspecialchars($eventId) ?>";
            });
        });
    </script>

</body>

</html>


<?php pg_close($conn); ?>