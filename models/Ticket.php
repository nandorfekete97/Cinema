<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "ticket".
 *
 * @property int $id
 * @property int $screening_id
 * @property string $seat_label
 * @property int $seat_row
 * @property string $seat_column
 * @property int $seat_number
 * @property string $buyer_name
 * @property string $buyer_phone
 * @property string $buyer_email
 * @property int|null $created_at
 * @property int|null $updated_at
 *
 * @property Screening $screening
 */
class Ticket extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ticket';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at'], 'default', 'value' => null],
            [['screening_id', 'seat_label', 'seat_row', 'seat_column', 'seat_number', 'buyer_name', 'buyer_phone', 'buyer_email'], 'required'],
            [['screening_id', 'seat_row', 'seat_number', 'created_at', 'updated_at'], 'integer'],
            [['seat_label'], 'string', 'max' => 3],
            [['seat_column'], 'string', 'max' => 1],
            [['buyer_name', 'buyer_phone', 'buyer_email'], 'string', 'max' => 255],
            [['screening_id', 'seat_label'], 'unique', 'targetAttribute' => ['screening_id', 'seat_label']],
            [['screening_id', 'seat_number'], 'unique', 'targetAttribute' => ['screening_id', 'seat_number']],
            [['screening_id'], 'exist', 'skipOnError' => true, 'targetClass' => Screening::class, 'targetAttribute' => ['screening_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'screening_id' => Yii::t('app', 'Screening ID'),
            'seat_label' => Yii::t('app', 'Seat Label'),
            'seat_row' => Yii::t('app', 'Seat Row'),
            'seat_column' => Yii::t('app', 'Seat Column'),
            'seat_number' => Yii::t('app', 'Seat Number'),
            'buyer_name' => Yii::t('app', 'Buyer Name'),
            'buyer_phone' => Yii::t('app', 'Buyer Phone'),
            'buyer_email' => Yii::t('app', 'Buyer Email'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * Gets query for [[Screening]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getScreening()
    {
        return $this->hasOne(Screening::class, ['id' => 'screening_id']);
    }

}