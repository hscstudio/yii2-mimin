<?php

namespace hscstudio\mimin\components;

use Yii;
use yii\helpers\ArrayHelper;
use hscstudio\mimin\models\AuthItem;

/**
 * @author Hafid Mukhlasin <hafidmukhlasin@gmail.com>
 * @since 1.0
 */
Class Mimin extends \yii\base\Object
{
	/**
	 * Method CheckRoute is used for checking if route right to access
	 *
	 * if ((Mimin::checkRoute($this->context->id.'/create'))){
	 *     echo Html::a('Create Foo', ['create'], ['class' => 'btn btn-success']);
	 * }
	 *
	 * @param $route
	 * @param bool $strict
	 * @return bool
	 */
	public static function checkRoute($route, $strict = false)
	{
		$user = Yii::$app->user;
		$permission = (substr($route, 0, 1) == '/') ? $route : '/' . $route;
		if ($user->can($permission)) {
			return true;
		}

		if (!$strict) {
			$pos = (strrpos($permission, '/'));
			$parent = substr($permission, 0, $pos);
			$authItems = AuthItem::find()->where(['like', 'name', $parent])->all();
			foreach ($authItems as $authItem) {
				$permission = $authItem->name;
				if ($user->can($permission)) {
					return true;
				}
			}
		}

		$allowActions = Yii::$app->allowActions;
		foreach ($allowActions as $action) {
			$action = (substr($action, 0, 1) == '/') ? $action : '/' . $action;
			if ($action === '*' or $action === '*/*') {
				return true;
			} else if (substr($action, -1) === '*') {
				$length = strlen($action) - 1;
				return (substr($action, 0, $length) == substr($route, 0, $length));
			} else {
				return ($action == $route);
			}
		}
		return false;
	}

	/**
	 * Method FilterMenu is used for filtering right access menu
	 *
	 * $menuItems = [
	 *     ['label' => 'Home', 'url' => ['/site/index']],
	 *     ['label' => 'About', 'url' => ['/site/about']],
	 * ];
	 *
	 * if (!Yii::$app->user->isGuest){
	 *     $menuItems[] = ['label' => 'App', 'items' => [
	 *         ['label' => 'Category', 'url' => ['/category/index']],
	 *         ['label' => 'Product', 'url' => ['/product/index']],
	 * 	   ]];
	 * }
	 *
	 * $menuItems = Mimin::filterMenu($menuItems);
	 *
	 * echo Nav::widget([
	 *     'options' => ['class' => 'navbar-nav navbar-right'],
	 *     'items' => $menuItems,
	 * ]);
	 *
	 * @param $menus
	 * @param bool $strict
	 * @return array
	 */
	public static function filterMenu($menus, $strict = false)
	{
		$allowedRoutes = [];
		$hr = 0;
		foreach ($menus as $menu) {
			$items = ArrayHelper::getValue($menu, 'items');
			if (is_array($items)) {
				$allowedSubRoutes = [];
				foreach ($items as $item) {
					$urls = ArrayHelper::getValue($item, 'url');
					if (is_array($urls)) {
						$permission = $urls[0];
						$allowed = self::checkRoute($permission, $strict);
						if ($allowed) {
							$allowedSubRoutes[] = $item;
							continue;
						}
					} else {
						$allowedSubRoutes[] = $item;
						$hr++;
					}
				}
				if (count($allowedSubRoutes) > 0) {
					$menu['items'] = $allowedSubRoutes;
					$allowedRoutes[] = $menu;
					continue;
				}
			} else {
				$urls = ArrayHelper::getValue($menu, 'url');
				if (is_array($urls)) {
					$permission = $urls[0];
					$allowed = self::checkRoute($permission, $strict);
					if ($allowed) {
						$allowedRoutes[] = $menu;
						continue;
					}
				} else {
					$allowedRoutes[] = $menu;
					$hr++;
				}
			}
		}
		if (count($allowedRoutes) == $hr) $allowedRoutes = [];
		return $allowedRoutes;
	}

	/**
	 * Method filterActionColumn is used for filtering template of Gridview Action Column
	 *
	 * echo GridView::widget([
	 *     'dataProvider' => $dataProvider,
	 *     'columns' => [
	 *         ...,
	 *         [
	 *            'class' => 'yii\grid\ActionColumn',
	 *            'template' => Mimin::filterActionColumn([
	 *                'update','delete','download'
	 *             ],$this->context->route),
	 *         ]
	 *     ]
	 * ]);
	 *
	 * The output is {update} {delete} {download}
	 *
	 * What's about 'delete' and 'drop'?
	 * If button name different with route name.
	 * But for best practice, it should same
	 *
	 * @param $actions
	 * @param $currentRoute
	 * @return string
	 */
	public static function filterActionColumn($actions, $currentRoute)
	{
		$template = '';
		$pos = (strrpos($currentRoute, '/'));
		$parent = substr($currentRoute, 0, $pos);
		foreach ($actions as $key => $value) {
			if (is_integer($key)) {
				$action = $value;
				$permission = $parent . '/' . $action;
			} else {
				$action = $key;
				$permission = $parent . '/' . $action;
			}
			$button = "{" . $value . "} ";
			$allowed = self::checkRoute($permission, true);
			if ($allowed) {
				$template .= $button;
				continue;
			} else {
				$allowed = self::checkRoute($parent . '/' . '*', true);
				if ($allowed) {
					$template .= $button;
				}
			}
		}
		return trim($template);
	}

}