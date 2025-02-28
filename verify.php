<?php
session_start();
require './php/conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input1 = isset($_POST['input1']) ? $_POST['input1'] : '';
    $input2 = isset($_POST['input2']) ? $_POST['input2'] : '';
    $input3 = isset($_POST['input3']) ? $_POST['input3'] : '';
    $input4 = isset($_POST['input4']) ? $_POST['input4'] : '';
    $input5 = isset($_POST['input5']) ? $_POST['input5'] : '';
    $input6 = isset($_POST['input6']) ? $_POST['input6'] : '';

    $inputCode = $input1 . $input2 . $input3 . $input4 . $input5 . $input6;

    if (!isset($_SESSION['verification_code']) || empty($inputCode)) {
        echo 'Ошибка. Код не найден.';
        exit;
    }
    $savedCode = $_SESSION['verification_code'];
    if ($inputCode === (string)$savedCode) {
        $email = $_SESSION['reg_data']['email'];
        $last_name = $_SESSION['reg_data']['last_name'];
        $first_name = $_SESSION['reg_data']['first_name'];
        $hashedPassword = $_SESSION['reg_data']['hashedPassword'];
        $birth_date = $_SESSION['reg_data']['birth_date'];
        $gender = $_SESSION['reg_data']['gender'];
        $image = $_SESSION['reg_data']['image'];


        $stmt = pg_prepare($conn, "insert_user", "insert into users(last_name, first_name, email, password, birth_date, gender, image) VALUES($1, $2, $3, $4, $5, $6, $7) RETURNING user_id;");
        $resultInsert = pg_execute($conn, "insert_user", [
            $last_name,
            $first_name,
            $email,
            $hashedPassword,
            $birth_date,
            $gender,
            $image
        ]);

        if ($resultInsert) {
            $row = pg_fetch_assoc($resultInsert);
            $userId = $row['user_id'];
            $_SESSION['user_id'] = $userId;
            setcookie("user_id", $userId, time() + 3600 * 24 * 30, "/");

            unset($_SESSION['verification_code']);
            unset($_SESSION['reg_data']);

            header('Location: lk.php');
            exit;
        } else {
            echo 'Ошибка добавления в БД';
        }
    } else {
        echo 'Неверный код!';
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/general.css">
    <title>Подтверждение</title>
    <style>
        body {
            background-color: #44969a;
        }

        form {
            text-align: center;
            height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 50px;
        }

        h1 {

            font-weight: bold;
            font-size: 39px;
        }

        .ap-otp-inputs {
            text-align: center;
        }

        .ap-otp-input {
            border: 3px solid #ebebeb;
            border-radius: 18px;
            width: 10%;
            height: 100px;
            margin: 4px;
            text-align: center;
            font-size: 35px;
        }

        .ap-otp-input:focus {
            outline: none !important;
            border: 3px solid #02494C;
            transition: 0.12s ease-in;
        }
    </style>
</head>

<body>
    <form method="post">
        <h1>Пожалуйста, проверьте свою электронную почту!</h1>
        <h3 class="title3">Мы отправили вам 6-значный код подтверждения. Пожалуйста, введите код в поле ниже, чтобы подтвердить свой адрес электронной почты.</h3>
        <div class="ap-otp-inputs" data-username="otibij" data-channel="email" data-nonce="0-8bf87e338f" data-length="6" data-form="registration">
            <input class="ap-otp-input" type="text" maxlength="1" data-index="0" inputmode="numeric" name="input1">
            <input class="ap-otp-input" type="text" maxlength="1" data-index="1" inputmode="numeric" name="input2">
            <input class="ap-otp-input" type="text" maxlength="1" data-index="2" inputmode="numeric" name="input3">
            <input class="ap-otp-input" type="text" maxlength="1" data-index="3" inputmode="numeric" name="input4">
            <input class="ap-otp-input" type="text" maxlength="1" data-index="4" inputmode="numeric" name="input5">
            <input class="ap-otp-input" type="text" maxlength="1" data-index="5" inputmode="numeric" name="input6">
        </div>
        <button type="submit" class="btn1">Подтвердить</button>
        <h4 class="title3">Если код не пришел <b>проверьте папку "Спам"</b> или отправьте код повторно</h4>
        <a href="./reg.php">Вернуться к входу</a>
    </form>
    <script src='https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js'></script>
    <script>
        // Сброс пароля
        const $inp = $(".ap-otp-input");

        $inp.on({
            paste(ev) {
                const clip = ev.originalEvent.clipboardData.getData('text').trim();

                if (!/\d{6}/.test(clip)) return ev.preventDefault();

                const s = [...clip];

                $inp.val(i => s[i]).eq(5).focus();
            },
            input(ev) {

                const i = $inp.index(this);
                if (this.value) $inp.eq(i + 1).focus();
            },
            keydown(ev) {
                const i = $inp.index(this);
                if (!this.value && ev.key === "Backspace" && i) $inp.eq(i - 1).focus();
            }

        });
    </script>
</body>

</html>