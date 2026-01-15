<?php

use app\models\Screening;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\jui\DatePicker;

/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var app\models\ScreeningSearch $searchModel */

$this->title = Yii::t('app', 'Available Screenings');
$this->params['breadcrumbs'][] = $this->title;

?>

<h1><?= Html::encode($this->title) ?></h1>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => [
        [
            'attribute' => 'movie_title',
            'filter' => false,
        ],
        [
            'attribute' => 'screening_date',
            'label' => 'Screening Date',
            'value' => function ($model) {
                return Yii::$app->formatter->asDate($model->screening_date);
            },
            'filter' => false
        ],
        [
            'label' => 'Start Time',
            'value' => function ($model) {
                return Yii::$app->formatter->asTime($model->start_time);
            },
        ],
        [
            'label' => 'Movie Runtime',
            'value' => function ($model) {
                $start = strtotime($model->start_time);
                $end   = strtotime($model->end_time);
                $minutes = ($end - $start) / 60;
                return (int)$minutes . ' min';
            },
        ],
        [
            'label' => 'Ticket Price',
            'value' => function ($model) {
                return Yii::$app->formatter->asDecimal($model->ticket_price, 2) . ' €';
            },
        ],
        [
            'label' => '',
            'format' => 'raw',
            'value' => function ($model) {
                return Html::a(
                    'Buy Ticket',
                    ['ticket/buy-ticket', 'id' => $model->id],
                    ['class' => 'btn btn-primary btn-sm']
                );
            },
        ],
    ],
]); ?>
