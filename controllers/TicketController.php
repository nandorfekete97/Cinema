<?php

namespace app\controllers;

use app\components\SeatLayout;
use app\models\Screening;
use app\models\ScreeningSearch;
use app\models\Ticket;
use app\models\TicketSearch;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * TicketController implements the CRUD actions for Ticket model.
 */
class TicketController extends Controller
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
     * Displays a single Ticket model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    public function actionIndex()
    {
        $searchModel = new TicketSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionAvailableScreenings() {
        $searchModel = new ScreeningSearch();

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        // Apply the "buyable screenings" constraint on top
        $now = new \DateTime();
        $today = $now->format('Y-m-d');
        $oneHourLater = (clone $now)->modify('+1 hour')->format('H:i:s');

        $dataProvider->query
            ->andWhere([
                'or',
                ['>', 'screening_date', $today],
                [
                    'and',
                    ['screening_date' => $today],
                    ['>=', 'start_time', $oneHourLater]
                ]
            ])
            ->orderBy([
                'screening_date' => SORT_ASC,
                'start_time' => SORT_ASC,
            ]);

        return $this->render('available-screenings', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionBuyTicket($id)
    {
        $screening = Screening::findOne($id);

        if (!$screening) {
            throw new NotFoundHttpException('Screening not found.');
        }

        if (Yii::$app->request->isPost) {

            $seatsRaw = Yii::$app->request->post('seats');
            $buyerName  = Yii::$app->request->post('buyer_name');
            $buyerPhone = Yii::$app->request->post('buyer_phone');
            $buyerEmail = Yii::$app->request->post('buyer_email');

            if (!$seatsRaw || !$buyerName || !$buyerPhone || !$buyerEmail) {
                Yii::$app->session->setFlash('error', 'All fields are required.');
                return $this->refresh();
            }

            $seatNumbers = explode(',', $seatsRaw);

            if (count($seatNumbers) > 10) {
                Yii::$app->session->setFlash('error', 'You can buy maximum 10 tickets.');
                return $this->refresh();
            }

            $transaction = Yii::$app->db->beginTransaction();

            try {
                foreach ($seatNumbers as $seatNumber) {
                    $exists = Ticket::find()
                        ->where([
                            'screening_id' => $screening->id,
                            'seat_number' => $seatNumber,
                        ])
                        ->exists();

                    if ($exists) {
                        throw new \Exception("Seat {$seatNumber} is already sold.");
                    }

                    $seatData = $this->findSeatByNumber((int)$seatNumber);

                    if (!$seatData) {
                        throw new \Exception("Seat {$seatNumber} not found.");
                    }

                    $ticket = new Ticket();
                    $ticket->screening_id = $screening->id;

                    $ticket->seat_number = (int)$seatNumber;
                    $ticket->seat_row = $seatData['row'];
                    $ticket->seat_column = $seatData['column'];
                    $ticket->seat_label = $seatData['label'];

                    $ticket->buyer_name = $buyerName;
                    $ticket->buyer_phone = $buyerPhone;
                    $ticket->buyer_email = $buyerEmail;

                    $ticket->created_at = time();
                    $ticket->updated_at = time();

                    if (!$ticket->save()) {
                        throw new \Exception(json_encode($ticket->errors));
                    }
                }

                $transaction->commit();

                return $this->redirect([
                    'ticket/available-screenings',
                    'id' => $screening->id,
                    'seats' => implode(',', $seatNumbers),
                ]);

            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', $e->getMessage());
                return $this->refresh();
            }
        }

        // GET request → show seat map
        $seatLayout = SeatLayout::getSeatLayout();

        $soldTickets = Ticket::find()
            ->select(['seat_number'])
            ->where(['screening_id' => $screening->id])
            ->asArray()
            ->all();

        $soldSeats = [];
        foreach ($soldTickets as $row) {
            $soldSeats[$row['seat_number']] = true;
        }

        return $this->render('buy-ticket', [
            'model' => $screening,
            'seatLayout' => $seatLayout,
            'soldSeats' => $soldSeats,
        ]);
    }


    /**
     * Creates a new Ticket model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Ticket();

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
     * Updates an existing Ticket model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
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
     * Deletes an existing Ticket model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Ticket model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Ticket the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Ticket::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }

    public function findSeatByNumber(int $seatNumber): ?array
    {
        $layout = SeatLayout::getSeatLayout();

        foreach ($layout as $row => $cols) {
            foreach ($cols as $col => $seat) {
                if ($seat['number'] === $seatNumber) {
                    return [
                        'row' => $row,
                        'column' => $col,
                        'label' => $row . $col,
                    ];
                }
            }
        }

        return null;
    }
}
