<?php

use yii\db\Migration;

class m260110_223006_add_seat_number_to_ticket extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(
            '{{%ticket}}',
            'seat_number',
            $this->integer()->notNull()
        );

        $this->createIndex(
            '{{%idx-ticket-seat_number}}',
            '{{%ticket}}',
            ['screening_id', 'seat_number'],
            true
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex(
            '{{%idx-ticket-seat_number}}',
            '{{%ticket}}'
        );

        $this->dropColumn(
            'ticket',
            'seat_number'
        );
    }
}
