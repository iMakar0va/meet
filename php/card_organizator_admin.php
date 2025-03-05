<?php
$organizerId = htmlspecialchars($row['organizator_id']);
?>
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
    </div>
    <div class="card__item long" style="font-weight: 300;"><b>Описание деятельности: </b><?= htmlspecialchars($row["description"]) ?></div>
    <div class="card__btns">
        <a href="changeOrganizator.php?organizator_id=<?= htmlspecialchars($row['organizator_id']) ?>" class="btn1">Изменить данные</a>
        <a href="organizatorEventNow.php?organizator_id=<?= htmlspecialchars($row['organizator_id']) ?>" class="btn1">Подробнее</a>
        <button class="btn1 toggle-button"
            data-id="<?= htmlspecialchars($row['organizator_id']) ?>"
            data-status="<?= $row["is_organizator"] === 't' ? 'true' : 'false' ?>">
            <?= $row["is_organizator"] === 't' ? 'Снять права' : 'Дать права' ?>
        </button>
    </div>
</div>