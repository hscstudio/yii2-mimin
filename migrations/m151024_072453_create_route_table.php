<?php

use yii\db\Schema;
use yii\db\Migration;

class m151024_072453_create_route_table extends Migration
{
    public function up()
    {
		$this->createTable('route', [
            'name' => $this->string(64)->primaryKey(),
            'alias' => $this->string(64)->notNull(),
            'type' => $this->string(64)->notNull(),
            'status' => $this->smallInteger()->notNull()->defaultValue(1),
        ]);
    }

    public function down()
    {
        echo "m151024_072453_create_route_table cannot be reverted.\n";
		$this->dropTable('route');
        return false;
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
