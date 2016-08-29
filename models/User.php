<?php

namespace hscstudio\mimin\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "user".
 *
 * @property integer $id
 * @property string $username
 * @property string $auth_key
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $email
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 */
class User extends \yii\db\ActiveRecord
{
	public $new_password, $old_password, $repeat_password;

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'user';
	}

	/**
	 * @inheritdoc
	 */
	public function behaviors()
	{
		return [
			TimestampBehavior::className(),
		];
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['username', 'email'], 'required'],
			[['username', 'email', 'password_hash'], 'string', 'max' => 255],
			[['username', 'email'], 'unique'],
			[['email'], 'email'],
			['status','integer'],
			[['old_password', 'new_password', 'repeat_password'], 'string', 'min' => 6],
			[['repeat_password'], 'compare', 'compareAttribute' => 'new_password'],
			[['old_password', 'new_password', 'repeat_password'], 'required', 'when' => function ($model) {
				return (!empty($model->new_password));
			}, 'whenClient' => "function (attribute, value) {
                return ($('#user-new_password').val().length>0);
            }"],
			//['username', 'filter', 'filter' => 'trim'],
			//['username', 'unique', 'targetClass' => '\app\models\User', 'message' => 'This username has already been taken.'],
		];
	}

	public function scenarios()
	{
		$scenarios = parent::scenarios();
		$scenarios['password'] = ['old_password', 'new_password', 'repeat_password'];
		return $scenarios;
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'id' => 'ID',
			'username' => 'Username',
			'password_hash' => 'Password Hash',
			'email' => 'Email',
		];
	}

	/**
	 * Validates password
	 *
	 * @param string $password password to validate
	 * @return boolean if password provided is valid for current user
	 */
	public function validatePassword($password)
	{
		return Yii::$app->security->validatePassword($password, $this->password_hash);
	}

	/**
	 * Generates password hash from password and sets it to the model
	 *
	 * @param string $password
	 */
	public function setPassword($password)
	{
		$this->password_hash = Yii::$app->security->generatePasswordHash($password);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getRoles()
	{
		return $this->hasMany(AuthAssignment::className(), [
			'user_id' => 'id',
		]);
	}
}
