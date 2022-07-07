<?php

namespace app\controllers;

use app\models\Supplier;
use app\models\searchs\SupplierSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * SupplierController implements the CRUD actions for Supplier model.
 */
class SupplierController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all Supplier models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new SupplierSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Supplier model.
     * @param string $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Supplier model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Supplier();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Supplier model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Supplier model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Supplier model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id ID
     * @return Supplier the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Supplier::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * 批量选择
     * @return array
     */
    public function actionBatchSelect()
    {
        $this->response->format = Response::FORMAT_JSON;
        $searchModel = new SupplierSearch();
        $provider = $searchModel->search($this->request->post(), ['pagination' => false]);

        $models = $provider->getModels();
        $ids = '';
        foreach ($models as $model) {
            $ids .= $model->id . ',';
        }

        $count = $provider->getTotalCount();
        return [
            'success' => true,
            'data' => [
                'count' => $count,
                'ids' => trim($ids, ','),
                'total' => (int)Supplier::find()->count()
            ]];
    }

    /**
     * 导出列表.
     */
    public function actionExport()
    {
        $data = $this->request->post();

        $title = 'id,';
        $downloadColumns[] = 'id';
        $columns = $data['SupplierSearch'];
        foreach ($columns as $name => $value) {
            if ($value) {
                $title .= $name . ',';
                $downloadColumns[] = $name;
            }
        }

        $title = trim($title, ',') . "\n";
        $fileName = '供应商' . date('Ymd') . '.csv';

        // 列表
        $dataArr = Supplier::find()
            ->select($downloadColumns)
            ->andFilterWhere(['in', 'id', explode(',', $data['ids'])])
            ->asArray()
            ->all();

        $wrstr = '';
        if (!empty($dataArr)) {
            foreach ($dataArr as $key => $value) {
                $wrstr .= implode(',', array_values($value));
                $wrstr .= "\n";
            }
        }

        $this->csvExport($fileName, $title, $wrstr);
    }

    /**
     * 导出文件
     */
    protected function csvExport($file, $title, $data)
    {
        header("Content-Disposition:attachment;filename=" . $file);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Transfer-Encoding: binary');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');

        $wrstr = $title;
        $wrstr .= $data;
        echo iconv("utf-8", "GBK//ignore", $wrstr);
    }
}
