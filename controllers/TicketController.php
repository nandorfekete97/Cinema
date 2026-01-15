<?php

namespace app\controllers;

use app\components\SeatLayout;
use app\models\Screening;
use app\models\ScreeningSearch;
use app\models\Ticket;
use app\models\TicketSearch;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
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
                'access' => [
                    'class' => AccessControl::class,
                    'rules' => [
                        // browsing screenings, buying tickets allowed for guests
                        [
                            'actions' => ['available-screenings', 'buy-ticket', 'thank-you'],
                            'allow'   => true,
                            'roles'   => ['?'],
                        ],
                        // ticket management for admins
                        [
                            'actions' => ['index', 'view', 'delete', 'create', 'update'],
                            'allow'   => true,
                            'roles'   => ['@'],
                        ],
                    ],
                ]
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
        $screening = $this->findScreening($id);

        if ($result = $this->checkIfScreeningStartIsLessThenOneHour($screening)) {
            return $result;
        }

        if (!Yii::$app->request->isPost) {
            return $this->renderBuyForm($screening);
        }

        $data = $this->getPurchaseFormData();
        if ($data === null) {
            return $this->refresh();
        }

        try {
            $ticketIds = $this->processTicketPurchase($screening, $data);

            return $this->redirect([
                'thank-you',
                'screening_id' => $screening->id,
                'ticket_ids'   => implode(',', $ticketIds),
            ]);
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
            return $this->refresh();
        }
    }

    private function findScreening($id): Screening
    {
        $screening = Screening::findOne($id);

        if (!$screening) {
            throw new NotFoundHttpException('Screening not found.');
        }

        return $screening;
    }

    private function getPurchaseFormData(): ?array
    {
        $seatsRaw    = Yii::$app->request->post('seats');
        $buyerName   = Yii::$app->request->post('buyer_name');
        $buyerPhone  = Yii::$app->request->post('buyer_phone');
        $buyerEmail  = Yii::$app->request->post('buyer_email');

        if (!$seatsRaw || !$buyerName || !$buyerPhone || !$buyerEmail) {
            Yii::$app->session->setFlash('error', 'All fields are required.');
            return null;
        }

        $seatNumbers = explode(',', $seatsRaw);

        if (count($seatNumbers) > 10) {
            Yii::$app->session->setFlash('error', 'You can buy maximum 10 tickets.');
            return null;
        }

        return [
            'seats'       => $seatNumbers,
            'buyer_name'  => $buyerName,
            'buyer_phone' => $buyerPhone,
            'buyer_email' => $buyerEmail,
        ];
    }

    private function processTicketPurchase(Screening $screening, array $data): array
    {
        $transaction = Yii::$app->db->beginTransaction();
        $now = time();

        try {
            foreach ($data['seats'] as $seatNumber) {
                $this->createSingleTicket($screening, (int)$seatNumber, $data, $now);
            }

            $transaction->commit();

            return Ticket::find()
                ->select('id')
                ->where([
                    'screening_id' => $screening->id,
                    'buyer_email'  => $data['buyer_email'],
                    'buyer_phone'  => $data['buyer_phone'],
                    'created_at'   => $now,
                ])
                ->column();

        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    private function createSingleTicket(Screening $screening, int $seatNumber, array $data, int $now): void
    {
        if ($this->isSeatAlreadySold($screening->id, $seatNumber)) {
            throw new \Exception("Seat {$seatNumber} is already sold.");
        }

        $seatData = $this->findSeatByNumber($seatNumber);
        if (!$seatData) {
            throw new \Exception("Seat {$seatNumber} not found.");
        }

        $ticket = new Ticket();
        $ticket->screening_id = $screening->id;
        $ticket->seat_number = $seatNumber;
        $ticket->seat_row    = $seatData['row'];
        $ticket->seat_column = $seatData['column'];
        $ticket->seat_label  = $seatData['label'];

        $ticket->buyer_name  = $data['buyer_name'];
        $ticket->buyer_phone = $data['buyer_phone'];
        $ticket->buyer_email = $data['buyer_email'];

        $ticket->created_at = $now;
        $ticket->updated_at = $now;

        if (!$ticket->save()) {
            throw new \Exception(json_encode($ticket->errors));
        }
    }

    private function isSeatAlreadySold(int $screeningId, int $seatNumber): bool
    {
        return Ticket::find()
            ->where([
                'screening_id' => $screeningId,
                'seat_number'  => $seatNumber,
            ])
            ->exists();
    }

    private function renderBuyForm(Screening $screening)
    {
        $seatLayout = SeatLayout::getSeatLayout();

        $soldSeats = Ticket::find()
            ->select(['seat_number'])
            ->where(['screening_id' => $screening->id])
            ->indexBy('seat_number')
            ->column();

        return $this->render('buy-ticket', [
            'model'      => $screening,
            'seatLayout' => $seatLayout,
            'soldSeats'  => array_fill_keys($soldSeats, true),
        ]);
    }

    public function checkIfScreeningStartIsLessThenOneHour($screening) {
        $now = time();
        $screeningStartTimestamp = strtotime(
            $screening->screening_date . ' ' . $screening->start_time
        );

        if ($screeningStartTimestamp - $now < 3600) {
            Yii::$app->session->setFlash(
                'error',
                'You can only buy tickets at least 1 hour before the screening starts.'
            );
            return $this->redirect(['ticket/available-screenings']);
        }

        return null;
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

    public function actionThankYou() {
        $screeningId = Yii::$app->request->get('screening_id');
        $ticketIdsRaw = Yii::$app->request->get('ticket_ids');

        $ticketIds = $ticketIdsRaw ? explode(',', $ticketIdsRaw) : [];

        return $this->render('thank-you',[
            'screeningId' => $screeningId,
            'ticketIds' => $ticketIds,
        ]);
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
