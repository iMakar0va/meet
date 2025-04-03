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

        $stmt = pg_prepare($conn, "insert_user", "insert into users(last_name, first_name, email, password, birth_date, gender) VALUES($1, $2, $3, $4, $5, $6) RETURNING user_id;");
        $resultInsert = pg_execute($conn, "insert_user", [
            $last_name,
            $first_name,
            $email,
            $hashedPassword,
            $birth_date,
            $gender
        ]);

        if ($resultInsert) {
            $row = pg_fetch_assoc($resultInsert);
            $userId = $row['user_id'];
            $_SESSION['user_id'] = $userId;
            // setcookie("user_id", $userId, time() + 3600 * 24 * 30, "/");

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
    <link rel="stylesheet" href="styles/auth.css">
    <link rel="stylesheet" href="styles/media/media_auth.css">
    <title>Подтверждение</title>
    <style>
        form {
            text-align: center;
        }

        h1 {

            font-weight: bold;
            font-size: 34px;
        }

        .ap-otp-inputs {
            text-align: center;
        }

        .ap-otp-input {
            border: 3px solid #ebebeb;
            border-radius: 5px;
            width: 68px;
            height: 73px;
            margin: 4px;
            text-align: center;
            font-size: 24px;
            color: var(--black-color);
        }

        .ap-otp-input:focus {
            outline: none !important;
            border: 3px solid #02494C;
            transition: 0.12s ease-in;
        }

        @media (max-width: 560px) {
            h1 {
                font-size: 28px;
            }

            .ap-otp-input {
                width: 54px;
                height: 65px;

            }

            .form {
                padding: 20px;
            }
        }

        @media (max-width: 462px) {
            h1 {
                font-size: 26px;
            }

            .ap-otp-input {
                width: 34px;
                height: 45px;
            }
        }

        @media (max-width: 342px) {
            h1 {
                font-size: 22px;
            }

            .ap-otp-input {
                width: 30px;
                height: 35px;
            }
        }
    </style>
</head>

<body>
    <?php
    require "./php/header.php";
    ?>
    <div class="container">
        <div class="form">
            <form method="post">
                <h1>Пожалуйста, проверьте свою электронную почту!</h1>
                <h3 class="title3">Мы отправили вам 6-значный код подтверждения. Пожалуйста, введите код в поле ниже, чтобы подтвердить свой адрес электронной почты. <br> Если код не пришел <b>проверьте папку "Спам"</b> или отправьте код повторно.</h3>
                <div class="ap-otp-inputs" data-username="otibij" data-channel="email" data-nonce="0-8bf87e338f" data-length="6" data-form="registration">
                    <input class="ap-otp-input" type="text" maxlength="1" data-index="0" inputmode="numeric" name="input1">
                    <input class="ap-otp-input" type="text" maxlength="1" data-index="1" inputmode="numeric" name="input2">
                    <input class="ap-otp-input" type="text" maxlength="1" data-index="2" inputmode="numeric" name="input3">
                    <input class="ap-otp-input" type="text" maxlength="1" data-index="3" inputmode="numeric" name="input4">
                    <input class="ap-otp-input" type="text" maxlength="1" data-index="4" inputmode="numeric" name="input5">
                    <input class="ap-otp-input" type="text" maxlength="1" data-index="5" inputmode="numeric" name="input6">
                </div>
                <button type="submit" class="btn1">Подтвердить</button>
                <!-- <h4 class="title3"></h4> -->
                <a href="./reg.php">Вернуться к входу</a>
            </form>
        </div>
    </div>


    <?php
    require "./php/footer.php";
    ?>
    <script src='https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js'></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const inputs = document.querySelectorAll('.ap-otp-input');

            inputs.forEach((input, index) => {
                input.addEventListener('input', (e) => {
                    let value = e.target.value.replace(/\D/g, ''); // Оставляем только цифры
                    e.target.value = value.slice(0, 1); // Ограничиваем ввод одной цифрой

                    if (value && inputs[index + 1]) {
                        inputs[index + 1].focus();
                    }
                });

                input.addEventListener('keydown', (e) => {
                    if (e.key === "Backspace" && !input.value && inputs[index - 1]) {
                        inputs[index - 1].focus();
                    }
                });
            });

            document.addEventListener('paste', (ev) => {
                const clip = ev.clipboardData.getData('text').trim();
                if (!/^\d{6}$/.test(clip)) return ev.preventDefault();

                [...clip].forEach((char, i) => {
                    if (inputs[i]) inputs[i].value = char;
                });

                inputs[5].focus();
            });
        });
    </script>
</body>

</html>