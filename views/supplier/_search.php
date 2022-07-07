<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\searchs\SupplierSearch */
/* @var $form yii\widgets\ActiveForm */

?>

<div class="supplier-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'name') ?>

    <?= $form->field($model, 'code') ?>

    <?= $form->field($model, 't_status')->dropDownList($status, [
        'prompt' => 'All'
    ]) ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
<!--        --><?//= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary reset']) ?>

        <?= Html::a('Export', ['export'], [
            'class' => 'btn btn-primary export'
        ]) ?>

    </div>

    <?php ActiveForm::end(); ?>

</div>