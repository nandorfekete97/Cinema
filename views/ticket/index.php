<?php

use app\models\Ticket;
use yii\helpers\Html;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\TicketSearch $searchModel */
/** @var app\models\Ticket $model */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = Yii::t('app', 'Tickets');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="ticket-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel'  => $searchModel,
            'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],

                    [
                            'label' => 'Movie Title',
                            'attribute' => 'movie_title',
                            'value' => function ($model) {
                                return $model->screening->movie_title;
                            }
                    ],

                    [
                            'label' => 'Date',
                            'attribute' => 'screening_date',
                            'value' => function ($model) {
                                return Yii::$app->formatter->asDate($model->screening->screening_date);
                            },
                            'filter' => \yii\jui\DatePicker::widget([
                                    'model' => $searchModel,
                                    'attribute' => 'screening_date',
                                    'dateFormat' => 'yyyy.MM.dd',
                                    'options' => [
                                            'class' => 'form-control',
                                            'autocomplete' => 'off',
                                    ],
                            ]),
                    ],

                    [
                            'label' => 'Start',
                            'attribute' => 'start_time',
                            'filter' => false,
                            'value' => function ($model) {
                                return Yii::$app->formatter->asTime($model->screening->start_time);
                            },
                    ],

                    [
                            'label' => 'End',
                            'attribute' => 'end_time',
                            'filter' => false,
                            'value' => function ($model) {
                                return Yii::$app->formatter->asTime($model->screening->end_time);
                            },
                    ],

                    [
                            'label' => 'Price',
                            'attribute' => 'ticket_price',
                            'filter' => false,
                            'value' => function ($model) {
                                return Yii::$app->formatter->asDecimal($model->screening->ticket_price, 2) . ' €';
                            },
                    ],

                    [
                            'attribute' => 'seat_number',
                            'filter' => false,
                    ],

                    [
                            'attribute' => 'buyer_name',
                            'filter' => false,
                    ],

                    [
                            'attribute' => 'buyer_phone',
                            'filter' => false,
                    ],

                    [
                            'attribute' => 'buyer_email',
                            'filter' => false,
                    ],
            ],
    ]); ?>

</div>
