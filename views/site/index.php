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

    <h1> <?= Html::encode($this->title) ?> </h1>

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

                    [
                            'attribute' => 'screening_date',
                            'value' => function ($model) {
                                return Yii::$app->formatter->asDate($model->screening_date);
                            }
                    ],

                    [
                            'attribute' => 'start_time',
                            'filter' => false,
                            'value' => function ($model) {
                                return Yii::$app->formatter->asTime($model->start_time);
                            },
                    ],

                    [
                            'attribute' => 'end_time',
                            'filter' => false,
                            'value' => function ($model) {
                                return Yii::$app->formatter->asTime($model->end_time);
                            },
                    ],
                    [
                            'attribute' => 'ticket_price',
                            'filter' => false,
                            'value' => function ($model) {
                                return Yii::$app->formatter->asDecimal($model->ticket_price, 2) . ' €';
                            },
                    ],
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
