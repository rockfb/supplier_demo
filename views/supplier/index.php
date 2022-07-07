<?php

use yii\bootstrap4\Modal;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\ActiveForm;

use app\assets\JqueryFormAsset;


/* @var $this yii\web\View */
/* @var $searchModel app\models\searchs\SupplierSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Suppliers';
$this->params['breadcrumbs'][] = $this->title;

JqueryFormAsset::register($this);
$status = \app\models\Supplier::status();
$pageSize = Yii::$app->params['pagination']['pageSize'];
?>
<div class="supplier-index" data-pagesize="<?php echo $pageSize;?>" data-total="<?php echo $dataProvider->getTotalCount();?>">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php echo $this->render('_search', ['model' => $searchModel, 'status' => $status]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'id' => 'supplier_table',
//        'filterModel' => $searchModel,
        'pager' => [
            //'options'=>['class'=>'hidden']//关闭自带分页
            'firstPageLabel' => "首页",
            'prevPageLabel' => '上一页',
            'nextPageLabel' => '下一页',
            'lastPageLabel' => '未页',
        ],
        'columns' => [
//            ['class' => 'yii\grid\SerialColumn'],
            ['class' => 'yii\grid\CheckboxColumn'],
            'id',
            'name',
            'code',
            't_status',
        ]
    ]); ?>
</div>


<?php Modal::begin([
    'title' => '#',
    'bodyOptions' => ['style' => 'display:none'],
    'options' => [
        'id' => 'dialog-batch-select'
    ]
]) ?>

<?php $form = ActiveForm::begin([
    'action' => ['supplier/batch-select']
]) ?>
<?= Html::hiddenInput('ids', null, ['id' => 'supplier-ids']) ?>
<?= $form->field($searchModel, 'id') ?>

<?= $form->field($searchModel, 'name') ?>

<?= $form->field($searchModel, 'code') ?>

<?= $form->field($searchModel, 't_status')->dropDownList($status, [
    'prompt' => 'All'
]) ?>

<div class="form-group">
    <?= Html::submitButton('Submit', ['class' => 'btn btn-primary submit-btn']) ?>
</div>

<?php ActiveForm::end() ?>
<?php Modal::end() ?>


<?php Modal::begin([
    'title' => '请选择需要导出的列',
    'options' => [
        'id' => 'dialog-export'
    ]
]) ?>

<?php $formExport = ActiveForm::begin([
    'action' => ['supplier/export']
]) ?>
<?= Html::hiddenInput('ids', null, ['id' => 'supplier-columns']) ?>
<?= $formExport->field($searchModel, 'id')->checkbox(['disabled' => true,'checked' => true]) ?>

<?= $formExport->field($searchModel, 'name')->checkbox() ?>

<?= $formExport->field($searchModel, 'code')->checkbox() ?>

<?= $formExport->field($searchModel, 't_status')->checkbox() ?>

<div class="form-group">
    <?= Html::submitButton('Submit', ['class' => 'btn btn-primary submit-btn-export']) ?>
</div>

<?php ActiveForm::end() ?>
<?php Modal::end() ?>


<?php $this->beginBlock('JS') ?>
    //multi select modal
    $(document).on('click', '#supplier_table', function (event) {
        var pageSize = $('.supplier-index').data('pagesize');
        var total = $('.supplier-index').data('total');
        var ids = $("#supplier_table").yiiGridView("getSelectedRows");

        var $dialog = $('#dialog-batch-select');
        $form = $dialog.find('form');
        $form.find('#supplier-ids').val(ids.join(","));

        if (ids.length == pageSize || (total < pageSize && total == ids.length)) {
            $dialog.find('.modal-header>#dialog-batch-select-label').html('All ' + ids.length + ' rows selects.<br/><a class="show-modal-body">select all supplier that match this search</a>');
            $dialog.find('.modal-body').css({'display': 'none'});
            $dialog.modal('show');
        }
    });

    $(document).on('click', '.show-modal-body', function (e) {
        e.preventDefault();
        $(this).parent().parent().next().css({'display': 'block'});
    });

    //close modal
    $(document).on('hidden.bs.modal', '#dialog-batch-select', function (e) {
        var $dialog = $('#dialog-batch-select');
        $form = $dialog.find('form');
        var ids = $form.find('#supplier-ids').val();
        var idArr = ids.split(',');
        var flag = 0;
        $("#supplier_table input[name='selection[]']").each(function(){
            if($.inArray($(this).val(), idArr) === -1){
                $(this).prop("checked",false);
            }else{
                $(this).prop("checked",true);
                flag++;
            }
        });
        var pageSize = $('.supplier-index').data('pagesize');
        var total = $('.supplier-index').data('total');
        if((flag < total && total < pageSize) || (flag < pageSize && total >= pageSize)){
            $('.select-on-check-all').prop("checked",false);
        }
    });

    //cancel
    $(document).on('click', '.cancel', function (e) {
        e.preventDefault();
        var $dialog = $('#dialog-batch-select');
        $form = $dialog.find('form');
        $form.find('#supplier-ids').val('');
        $(this).trigger($('.close').click());
    });


    $(document).on('click', '.submit-btn', function (e) {
        var $dialog = $('#dialog-batch-select');
        $form = $dialog.find('form');

        $form.ajaxForm(function (response) {
            if (response.success) {
                var count = response.data.count;
                var total = response.data.total;
                var ids = response.data.ids;

                if(count === total){
$dialog.find('.modal-header>#dialog-batch-select-label').html('All suppliers in this search has been selected.<br/><a class="cancel">cancel</a>');
} else {
$dialog.find('.modal-header>#dialog-batch-select-label').html('All ' + count + ' rows selects.<br/><a class="show-modal-body">select all supplier that match this search</a>');
                }
$dialog.find('.modal-body').css({'display': 'none'});
$form.find('#supplier-ids').val(ids);
            }
        });
    });

    //export
    $(document).on('click', '.export', function (e) {
        e.preventDefault();
        var $dialogExport = $('#dialog-export');
        var $dialog = $('#dialog-batch-select');
        $form = $dialog.find('form');
        $ids = $form.find('#supplier-ids').val();
        if(!$ids.length){
            alert('请选择导出的数据');
            return;
        }
        $form = $dialogExport.find('form').find('#supplier-columns').val($ids)
        $dialogExport.modal('show');
    });

    $(document).on('click', '.submit-btn-export', function (e) {
        $('#dialog-export').modal('hide');
    });
<?php $this->endBlock(); ?>
<?php $this->registerJs($this->blocks['JS']); ?>


