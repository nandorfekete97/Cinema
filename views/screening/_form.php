<?php

use kartik\time\TimePicker;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Screening $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="screening-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'movie_title')->textInput(['maxlength' => true]) ?>

    <div>
        <div class="col-md-3">
            <?= $form->field($model, 'screening_date')->widget(\yii\jui\DatePicker::class, [
                    'options' => ['readOnly' => true],
                    'dateFormat' => 'yyyy-MM-dd'
                ]);
            ?>
        </div>

        <div class="row">
            <div class="col-md-3">
                <?= $form->field($model, 'start_time')->widget(TimePicker::class, [
                        'pluginOptions' => [
                                'showSeconds' => false,
                                'showMeridian' => false,
                                'minuteStep' => 15,
                                'defaultTime' => "00:00",
                        ],
                        'options' => [
                                'readonly' => true,
                        ],
                ]);
                ?>
            </div>

            <div class="col-md-3">
                <?= $form->field($model, 'end_time')->widget(TimePicker::class, [
                        'pluginOptions' => [
                                'showSeconds' => false,
                                'showMeridian' => false,
                                'minuteStep' => 1,
                                'defaultTime' => "00:00",
                        ],
                        'options' => [
                                'readonly' => true,
                        ],
                ]);
                ?>
            </div>
        </div>

    </div>

    <?= $form->field($model, 'ticket_price')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
