<?php

namespace app\controllers;

use app\components\SeatLayout;
use app\models\Screening;
use app\models\ScreeningSearch;
use app\models\Ticket;
use Yii;
use yii\filters\AccessControl;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
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
                    'class' => VerbFilter::class,
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
                'access' => [
                    'class' => AccessControl::class,
                    'rules' => [
                        [
                            'allow'   => true,
                            'roles'   => ['@'],   // logged in users
                        ],
                    ],
                ]
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

        $ticketsForScreening = $screening->getTickets()->all();

        $soldTicketSeatNumbers_ForScreening = Ticket::find()
            ->select(['seat_number'])
            ->where(['screening_id' => $screening->id])
            ->asArray()
            ->all();

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
            'ticketsForScreening' => $ticketsForScreening,
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
        $screeningsForDate = [];

        if ($model->load($this->request->post())) {

            if ($model->screening_date) {
                $screeningsForDate = Screening::getScreeningsForDate($model->screening_date);
            }

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
            'screeningsForDate' => $screeningsForDate,
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

        $screeningsForDate = Screening::getScreeningsForDate($model->screening_date);

        $this->checkIfScreeningHasTickets($model);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
            'screeningsForDate' => $screeningsForDate,
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

        $this->checkIfScreeningHasTickets($model);

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

    private function checkIfScreeningHasTickets(Screening $model) {
        if ($model->getTickets()->exists()) {
            throw new BadRequestHttpException(
                Yii::t(
                'app', 'This screening cannot be modified because tickets have already been sold.'
                )
            );
        }
    }
}
