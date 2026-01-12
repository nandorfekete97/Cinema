<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%ticket}}`.
 */
class m260108_120239_create_ticket_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%ticket}}', [
            'id' => $this->primaryKey(),
            'screening_id' => $this->integer()->notNull(),
            'seat_label' => $this->string(3)->notNull(),
            'seat_row' => $this->integer()->notNull(),
            'seat_column' => $this->string(1)->notNull(),
            'buyer_name' => $this->string()->notNull(),
            'buyer_phone' => $this->string()->notNull(),
            'buyer_email' => $this->string()->notNull(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);

        $this->createIndex(
            'idx-ticket-screening_id',
            '{{%ticket}}',
            'screening_id'
        );

        $this->createIndex(
            'idx-ticket-screening-seat',
            '{{%ticket}}',
            ['screening_id', 'seat_label'],
            true
        );

        $this->addForeignKey(
            'fk-ticket-screening_id',
            '{{%ticket}}',
            'screening_id',
            '{{%screening}}',
            'id',
            'CASCADE',
            'RESTRICT'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-ticket-screening_id', '{{%ticket}}');
        $this->dropIndex('idx-ticket-screening-seat', '{{%ticket}}');
        $this->dropIndex('idx-ticket-screening_id', '{{%ticket}}');
        $this->dropTable('{{%ticket}}');
    }
}
