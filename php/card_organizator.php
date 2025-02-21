<div class="card_organizator" data-id="<?= htmlspecialchars($row['organizator_id']) ?>">
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
            <div class="card__content">
                <div class="card__item">Текущих мероприятий: <?= htmlspecialchars($row["name"]) ?></div>
                <div class="card__item">Проведено мероприятий: <?= htmlspecialchars($row["phone_number"]) ?></div>
                <div class="card__item">Отмененных мероприятий: <?= htmlspecialchars($row["email"]) ?></div>
                <div class="card__item">Направления мероприятий:
                    <br>
                    <?= htmlspecialchars($row["date_start_work"]) ?>
                </div>
            </div>
        </div>
        <div class="card_blocks_left">
            <button class="btn1 toggle-button"
                data-id="<?= htmlspecialchars($row['organizator_id']) ?>"
                data-status="<?= $row["is_organizator"] === 't' ? 'true' : 'false' ?>">
                <?= $row["is_organizator"] === 't' ? 'Снять права' : 'Назначить организатором' ?>
            </button>
        </div>
    </div>
    <div class="card__bottom">Описание деятельности: <?= htmlspecialchars($row["description"]) ?></div>
</div>