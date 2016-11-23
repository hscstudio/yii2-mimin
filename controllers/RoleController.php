<?php

namespace hscstudio\mimin\controllers;

use Yii;
use hscstudio\mimin\models\AuthItem;
use hscstudio\mimin\models\AuthItemSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * AuthItemController implements the CRUD actions for AuthItem model.
 */
class RoleController extends Controller
{
	public function behaviors()
	{
		return [
			'access' => [
				'class' => AccessControl::className(),
				'rules' => [
					[
						'allow' => true,
						'roles' => ['@'],
					],
				],
			],
			'verbs' => [
				'class' => VerbFilter::className(),
				'actions' => [
					'delete' => ['post'],
				],
			],
		];
	}

	/**
	 * Lists all AuthItem models.
	 * @return mixed
	 */
	public function actionIndex()
	{
		$searchModel = new AuthItemSearch([
			'type' => 1
		]);
		$dataProvider = $searchModel->search(Yii::$app->request->queryParams);

		return $this->render('index', [
			'searchModel' => $searchModel,
			'dataProvider' => $dataProvider,
		]);
	}

	/**
	 * Displays a single AuthItem model.
	 * @param string $id
	 * @return mixed
	 */
	public function actionView($id)
	{
		$model = $this->findModel($id);

		return $this->render('view', [
			'model' => $model,
		]);
	}

	/**
	 * Creates a new AuthItem model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 * @return mixed
	 */
	public function actionCreate()
	{
		$model = new AuthItem();

		if ($model->load(Yii::$app->request->post())) {
			$auth = Yii::$app->authManager;
			$admin = $auth->createRole($model->name);
			$auth->add($admin);
			$model->save();
			return $this->redirect(['view', 'id' => $model->name]);
		} else {
			return $this->render('create', [
				'model' => $model,
			]);
		}
	}

	/**
	 * Updates an existing AuthItem model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param string $id
	 * @return mixed
	 */
	public function actionUpdate($id)
	{
		$model = $this->findModel($id);

		if ($model->load(Yii::$app->request->post())) {
			$auth = Yii::$app->authManager;
			$admin = $auth->createRole($model->name);
			$auth->update($model->name, $admin);
			$model->save();
			return $this->redirect(['view', 'id' => $model->name]);
		} else {
			return $this->render('update', [
				'model' => $model,
			]);
		}
	}

	/**
	 * Deletes an existing AuthItem model.
	 * If deletion is successful, the browser will be redirected to the 'index' page.
	 * @param string $id
	 * @return mixed
	 */
	public function actionDelete($id)
	{
		$model = $this->findModel($id);
		$auth = Yii::$app->authManager;
		$admin = $auth->createRole($model->name);
		$auth->remove($admin);

		return $this->redirect(['index']);
	}

	/**
	 * Finds the AuthItem model based on its primary key value.
	 * If the model is not found, a 404 HTTP exception will be thrown.
	 * @param string $id
	 * @return AuthItem the loaded model
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	protected function findModel($id)
	{
		if (($model = AuthItem::findOne($id)) !== null) {
			return $model;
		} else {
			throw new NotFoundHttpException('The requested page does not exist.');
		}
	}

	public function actionPermission($roleName, $permissionName)
	{
		\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
		$auth = Yii::$app->authManager;
		$roleExist = $auth->getRole($roleName);
		$msg = 'no exec';
		if ($roleExist) {
			$role = $auth->createRole($roleName);
			$permissionExist = $auth->getPermission($permissionName);
			if ($permissionExist) {
				$permission = $auth->createPermission($permissionName);
			} else {
				$permission = $auth->createPermission($permissionName);
				$auth->add($permission);
			}

			if ($auth->hasChild($role, $permission)) {
				$auth->removeChild($role, $permission);
				//$auth->remove($permission);
				$msg = 'permission removed';
			} else {
				$auth->addChild($role, $permission);
				$msg = 'permission added';
			}
		}

		return ['data' => $msg];
	}
}
