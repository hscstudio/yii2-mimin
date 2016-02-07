<?php

namespace hscstudio\mimin\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use hscstudio\mimin\models\AuthItem;

/**
 * AuthItemSearch represents the model behind the search form about `hscstudio\mimin\models\AuthItem`.
 */
class AuthItemSearch extends AuthItem
{
	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['name', 'description', 'rule_name', 'data'], 'safe'],
			[['type', 'created_at', 'updated_at'], 'integer'],
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
		$query = AuthItem::find();

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
			'type' => $this->type,
			'created_at' => $this->created_at,
			'updated_at' => $this->updated_at,
		]);

		$query->andFilterWhere(['like', 'name', $this->name])
			->andFilterWhere(['like', 'description', $this->description])
			->andFilterWhere(['like', 'rule_name', $this->rule_name])
			->andFilterWhere(['like', 'data', $this->data]);

		return $dataProvider;
	}
}
