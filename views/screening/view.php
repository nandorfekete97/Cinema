<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\Screening $model */
/** @var app\controllers\ScreeningController $seatLayout */
/** @var app\controllers\ScreeningController $soldCount */
/** @var app\models\Ticket[] $ticketsForScreening */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Screenings'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="screening-view">

    <?php if (!$model->getTickets()->exists()): ?>
    <p>
        <?= Html::a(Yii::t('app', 'Update'), ['update', 'id' => $model->id], [
                'class' => 'btn btn-primary'
            ])
        ?>
        <?= Html::a(Yii::t('app', 'Delete'), ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?php else: ?>

    <p>
        <span class="text-muted">
            This screening can no longer be modified because tickets have already been sold.
        </span>
    </p>

    <?php endif; ?>

    <div class="screening-info">

        <div class="screening-info-left">
            <div><strong>Movie Title:</strong> <?= Html::encode($model->movie_title) ?></div>

            <div>
                <strong>Date:</strong>
                <?= Yii::$app->formatter->asDate($model->screening_date) ?>
            </div>

            <div>
                <strong>Start Time:</strong>
                <?= Yii::$app->formatter->asTime($model->start_time) ?>
            </div>

            <div>
                <strong>End Time:</strong>
                <?= Yii::$app->formatter->asTime($model->end_time) ?>
            </div>

            <div>
                <strong>Ticket Price:</strong>
                <?= Yii::$app->formatter->asDecimal($model->ticket_price, 2) ?> €
            </div>
        </div>

        <div class="screening-info-right">
            <div><strong>Sold Tickets Count:</strong> <?= $soldCount ?> </div>
            <div><strong>Screening Income:</strong> <?= $soldCount * $model->ticket_price ?> €</div>
        </div>

    </div>

<div class="seat-layout">
    <h1> MOVIE THEATRE </h1>
    <div class="seat-layout-grid">

        <div class="corner-cell"></div>

        <?php foreach (range('A', 'N') as $col): ?>
            <div class="col-label"><?= $col ?></div>
        <?php endforeach; ?>

        <?php foreach ($seatLayout as $row => $seats): ?>

            <div class="row-label"><?= $row ?></div>

            <?php foreach (range('A', 'N') as $col): ?>
                <?php
                $seat = $seats[$col] ?? null;

                if ($seat === null) {
                    echo '<div class="seat-empty"></div>';
                } else {
                    $seatNumber = $seat['number'];
                    $isSold = isset($soldSeats[$seatNumber]);
                    ?>
                    <div class="seat <?= $isSold ? 'seat-sold' : 'seat-free' ?>"
                         data-seat="<?= $seatNumber ?>">
                        <?= $seatNumber ?>
                    </div>
                <?php } ?>
            <?php endforeach; ?>

        <?php endforeach; ?>
    </div>
</div>

<div class="tickets-for-screening">
    <h3>Tickets for this screening</h3>

    <?php if (empty($ticketsForScreening)): ?>
        <p>No tickets sold yet.</p>
    <?php else: ?>
        <?php foreach ($ticketsForScreening as $ticket): ?>
            <div class="ticket-for-screening">
                Seat: <?=$ticket->seat_number ?> |
                Buyer: <?= Html::encode($ticket->buyer_name) ?> |
                Phone: <?= Html::encode($ticket->buyer_phone) ?>
            </div>
        <?php endforeach;?>
    <?php endif; ?>
</div>
