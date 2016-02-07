<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\administrator\models\RouteSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Routes';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="route-index">

	<h1><?= Html::encode($this->title) ?></h1>
	<?php // echo $this->render('_search', ['model' => $searchModel]); ?>

	<p>
		<?= Html::a('Create Route', ['create'], ['class' => 'btn btn-success']) ?>
		<?= Html::a('Generate Route', ['generate'], ['class' => 'btn btn-primary']) ?>
	</p>

	<?= GridView::widget([
		'dataProvider' => $dataProvider,
		'filterModel' => $searchModel,
		'columns' => [
			['class' => 'yii\grid\SerialColumn'],
			'type',
			'alias',
			'name',
			[
				'attribute' => 'status',
				'filter' => [0 => 'off', 1 => 'on'],
				'format' => 'raw',
				'options' => [
					'width' => '80px',
				],
				'value' => function ($data) {
					if ($data->status == 1)
						return "<span class='label label-primary'>" . 'On' . "</span>";
					else
						return "<span class='label label-danger'>" . 'Off' . "</span>";
				}
			],
			['class' => 'yii\grid\ActionColumn'],
		],
	]); ?>

</div>
