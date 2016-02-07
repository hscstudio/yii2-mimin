<?php

use yii\db\Schema;
use yii\db\Migration;

class m151027_142811_create_route_table extends Migration
{
	public function up()
	{
		$tableOptions = null;
		if ($this->db->driverName === 'mysql') {
			// http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
		}

		$this->createTable('{{%route}}', [
			'name' => Schema::TYPE_STRING . '(64) NOT NULL',
			'alias' => Schema::TYPE_STRING . '(64) NOT NULL',
			'type' => Schema::TYPE_STRING . '(64) NOT NULL',
			'status' => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 1',
			'PRIMARY KEY (name)'
		], $tableOptions);
	}

	public function down()
	{
		echo "m151027_142811_create_route_table cannot be reverted.\n";
		$this->dropTable('{{%route}}');
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
