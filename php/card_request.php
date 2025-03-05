<div class="card_organizator" data-id="<?= htmlspecialchars($row['organizator_id']) ?>">
    <div class="card__title"><?= htmlspecialchars($row["name"]) ?></div>
    <div class="card__blocks">
        <div class="card__block">
            <div class="card__item"><?= htmlspecialchars($row["email"]) ?></div>
            <div class="card__item"><?= htmlspecialchars($row["phone_number"]) ?></div>
            <div class="card__item">Дата основания: <?= htmlspecialchars($row["date_start_work"]) ?></div>
            <div class="card__item" data-id="<?= htmlspecialchars($row['organizator_id']) ?>">
                Статус: <?= $row["is_organizator"] === 't' ? 'Организатор' : 'Не организатор' ?>
            </div>
        </div>
        <div class="card__block">
            <button class="btn1 approve-button" data-id="<?= htmlspecialchars($row['organizator_id']) ?>">Одобрить</button>
            <button class="btn1 delete-button toggle-button" data-id="<?= htmlspecialchars($row['organizator_id']) ?>">Отклонить</button>
        </div>
    </div>
    <div class="card__item long" style="font-weight: 300;"><b>Описание деятельности: </b><?= htmlspecialchars($row["description"]) ?></div>

</div>