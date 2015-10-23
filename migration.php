<?php
use yii\db\Schema;
/**
 * Migration table of route
 * 
 * @author Hafid <hafidmukhlasin@gmail.com>
 * @since 1.0
 */
class m150101_185401_create_route extends \yii\db\Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('route', [
            'name' => $this->string(64)->primaryKey(),
            'alias' => $this->string(64)->notNull(),
            'type' => $this->string(64)->notNull(),
            'status' => $this->boolean()->defaultValue(1),
        ]);
    }
    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('route');
    }
}
