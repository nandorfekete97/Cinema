<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Ticket;

/**
 * TicketSearch represents the model behind the search form of `app\models\Ticket`.
 */
class TicketSearch extends Ticket
{
    public $movie_title;
    public $screening_date;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'screening_id', 'seat_row', 'created_at', 'updated_at', 'seat_number'], 'integer'],
            [['seat_label', 'seat_column', 'buyer_name', 'buyer_phone', 'buyer_email', 'movie_title', 'screening_date'], 'safe'],
            [['movie_title', 'screening_date'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     * @param string|null $formName Form name to be used into `->load()` method.
     *
     * @return ActiveDataProvider
     */
    public function search($params, $formName = null)
    {
        $query = Ticket::find()->joinWith('screening');

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params, $formName);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere(['like', 'screening.movie_title', $this->movie_title])
            ->andFilterWhere(['=', 'screening.screening_date', $this->screening_date])
            ->andFilterWhere(['=', 'ticket.seat_number', $this->seat_number])
            ->andFilterWhere(['like', 'buyer_name', $this->buyer_name])
            ->andFilterWhere(['like', 'buyer_phone', $this->buyer_phone])
            ->andFilterWhere(['like', 'buyer_email', $this->buyer_email]);

        return $dataProvider;
    }
}
