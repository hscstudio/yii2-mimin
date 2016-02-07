<?php

namespace hscstudio\mimin\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use hscstudio\mimin\models\Route;

/**
 * RouteSearch represents the model behind the search form about `hscstudio\mimin\models\Route`.
 */
class RouteSearch extends Route
{
	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['name', 'alias', 'type'], 'safe'],
			[['status'], 'integer'],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function scenarios()
	{
		// bypass scenarios() implementation in the parent class
		return Model::scenarios();
	}

	/**
	 * Creates data provider instance with search query applied
	 *
	 * @param array $params
	 *
	 * @return ActiveDataProvider
	 */
	public function search($params)
	{
		$query = Route::find();

		$dataProvider = new ActiveDataProvider([
			'query' => $query,
		]);

		$this->load($params);

		if (!$this->validate()) {
			// uncomment the following line if you do not want to return any records when validation fails
			// $query->where('0=1');
			return $dataProvider;
		}

		$query->andFilterWhere([
			'status' => $this->status,
		]);

		$query->andFilterWhere(['like', 'name', $this->name])
			->andFilterWhere(['like', 'alias', $this->alias])
			->andFilterWhere(['like', 'type', $this->type]);

		return $dataProvider;
	}
}
