<div class="card">
    <div class="card__content">
        <div class="card__item"><?= htmlspecialchars($row["name"]) ?></div>
        <div class="card__item"><?= htmlspecialchars($row["phone_number"]) ?></div>
        <div class="card__item"><?= htmlspecialchars($row["email"]) ?></div>
        <div class="card__item"><?= htmlspecialchars($row["date_start_work"]) ?></div>
        <div class="card__item"><?= htmlspecialchars($row["description"]) ?></div>
    </div>
    <button class="btn1 approve-button" data-id="<?= htmlspecialchars($row['organizator_id']) ?>">Одобрить</button>
    <button class="btn1 delete-button" data-id="<?= htmlspecialchars($row['organizator_id']) ?>">Удалить</button>
</div>
