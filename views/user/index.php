<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\administrator\models\UserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Users';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-index">

	<h1><?= Html::encode($this->title) ?></h1>
	<?php // echo $this->render('_search', ['model' => $searchModel]); ?>

	<p>
		<?= Html::a('Create User', ['create'], ['class' => 'btn btn-success']) ?>
	</p>

	<?= GridView::widget([
		'dataProvider' => $dataProvider,
		'filterModel' => $searchModel,
		'columns' => [
			['class' => 'yii\grid\SerialColumn'],

			//'id',
			'username',
			//'auth_key',
			//'password_hash',
			//'password_reset_token',
			'email:email',
			[
				'attribute' => 'roles',
				'format' => 'raw',
				'value' => function ($data) {
					$roles = [];
					foreach ($data->roles as $role) {
						$roles[] = $role->item_name;
					}
					return Html::a(implode(', ', $roles), ['view', 'id' => $data->id]);
				}
			],
			[
				'attribute' => 'status',
				'format' => 'raw',
				'options' => [
					'width' => '80px',
				],
				'value' => function ($data) {
					if ($data->status == 10)
						return "<span class='label label-primary'>" . 'Active' . "</span>";
					else
						return "<span class='label label-danger'>" . 'Banned' . "</span>";
				}
			],
			[
				'attribute' => 'created_at',
				'format' => ['date', 'php:d M Y H:i:s'],
				'options' => [
					'width' => '120px',
				],
			],
			[
				'attribute' => 'updated_at',
				'format' => ['date', 'php:d M Y H:i:s'],
				'options' => [
					'width' => '120px',
				],
			],
			[
				'options' => [
					'width' => '80px',
				],
				'class' => 'yii\grid\ActionColumn'
			],
		],
	]); ?>

</div>
