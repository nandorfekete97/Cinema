<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "screening".
 *
 * @property int $id
 * @property string $movie_title
 * @property string $screening_date
 * @property string $start_time
 * @property string $end_time
 * @property float $ticket_price
 * @property int|null $created_at
 * @property int|null $updated_at
 *
 * @property Ticket[] $tickets
 */
class Screening extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'screening';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at'], 'default', 'value' => null],
            [['movie_title', 'screening_date', 'start_time', 'end_time', 'ticket_price'], 'required'],

            [['screening_date', 'start_time', 'end_time'], 'safe'],
            ['screening_date', 'checkIfScreeningDateIsLastSundayOfMonth'],

            ['start_time', 'validateStartTimeWindow'],
            ['start_time', 'validateStartTimeToday'],
            ['start_time', 'checkIfStartingMinuteIsRounded'],
            ['end_time', 'validateDuration'],
            [['start_time', 'end_time'], 'validateGapBetweenScreenings'],

            [['ticket_price'], 'number'],
            [['created_at', 'updated_at'], 'integer'],
            [['movie_title'], 'string', 'max' => 255],
        ];
    }

    public function behaviors() {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => time(),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'movie_title' => Yii::t('app', 'Movie Title'),
            'screening_date' => Yii::t('app', 'Screening Date'),
            'start_time' => Yii::t('app', 'Start Time'),
            'end_time' => Yii::t('app', 'End Time'),
            'ticket_price' => Yii::t('app', 'Ticket Price'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * Gets query for [[Tickets]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTickets()
    {
        return $this->hasMany(Ticket::class, ['screening_id' => 'id']);
    }

    public function validateStartTimeToday($attribute)
    {
        $today = date('Y-m-d');
        $now = new \DateTime();

        if ($this->screening_date < $today) {
            $this->addError(
                $attribute,
                'Screenings cannot be created in the past.'
            );
            return;
        }

        if ($this->screening_date !== $today) {
            return;
        }

        $minutes = (int)$now->format('i');

        $roundedMinutes = ceil($minutes / 15) * 15;

        if ($roundedMinutes === 60) {
            $now->modify('+1 hour');
            $roundedMinutes = 0;
        }

        $now->setTime(
            (int)$now->format('H'),
            $roundedMinutes,
            0
        );

        $earliestAllowed = $now->format('H:i');

        $start = strtotime($this->start_time);

        if ($start < strtotime($earliestAllowed)) {
            $this->addError(
                $attribute,
                "The earliest possible screening start time today is {$earliestAllowed}."
            );
            // with the current state of this method, the return statement is REDUNDANT
            // (being the last executable statement in the method, PHP implicitly returns anyway)
            // HOWEVER: as defensive clarity, the 'return' can stay, because if code is added below
            // the logic remains safe
            return;
        }
    }

    public function validateStartTimeWindow($attribute) {
        $start = strtotime($this->start_time);

        $min = strtotime('08:00:00');
        $max = strtotime('20:00:00');

        if ($start < $min || $start > $max) {
            $this->addError(
                $attribute,
                'Screening start time must be between 08:00 and 20:00'
            );
        }
    }

    public function validateDuration($attribute) {
        $start = strtotime($this->start_time);
        $end = strtotime($this->end_time);

        if ($end < $start) {
            $this->addError($attribute, 'End time cannot be before start time.');
        }

        $duration = $end - $start;

        if ($duration < 1800) {
            $this->addError($attribute, 'End time must be at least 30 minutes after start time');
        }

        if ($duration > 6 * 3600) {
            $this->addError($attribute, 'Maximum screening length is 6 hours');
        }
    }

    public function validateGapBetweenScreenings($attribute)
    {
        if ($this->hasErrors()) {
            return;
        }

        $start = strtotime($this->start_time);
        $end   = strtotime($this->end_time);

        $query = self::find()->where([
            'screening_date' => $this->screening_date
        ]);

        if (!$this->isNewRecord) {
            $query->andWhere(['<>', 'id', $this->id]);
        }

        $screenings = $query->all();

        foreach ($screenings as $screening) {

            $existingStart = strtotime($screening->start_time);
            $existingEnd   = strtotime($screening->end_time);

            if ($end <= $existingStart) {
                if (($existingStart - $end) < 3600) {
                    $this->addError(
                        $attribute,
                        'There must be at least 1 hour between screenings.'
                    );
                    return;
                }
            }

            if ($start >= $existingEnd) {
                if (($start - $existingEnd) < 3600) {
                    $this->addError(
                        $attribute,
                        'There must be at least 1 hour between screenings.'
                    );
                    return;
                }
            }

            if ($start < $existingEnd && $end > $existingStart) {
                $this->addError(
                    $attribute,
                    'Screenings cannot overlap.'
                );
                return;
            }
        }
    }

    public function checkIfScreeningDateIsLastSundayOfMonth($attribute)
    {
        if (empty($this->screening_date)) {
            return;
        }

        // screening_date is in string format, so we need to convert to DateTime object (for calendar math)
        $date = new \DateTime($this->screening_date);

        // format 'N' returns the integer of the datetime objects day number
        if ((int)$date->format('N') !== 7) {
            // Not a Sunday → rule does not apply
            return;
        }

        // Clone date so we don't mutate original
        $lastDayOfMonth = clone $date;

        // PHP's datetime::modify() uses natural language date parser
        // modify('last day of this month') changes the DateTime object's day to the last calendar day of the month
        $lastDayOfMonth->modify('last day of this month');

        // iterate backwards until we hit a Sunday
        while ((int)$lastDayOfMonth->format('N') !== 7) {
            $lastDayOfMonth->modify('-1 day');
        }

        // Compare dates (Y-m-d only)
        if ($date->format('Y-m-d') === $lastDayOfMonth->format('Y-m-d')) {
            $this->addError(
                $attribute,
                'No screenings are allowed on the last Sunday of the month.'
            );
        }
    }

    public function checkIfStartingMinuteIsRounded($attribute) {
        if (!$this->start_time) {
            return;
        }

        $minute = date('i', strtotime($this->start_time));

        $allowed = ['00', '15', '30', '45'];

        if (!in_array($minute, $allowed, true)) {
            $this->addError(
                $attribute,
                'Starting minute for screening must be 00, 15, 30 or 45.'
            );
        }
    }

    public static function getScreeningsForDate(string $date) {
        return self::find()
            ->where(['screening_date' => $date])
            ->orderBy(['start_time' => SORT_ASC])
            ->all();
    }
}
