<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Screening $model */

$this->title = Yii::t('app', 'Create Screening');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Screenings'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="screening-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
