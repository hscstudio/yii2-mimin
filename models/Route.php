<?php

namespace hscstudio\mimin\models;

use Yii;

/**
 * This is the model class for table "route".
 *
 * @property string $name
 * @property string $alias
 * @property string $type
 */
class Route extends \yii\db\ActiveRecord
{
	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'route';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['name', 'alias'], 'required'],
			[['name', 'alias', 'type'], 'string', 'max' => 64],
			[['status'], 'integer']
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'name' => 'Name',
			'alias' => 'Alias',
			'type' => 'Type',
			'status' => 'Status',
		];
	}
}
