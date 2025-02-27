<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Профиль организатора</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <div class="organizer-info">
            <div class="organizer-details">
                <h2>Имя организатора</h2>
                <p><strong>Почта:</strong> example@mail.com</p>
                <p><strong>Телефон:</strong> +7 (999) 123-45-67</p>
                <p><strong>Дата регистрации:</strong> 01.01.2023</p>
                <p><strong>Статус:</strong> Активен</p>
                <p class="description">Краткое описание деятельности организатора.</p>
            </div>
            <div class="event-stats">
                <div class="stat">
                    <span>Пройденные мероприятия:</span>
                    <strong>0</strong>
                </div>
                <div class="stat">
                    <span>Текущие мероприятия:</span>
                    <strong>0</strong>
                </div>
                <div class="stat">
                    <span>Отмененные мероприятия:</span>
                    <strong>0</strong>
                </div>
            </div>
        </div>

        <div class="event-menu">
            <button class="tab-button active" data-tab="active">Активные</button>
            <button class="tab-button" data-tab="past">Прошедшие</button>
        </div>

        <div class="event-list" id="active-events">
            <p>Здесь будут активные мероприятия...</p>
        </div>
        <div class="event-list hidden" id="past-events">
            <p>Здесь будут прошедшие мероприятия...</p>
        </div>
    </div>

    <script>
        document.querySelectorAll('.tab-button').forEach(button => {
            button.addEventListener('click', function() {
                document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');

                document.querySelectorAll('.event-list').forEach(list => list.classList.add('hidden'));
                document.getElementById(this.dataset.tab + '-events').classList.remove('hidden');
            });
        });
    </script>
</body>
</html>
