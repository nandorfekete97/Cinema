<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%screening}}`.
 */
class m260108_115808_create_screening_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%screening}}', [
            'id' => $this->primaryKey(),
            'movie_title' => $this->string()->notNull(),
            'screening_date' => $this->date()->notNull(),
            'start_time' => $this->time()->notNull(),
            'end_time' => $this->time()->notNull(),
            'ticket_price' => $this->decimal()->notNull(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%screening}}');
    }
}
