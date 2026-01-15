<?php

use app\models\Ticket;
use app\models\Screening;
use yii\helpers\Html;
use yii\web\NotFoundHttpException;

/** @var int $screeningId */
/** @var array $ticketIds */

$screening = Screening::findOne($screeningId);

if (!$screening) {
    throw new NotFoundHttpException(Yii::t('app', 'Screening not found.'));
}

$tickets = Ticket::find()
        ->where(['id' => $ticketIds])
        ->all();

$totalTickets = count($tickets);
$totalPrice = $totalTickets * $screening->ticket_price;
?>

<div class="thank-you">

    <h1>Thank you for buying your <strong>ticket<?= $totalTickets > 1 ? 's' : '' ?>!</strong> </h1>

    <hr>

    <h3>Screening information</h3>
    <p>
        <strong>Movie:</strong> <?= Html::encode($screening->movie_title) ?><br>
        <strong>Date:</strong> <?= Yii::$app->formatter->asDate($screening->screening_date) ?><br>
        <strong>Start:</strong> <?= Yii::$app->formatter->asTime($screening->start_time) ?><br>
        <strong>Price per ticket:</strong>
        <?= Yii::$app->formatter->asDecimal($screening->ticket_price, 2) ?> €
    </p>

    <hr>

    <h3>Your tickets</h3>

    <table class="table table-bordered">
        <thead>
        <tr>
            <th>#</th>
            <th>Seat Number</th>
            <th>Seat Label</th>
            <th>Buyer</th>
            <th>Email</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($tickets as $i => $ticket): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><?= $ticket->seat_number ?></td>
                <td><?= Html::encode($ticket->seat_label) ?></td>
                <td><?= Html::encode($ticket->buyer_name) ?></td>
                <td><?= Html::encode($ticket->buyer_email) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <hr>

    <h3>Payment summary</h3>
    <p>
        <strong>Number of tickets:</strong> <?= $totalTickets ?><br>
        <strong>Total paid:</strong>
        <?= Yii::$app->formatter->asDecimal($totalPrice, 2) ?> €
    </p>

</div>
