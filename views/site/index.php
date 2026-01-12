<?php

/** @var yii\web\View $this */
/** @var app\models\ScreeningSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Todays Screenings';
?>

<div class="site-index">

    <h1> <?= \yii\helpers\Html::encode($this->title) ?> </h1>

    <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    'id',
                    [
                            'attribute' => 'movie_title',
                            'format' => 'raw',
                            'value' => function ($model) {
                                return Html::a(
                                        Html::encode($model->movie_title),
                                        ['screening/view', 'id' => $model->id]
                                );
                            },
                    ],
                    'screening_date',
                    'start_time',
                    'end_time',
                    'ticket_price',
                    [
                            'label' => 'Tickets Sold',
                            'filter' => false,
                            'value' => function ($model) {
                                $sold = $model->getTickets()->count();
                                return $sold . ' / 40';
                            },
                    ],
            ],
    ]); ?>

</div>
