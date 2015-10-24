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
   * @inheritdoc
   */
  public static function filterRouteMenu($routes,$strict=false)
  {
      $allowedRoutes = [];
      $user = Yii::$app->user;
      $hr = 0;
      foreach ($routes as $route) {
          $value = ArrayHelper::getValue($route, 'url');
          if(is_array($value)){
              $permision = $value[0];
              if ($user->can('/' . $permision) or $user->can($permision)) {
                  $allowedRoutes[] = $route;
                  continue;
              }

              if(!$strict){
                  /*

                  */
                  $pos = (strrpos($permision, '/'));
                  $parent = substr($permision, 1, $pos-1);

                  $authItems = AuthItem::find()->where(['like','name',$parent])->all();
                  foreach ($authItems as $authItem) {
                      $permision = $authItem->name;
                      if ($user->can('/' . $permision) or $user->can($permision)) {
                          $allowedRoutes[] = $route;
                          break;
                      }
                  }
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
