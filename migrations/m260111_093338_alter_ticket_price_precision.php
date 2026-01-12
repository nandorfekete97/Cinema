<?php

use yii\db\Migration;

class m260111_093338_alter_ticket_price_precision extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('{{screening}}', 'ticket_price',
            $this->decimal(10,2)->notNull()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('{{screening}}', 'ticket_price',
            $this->decimal(10,2)->notNull()
        );
    }
}
