<?php

namespace hscstudio\mimin\controllers;

use Yii;
use hscstudio\mimin\models\Route;
use hscstudio\mimin\models\RouteSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

use hscstudio\mimin\components\Configs;
use yii\helpers\Inflector;
use yii\helpers\VarDumper;
use yii\caching\TagDependency;
use yii\web\Response;

/**
 * RouteController implements the CRUD actions for Route model.
 */
class RouteController extends Controller
{
	const CACHE_TAG = 'mdm.admin.route';

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
	 * Lists all Route models.
	 * @return mixed
	 */
	public function actionIndex()
	{
		$searchModel = new RouteSearch([
			'status' => 1,
		]);
		$dataProvider = $searchModel->search(Yii::$app->request->queryParams);
		$dataProvider->getSort()->defaultOrder = [
			'type' => SORT_ASC,
		];
		return $this->render('index', [
			'searchModel' => $searchModel,
			'dataProvider' => $dataProvider,
		]);
	}

	/**
	 * Displays a single Route model.
	 * @param string $id
	 * @return mixed
	 */
	public function actionView($id)
	{
		return $this->render('view', [
			'model' => $this->findModel($id),
		]);
	}

	/**
	 * Creates a new Route model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 * @return mixed
	 */
	public function actionCreate()
	{
		$model = new Route();

		if ($model->load(Yii::$app->request->post())) {
			$model->save();
			return $this->redirect(['view', 'id' => $model->name]);
		} else {
			return $this->render('create', [
				'model' => $model,
			]);
		}
	}

	/**
	 * Updates an existing Route model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param string $id
	 * @return mixed
	 */
	public function actionUpdate($id)
	{
		$model = $this->findModel($id);

		if ($model->load(Yii::$app->request->post()) && $model->save()) {
			return $this->redirect(['view', 'id' => $model->name]);
		} else {
			return $this->render('update', [
				'model' => $model,
			]);
		}
	}

	/**
	 * Deletes an existing Route model.
	 * If deletion is successful, the browser will be redirected to the 'index' page.
	 * @param string $id
	 * @return mixed
	 */
	public function actionDelete($id)
	{
		$this->findModel($id)->delete();

		return $this->redirect(['index']);
	}

	/**
	 * Deletes an existing Route model.
	 * If deletion is successful, the browser will be redirected to the 'index' page.
	 * @param string $id
	 * @return mixed
	 */
	public function actionGenerate()
	{
		$routes = $this->searchRoute('all');
		foreach ($routes as $route => $status) {
			if (!Route::findOne($route)) {
				$model = new Route();
				$model->name = $route;
				$pos = (strrpos($route, '/'));
				$model->type = substr($route, 1, $pos - 1);
				$model->alias = substr($route, $pos + 1, 64);
				$model->save();
			}
		}
		Yii::$app->session->setFlash('success', 'Route success generate');
		return $this->redirect(['index']);
	}

	/**
	 * Finds the Route model based on its primary key value.
	 * If the model is not found, a 404 HTTP exception will be thrown.
	 * @param string $id
	 * @return Route the loaded model
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	protected function findModel($id)
	{
		if (($model = Route::findOne($id)) !== null) {
			return $model;
		} else {
			throw new NotFoundHttpException('The requested page does not exist.');
		}
	}

	/**
	 * Search Route
	 * @param string $target
	 * @param string $term
	 * @param string $refresh
	 * @return array
	 */
	public function searchRoute($target, $term = '', $refresh = '0')
	{
		if ($refresh == '1') {
			$this->invalidate();
		}
		$result = [];
		$manager = Yii::$app->getAuthManager();

		$exists = array_keys($manager->getPermissions());
		$routes = $this->getAppRoutes();
		if ($target == 'available') {
			foreach ($routes as $route) {
				if (in_array($route, $exists)) {
					continue;
				}
				if (empty($term) or strpos($route, $term) !== false) {
					$result[$route] = true;
				}
			}
		} else if ($target == 'all') {
			foreach ($routes as $route) {
				$available = 0;
				if (in_array($route, $exists)) {
					$available = 1;
				}
				if (empty($term) or strpos($route, $term) !== false) {
					$result[$route] = $available;
				}
			}
		} else {
			foreach ($exists as $name) {
				if ($name[0] !== '/') {
					continue;
				}
				if (empty($term) or strpos($name, $term) !== false) {
					$r = explode('&', $name);
					$result[$name] = !empty($r[0]) && in_array($r[0], $routes);
				}
			}
		}

		//Yii::$app->response->format = 'json';
		return $result;
	}

	/**
	 * Get list of application routes
	 * @return array
	 */
	public function getAppRoutes()
	{
		$key = __METHOD__;
		$cache = Configs::instance()->cache;
		if ($cache === null || ($result = $cache->get($key)) === false) {
			$result = [];
			$this->getRouteRecrusive(Yii::$app, $result);
			if ($cache !== null) {
				$cache->set($key, $result, Configs::instance()->cacheDuration, new TagDependency([
					'tags' => self::CACHE_TAG
				]));
			}
		}

		return $result;
	}

