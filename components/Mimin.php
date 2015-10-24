<?php

namespace hscstudio\mimin\components;

use Yii;
use yii\helpers\ArrayHelper;

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
      $hr = 0;
      foreach ($routes as $route) {
          $value = ArrayHelper::getValue($route, 'url');
          if(is_array($value)){
              if ($user->can('/' . $value[0]) or $user->can($value[0])) {
                  $allowedRoutes[] = $route;
              }
          }
          else {
              $allowedRoutes[] = $route;
              $hr++;
          }
      }
      if(count($allowedRoutes) == $hr) $allowedRoutes = [];
      return $allowedRoutes;
  }

}
