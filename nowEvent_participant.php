<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <link rel="stylesheet" href="styles/auth.css">
    <link rel="stylesheet" href="styles/lk.css">
    <link rel="stylesheet" href="styles/media/media_auth.css">
    <link rel="stylesheet" href="styles/media/media_lk.css">
    <title>Личный кабинет</title>
</head>

<body>
    <?php
    require './php/conn.php'; // Здесь подключаем PDO
    require './php/header.php';


    session_start();
    if (!isset($_SESSION['user_id'])) {
        header("Location: auth.php");
        exit();
    }
    ?>

    <div class="container">
        <div class="lk">
            <?php require 'php/lk/lk_menu.php'; ?>
            <div class="lk__profile">
                <h1 class="title1">Текущие мероприятия</h1>
                <div class="cards">
                    <?php
                    // Подготовка запроса с использованием PDO
                    $userId = $_SESSION['user_id'];
                    $getEventUser = "SELECT * FROM users u
                                     JOIN user_events ue ON u.user_id = ue.user_id
                                     JOIN events e ON ue.event_id = e.event_id
                                     WHERE u.user_id = :user_id AND e.event_date > CURRENT_DATE;";

                    // Подготовка и выполнение запроса
                    try {
                        $stmt = $pdo->prepare($getEventUser);
                        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
                        $stmt->execute();

                        if ($stmt->rowCount() > 0) {
                            // Если есть записи, выводим карточки
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                require './php/card.php';
                            }
                        } else {
                            echo "<p>Нет текущих мероприятий для отображения.</p>";
                        }
                    } catch (PDOException $e) {
                        // Обработка ошибок при выполнении запроса
                        echo "Ошибка: " . $e->getMessage();
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <?php
    // Не нужно закрывать соединение вручную с PDO, оно закрывается автоматически при завершении скрипта
    require './php/footer.php';
    ?>
</body>

</html>
