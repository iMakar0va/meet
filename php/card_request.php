<!-- <div class="card_organizator" data-id="<?= htmlspecialchars($row['organizator_id']) ?>">
    <div class="card_blocks">
        <div class="card_blocks_right">
            <div class="card__content">
                <div class="card__item item__title"><?= htmlspecialchars($row["name"]) ?></div>
                <div class="card__item"><?= htmlspecialchars($row["email"]) ?></div>
                <div class="card__item"><?= htmlspecialchars($row["phone_number"]) ?></div>
                <div class="card__item">Дата регистрации: <?= htmlspecialchars($row["date_start_work"]) ?></div>
                <div class="card__item status" data-id="<?= htmlspecialchars($row['organizator_id']) ?>">
                    Статус: <?= $row["is_organizator"] === 't' ? 'Организатор' : 'Не организатор' ?>
                </div>
            </div>
        </div>
        <div class="card_blocks_left">
            <button class="btn1 approve-button" data-id="<?= htmlspecialchars($row['organizator_id']) ?>">Одобрить</button>
            <button class="btn1 delete-button" data-id="<?= htmlspecialchars($row['organizator_id']) ?>">Отклонить</button>
        </div>
    </div>
    <div class="card__bottom">Описание деятельности: <?= htmlspecialchars($row["description"]) ?></div>
</div> -->

<div class="card_organizator" data-id="<?= htmlspecialchars($row['organizator_id']) ?>">
    <div class="card__title"><?= htmlspecialchars($row["name"]) ?></div>
    <div class="card__blocks">
        <div class="card__block">
            <div class="card__item"><?= htmlspecialchars($row["email"]) ?></div>
            <div class="card__item"><?= htmlspecialchars($row["phone_number"]) ?></div>
            <div class="card__item">Дата регистрации: <?= htmlspecialchars($row["date_start_work"]) ?></div>
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