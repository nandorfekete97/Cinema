<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Screening $model */
/** @var array $seatLayout */
/** @var array $soldSeats */

$this->title = 'Buy tickets – ' . $model->movie_title;
$this->params['breadcrumbs'][] = ['label' => 'Home', 'url' => ['/ticket/index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<h1><?= Html::encode($this->title) ?></h1>

<p>
    Date: <?= Yii::$app->formatter->asDate($model->screening_date) ?> |
    Time: <?= Yii::$app->formatter->asTime($model->start_time) ?> |
    Price: <?= Yii::$app->formatter->asDecimal($model->ticket_price, 2) ?> €
</p>

<?php $form = ActiveForm::begin([
        'action' => ['ticket/buy', 'id' => $model->id],
        'method' => 'post',
]); ?>

<!-- Hidden field that will contain selected seat numbers like: "12,15,18" -->
<input type="hidden" name="seats" id="selected-seats">

<div class="seat-layout">
    <h2>MOVIE THEATRE</h2>

    <div class="seat-layout-grid">

        <!-- Top-left corner cell -->
        <div class="corner-cell">
            <div class="corner-col">COLUMN</div>
            <div class="corner-row">ROW</div>
        </div>

        <!-- Column labels -->
        <?php foreach (range('A', 'N') as $col): ?>
            <div class="col-label"><?= $col ?></div>
        <?php endforeach; ?>

        <!-- Seat rows -->
        <?php foreach ($seatLayout as $row => $seats): ?>

            <!-- Row label -->
            <div class="row-label"><?= $row ?></div>

            <!-- Seats -->
            <?php foreach (range('A', 'N') as $col): ?>
                <?php
                // Check if a seat exists at this row/column
                $seat = $seats[$col] ?? null;

                if ($seat === null) {
                    // No seat here, just a placeholder to keep grid alignment
                    echo '<div class="seat-empty"></div>';
                } else {
                    $seatNumber = $seat['number'];
                    $isSold = isset($soldSeats[$seatNumber]);
                    ?>
                    <div
                            class="seat <?= $isSold ? 'seat-sold' : 'seat-free' ?>"
                            data-seat="<?= $seatNumber ?>"
                            <?= $isSold ? '' : 'onclick="toggleSeat(this)"' ?>
                    >
                        <?= $seatNumber ?>
                    </div>
                <?php } ?>
            <?php endforeach; ?>

        <?php endforeach; ?>

    </div>
</div>

<hr>

<div class="buyer-form">
    <h3>Buyer information</h3>

    <div class="form-group">
        <label>Name</label>
        <input type="text" name="buyer_name" class="form-control" required>
    </div>

    <div class="form-group">
        <label>Phone</label>
        <input type="text" name="buyer_phone" class="form-control" required>
    </div>

    <div class="form-group">
        <label>Email</label>
        <input type="email" name="buyer_email" class="form-control" required>
    </div>
</div>

<hr>

<div class="form-group">
    <?= Html::submitButton('Buy Tickets', [
            'class' => 'btn btn-success',
            'id' => 'buy-button',
            'disabled' => true,
    ]) ?>
</div>

<?php ActiveForm::end(); ?>

<script>
    let selectedSeats = [];

    /*
     This function is called when a free seat is clicked.
     It toggles selection on/off and keeps the hidden input updated.
    */
    function toggleSeat(el) {
        const seatNumber = el.dataset.seat;

        if (el.classList.contains('seat-selected')) {
            // Unselect
            el.classList.remove('seat-selected');
            selectedSeats = selectedSeats.filter(s => s !== seatNumber);
        } else {
            // Select
            if (selectedSeats.length >= 10) {
                alert('You can select a maximum of 10 seats.');
                return;
            }
            el.classList.add('seat-selected');
            selectedSeats.push(seatNumber);
        }

        // Update hidden input value
        document.getElementById('selected-seats').value = selectedSeats.join(',');

        // Enable button only if at least one seat is selected
        document.getElementById('buy-button').disabled = selectedSeats.length === 0;
    }
</script>
