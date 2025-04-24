<footer>
    <div class="container">
        <div class="footer__wrapper">
            <div class="footer__block">
                <div class="logo">
                    <a href="main.php"><img src="img/icons/logo.svg" alt="logo"></a>
                </div>
                <div class="footer-title">Поддержка</div>
                <div class="footer-help">
                    <a href="#!">+7 921 399 98 56</a>
                    <a href="#!">eno7i@yndex.ru</a>
                </div>
            </div>
            <!-- /footer__block -->
            <div class="footer__block">
                <a href="document/пользовательское соглашение.docx">Пользовательское соглашение</a><br><br>
                <a href="document/политика обработки данных.docx">Политика обработки персональных данных</a><br><br>
                <a href="document/политика конфиденциальности.docx">Политика конфиденциальности</a>
            </div>
            <!-- /footer__block -->
        </div>
        <!-- /footer__wrapper -->
        <div class="footer__cookie">
            <div>
                Мы используем cookie-файлы, чтобы улучшать сервисы для вас. Оставаясь на сайте, вы соглашаетесь на сбор и <a href="document/политика обработки данных.docx">обработку этих данных</a>.
            </div>
        </div>
        <!-- /footer__cookie -->
    </div>
    <!-- /container -->
</footer>
<!-- Баннер о куки -->
<div id="cookie-banner" class="cookie-banner">
    <p>Наш сайт использует cookie-файлы, чтобы сделать наши сервисы быстрее и удобнее.
        Продолжая им пользоваться вы принимаете условия <a href="document/пользовательское соглашение.docx">Пользовательского соглашения</a> и соглашаетесь со сбором cookie-файлов. Подробности про отработку данных — в нашей <a href="document/политика обработки данных.docx">Политике обрабоки персональных данных</a></p>
    <button id="accept-cookies">Согласиться</button>
</div>

<style>
    /* Стили баннера */
    .cookie-banner {
        position: fixed;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(0, 0, 0, 0.85);
        color: white;
        padding: 15px 20px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        max-width: 80%;
        font-size: 14px;
        z-index: 1000;
        box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.26);
    }

    .cookie-banner p {
        color: white;
    }

    .cookie-banner a {
        color: #ff9800;
    }

    .cookie-banner a:hover {
        text-decoration: underline;
    }

    .cookie-banner button {
        background: #ff9800;
        color: black;
        border: none;
        padding: 8px 12px;
        margin-left: 15px;
        border-radius: 5px;
        cursor: pointer;
        transition: background 0.3s;
    }

    .cookie-banner button:hover {
        background: rgb(186, 114, 5);
    }
</style>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const banner = document.getElementById("cookie-banner");
        const acceptButton = document.getElementById("accept-cookies");

        // Проверяем, есть ли куки о согласии
        if (localStorage.getItem("cookiesAccepted")) {
            banner.style.display = "none"; // Если уже согласился, скрываем баннер
        }

        acceptButton.addEventListener("click", function() {
            localStorage.setItem("cookiesAccepted", "true"); // Запоминаем согласие
            banner.style.display = "none"; // Скрываем баннер
        });
    });
</script>