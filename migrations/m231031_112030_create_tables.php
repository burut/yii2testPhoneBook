<?php

use yii\db\Migration;
use yii\db\Schema;

/**
 * Class m231031_112030_create_tables
 */
class m231031_112030_create_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('Person', [
            'id'        => Schema::TYPE_PK,
            'firstname' => Schema::TYPE_STRING . ' NOT NULL',
            'lastname'  => Schema::TYPE_STRING . ' DEFAULT NULL',
            'email'     => Schema::TYPE_STRING . ' DEFAULT NULL',
            'birthday'  => Schema::TYPE_DATE . ' DEFAULT NULL',
        ]);

        $this->createIndex(
            'idx_email',
            'Person',
            'email',
            true
        );

        $this->createTable('Phone', [
            'person_id' => Schema::TYPE_INTEGER,
            'number'    => Schema::TYPE_STRING . ' NOT NULL',
            'PRIMARY KEY(person_id, number)',
        ]);

        $this->createIndex(
            'idx_number',
            'Phone',
            'number',
            true
        );

        $this->addForeignKey(
            'fk_Phone_person',
            'Phone',
            'person_id',
            'Person',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(
            'fk_Phone_person',
            'Phone'
        );

        $this->dropTable('Phone');
        $this->dropTable('Person');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m231029_095953_create_tables cannot be reverted.\n";

        return false;
    }
    */
}