	/**
	 * Get route(s) recrusive
	 * @param \yii\base\Module $module
	 * @param array $result
	 */
	private function getRouteRecrusive($module, &$result)
	{
		$token = "Get Route of '" . get_class($module) . "' with id '" . $module->uniqueId . "'";
		Yii::beginProfile($token, __METHOD__);
		try {
			foreach ($module->getModules() as $id => $child) {
				if (($child = $module->getModule($id)) !== null) {
					$this->getRouteRecrusive($child, $result);
				}
			}

			foreach ($module->controllerMap as $id => $type) {
				$this->getControllerActions($type, $id, $module, $result);
			}

			$namespace = trim($module->controllerNamespace, '\\') . '\\';
			$this->getControllerFiles($module, $namespace, '', $result);
			$result[] = ($module->uniqueId === '' ? '' : '/' . $module->uniqueId) . '/*';
		} catch (\Exception $exc) {
			Yii::error($exc->getMessage(), __METHOD__);
		}
		Yii::endProfile($token, __METHOD__);
	}

	/**
	 * Get list controller under module
	 * @param \yii\base\Module $module
	 * @param string $namespace
	 * @param string $prefix
	 * @param mixed $result
	 * @return mixed
	 */
	private function getControllerFiles($module, $namespace, $prefix, &$result)
	{
		$path = @Yii::getAlias('@' . str_replace('\\', '/', $namespace));
		$token = "Get controllers from '$path'";
		Yii::beginProfile($token, __METHOD__);
		try {
			if (!is_dir($path)) {
				return;
			}
			foreach (scandir($path) as $file) {
				if ($file == '.' || $file == '..') {
					continue;
				}
				if (is_dir($path . '/' . $file)) {
					$this->getControllerFiles($module, $namespace . $file . '\\', $prefix . $file . '/', $result);
				} elseif (strcmp(substr($file, -14), 'Controller.php') === 0) {
					$id = Inflector::camel2id(substr(basename($file), 0, -14));
					$className = $namespace . Inflector::id2camel($id) . 'Controller';
					if (strpos($className, '-') === false && class_exists($className) && is_subclass_of($className, 'yii\base\Controller')) {
						$this->getControllerActions($className, $prefix . $id, $module, $result);
					}
				}
			}
		} catch (\Exception $exc) {
			Yii::error($exc->getMessage(), __METHOD__);
		}
		Yii::endProfile($token, __METHOD__);
	}

	/**
	 * Get list action of controller
	 * @param mixed $type
	 * @param string $id
	 * @param \yii\base\Module $module
	 * @param string $result
	 */
	private function getControllerActions($type, $id, $module, &$result)
	{
		$token = "Create controller with cofig=" . VarDumper::dumpAsString($type) . " and id='$id'";
		Yii::beginProfile($token, __METHOD__);
		try {
			/* @var $controller \yii\base\Controller */
			$controller = Yii::createObject($type, [$id, $module]);
			$this->getActionRoutes($controller, $result);
			$result[] = '/' . $controller->uniqueId . '/*';
		} catch (\Exception $exc) {
			Yii::error($exc->getMessage(), __METHOD__);
		}
		Yii::endProfile($token, __METHOD__);
	}

	/**
	 * Get route of action
	 * @param \yii\base\Controller $controller
	 * @param array $result all controller action.
	 */
	private function getActionRoutes($controller, &$result)
	{
		$token = "Get actions of controller '" . $controller->uniqueId . "'";
		Yii::beginProfile($token, __METHOD__);
		try {
			$prefix = '/' . $controller->uniqueId . '/';
			foreach ($controller->actions() as $id => $value) {
				$result[] = $prefix . $id;
			}
			$class = new \ReflectionClass($controller);
			foreach ($class->getMethods() as $method) {
				$name = $method->getName();
				if ($method->isPublic() && !$method->isStatic() && strpos($name, 'action') === 0 && $name !== 'actions') {
					$result[] = $prefix . Inflector::camel2id(substr($name, 6));
				}
			}
		} catch (\Exception $exc) {
			Yii::error($exc->getMessage(), __METHOD__);
		}
		Yii::endProfile($token, __METHOD__);
	}

	/**
	 * Ivalidate cache
	 */
	protected function invalidate()
	{
		if (Configs::instance()->cache !== null) {
			TagDependency::invalidate(Configs::instance()->cache, self::CACHE_TAG);
		}
	}

	/**
	 * Set default rule of parameterize route.
	 */
	protected function setDefaultRule()
	{
		if (Yii::$app->authManager->getRule(RouteRule::RULE_NAME) === null) {
			Yii::$app->authManager->add(Yii::createObject([
					'class' => RouteRule::className(),
					'name' => RouteRule::RULE_NAME]
			));
		}
	}
}
