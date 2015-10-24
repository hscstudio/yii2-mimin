<?php

namespace hscstudio\mimin\components;

use Yii;

/**
* @author Hafid Mukhlasin <hafidmukhlasin@gmail.com>
* @since 1.0
*/

Class Mimin extends \yii\base\Object
{

  /**
   * @inheritdoc
   */
  public static function filterRouteMenu($routes)
  {
      $allowedRoutes = [];
      $user = Yii::$app->user;
      foreach ($routes as $route) {
          if ($user->can('/' . $route['url']) or $user->can($route['url'])) {
              $allowedRoutes[] = $route;
          }
      }

      return $allowedRoutes;
  }

}
