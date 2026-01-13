<?php

namespace app\controllers;

use app\components\SeatLayout;
use app\models\Screening;
use app\models\ScreeningSearch;
use app\models\Ticket;
use Yii;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * ScreeningController implements the CRUD actions for Screening model.
 */
class ScreeningController extends Controller
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
     * Lists all Screening models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new ScreeningSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Screening model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $screening = $this->findModel($id);

        $seatLayout = SeatLayout::getSeatLayout();

        $soldTicketSeatNumbers_ForScreening = Ticket::find()
            ->select(['seat_number'])
            ->where(['screening_id' => $screening->id])
            ->asArray()
            ->all();

        // Instead of [12, 17, 35] we get [12 => true, 17 => true, 35 => true] which results in faster lookup (about which seat is sold)
        //
        $soldSeats = [];
        foreach ($soldTicketSeatNumbers_ForScreening as $ticket) {
            $soldSeats[$ticket['seat_number']] = true;
        }

        $soldCount = count($soldSeats);

        $income = $soldCount * $screening->ticket_price;

        return $this->render('view', [
            'model'      => $screening,
            'seatLayout' => $seatLayout,
            'soldSeats'  => $soldSeats,
            'soldCount'  => $soldCount,
            'income'     => $income,
        ]);
    }


    /**
     * Creates a new Screening model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Screening();

        if ($model->load($this->request->post())) {
            if ($model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }

            foreach ($model->getFirstErrors() as $error) {
                Yii::$app->session->setFlash('danger', $error);
            }
        }

        else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Screening model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->getTickets()->exists()) {
            throw new ForbiddenHttpException(
                'This screening cannot be modified because tickets have already been sold.'
            );
        }

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Screening model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        if ($model->getTickets()->exists()) {
            throw new ForbiddenHttpException(
                'This screening cannot be deleted because tickets have already been sold.'
            );
        }

        $model->delete();
        return $this->redirect(['index']);
    }

    /**
     * Finds the Screening model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Screening the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Screening::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
