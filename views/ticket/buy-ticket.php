<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Screening $model */
/** @var array $seatLayout */
/** @var array $soldSeats */

$this->title = 'Buy tickets – ' . $model->movie_title;
$this->params['breadcrumbs'][] = ['label' => 'Buy Tickets', 'url' => ['/ticket/index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<h1><?= Html::encode($this->title) ?></h1>

<?php $form = ActiveForm::begin([
        'action' => ['ticket/buy-ticket', 'id' => $model->id],
        'method' => 'post',
]); ?>

<!-- Hidden field that will contain selected seat numbers like: "12,15,18" -->
<!--what is this for?-->
<input type="hidden" name="seats" id="selected-seats">

<hr>

<div class="buy-info">

    <div class="buy-info-left">
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

    <div class="buy-info-right">
        <h3>Screening information</h3>

        <div>
            <strong>Movie Title:</strong>
            <span id="movie-title"><?= $model->movie_title ?></span>
        </div>

        <div>
            <strong>Screening Date:</strong>
            <span id="screening-date"><?= Yii::$app->formatter->asDate($model->screening_date) ?></span>
        </div>

        <div>
            <strong>Start Time:</strong>
            <span id="start-time"><?= Yii::$app->formatter->asTime($model->start_time) ?></span>
        </div>

        <div>
            <strong>End Time:</strong>
            <span id="end-time"><?= Yii::$app->formatter->asTime($model->end_time) ?></span>
        </div>

        <div>
            <strong>Ticket price:</strong>
            <?= Yii::$app->formatter->asDecimal($model->ticket_price, 2) ?> €
        </div>

        <div>
            <strong>Purchase Information:</strong>
            <span>provide your customer credentials, then select your seat of preference.
                After that click 'Buy Ticket(s)'.
            </span>
        </div>

    </div>

</div>

<hr>

<div class="seat-layout">
    <h2>MOVIE THEATRE</h2>

    <div class="seat-layout-grid">

        <div class="corner-cell">
        </div>

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

<div class="screening-summary">
    <div>
        <strong>Number of tickets:</strong>
        <span id="ticket-count">0</span>
    </div>
    <div>
        <strong>Total to be paid:</strong>
        <span id="total-price">0.00</span> €
    </div>
</div>

<div class="form-group" id="buy-tickets-button">
    <?= Html::submitButton('Buy Ticket(s)', [
            'class' => 'btn btn-success',
            'id' => 'buy-button',
            'disabled' => true,
    ]) ?>
</div>

<?php ActiveForm::end(); ?>

<script>
    let selectedSeats = [];
    const ticketPrice = <?= json_encode((float)$model->ticket_price) ?>;

    function toggleSeat(el) {
        const seatNumber = el.dataset.seat;

        if (el.classList.contains('seat-selected')) {
            el.classList.remove('seat-selected');
            selectedSeats = selectedSeats.filter(s => s !== seatNumber);
        } else {
            if (selectedSeats.length >= 10) {
                alert('You can select a maximum of 10 seats.');
                return;
            }
            el.classList.add('seat-selected');
            selectedSeats.push(seatNumber);
        }

        document.getElementById('selected-seats').value = selectedSeats.join(',');

        document.getElementById('buy-button').disabled = selectedSeats.length === 0;

        updatePaymentSummary();
    }

    function updatePaymentSummary() {
        const count = selectedSeats.length;
        const total = count * ticketPrice;

        document.getElementById('ticket-count').innerText = count;
        document.getElementById('total-price').innerText = total.toFixed(2);
    }
</script>
