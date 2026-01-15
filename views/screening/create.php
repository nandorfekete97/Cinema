<?php

use app\models\Screening;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Screening $model */
/** @var app\models\Screening[] $screeningsForDate title */

$this->title = Yii::t('app', 'Create Screening');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Screenings'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="screening-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

    <?php if (!$model->screening_date): ?>
        <p class="text-muted">
            Select a date to see existing screenings for that day.
        </p>
    <?php else: ?>

        <h3 class="today-screening-list">
            Screenings on <?= Yii::$app->formatter->asDate($model->screening_date) ?>
        </h3>

        <?php if (empty($screeningsForDate)): ?>
            <p>No screenings scheduled for this date.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($screeningsForDate as $screening): ?>
                    <li class="screening-list-item">
                        <strong><?= Html::encode($screening->movie_title) ?></strong>
                        |
                        <?= Yii::$app->formatter->asTime($screening->start_time) ?>
                        –
                        <?= Yii::$app->formatter->asTime($screening->end_time) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

    <?php endif; ?>


</div>
