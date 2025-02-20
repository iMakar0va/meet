<div class="card" data-id="<?= htmlspecialchars($row['organizator_id']) ?>">
    <div class="card__content">
        <div class="card__item"><?= htmlspecialchars($row["name"]) ?></div>
        <div class="card__item"><?= htmlspecialchars($row["phone_number"]) ?></div>
        <div class="card__item"><?= htmlspecialchars($row["email"]) ?></div>
        <div class="card__item"><?= htmlspecialchars($row["date_start_work"]) ?></div>
        <div class="card__item"><?= htmlspecialchars($row["description"]) ?></div>
        <div class="card__item status" data-id="<?= htmlspecialchars($row['organizator_id']) ?>">
            <?= $row["is_organizator"] === 't' ? '✅ Организатор' : '❌ Не организатор' ?>
        </div>
    </div>
    <button class="btn1 toggle-button"
            data-id="<?= htmlspecialchars($row['organizator_id']) ?>"
            data-status="<?= $row["is_organizator"] === 't' ? 'true' : 'false' ?>">
        <?= $row["is_organizator"] === 't' ? 'Снять права' : 'Назначить организатором' ?>
    </button>
</div>
