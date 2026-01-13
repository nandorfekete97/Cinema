<?php

use app\models\Screening;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\ScreeningSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = Yii::t('app', 'Screenings');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="screening-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Create Screening'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel'  => $searchModel,
            'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],

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
                            },
                            'filter' => \yii\jui\DatePicker::widget([
                                    'model' => $searchModel,
                                    'attribute' => 'screening_date',
                                    'dateFormat' => 'yyyy-MM-dd',
                                    'options' => [
                                            'class' => 'form-control',
                                            'autocomplete' => 'off',
                                    ],
                            ]),
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

                    [
                            'class' => ActionColumn::class,
                            'visibleButtons' => [
                                    'update' => function ($model) {
                                        return !$model->getTickets()->exists();
                                    },
                                    'delete' => function ($model) {
                                        return !$model->getTickets()->exists();
                                    },
                                    'view' => true,
                            ],
                            'urlCreator' => function ($action, Screening $model) {
                                return Url::toRoute([$action, 'id' => $model->id]);
                            }
                    ],
            ],
    ]); ?>


</div>
