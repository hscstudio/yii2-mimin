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
   * $items=[
   *     ['label' => 'User', 'url' => ['/mimin/user']],
   *     ['label' => 'Role', 'url' => ['/mimin/role']],
   *     ['label' => 'Route', 'url' => ['/mimin/route']],
   * ];
   * $items = Mimin::filterRouteMenu($items);
   * if(count($items)>0){
   *    $menuItems[] = ['label' => 'Administrator', 'items' => $items];
   * }
   */
  public static function filterRoute($route,$strict=false)
  {
      $allowedRoutes = [];
      $user = Yii::$app->user;
      $permission = (substr($route,0,1)=='/')?$route:'/'.$route;
      if ($user->can($permission)) {
          return true;
      }

      if(!$strict){
          $pos = (strrpos($permission, '/'));
          $parent = substr($permission, 0, $pos);
          $authItems = AuthItem::find()->where(['like','name',$parent])->all();
          foreach ($authItems as $authItem) {
              $permission = $authItem->name;
              if ($user->can($permission)) {
                  return true;
              }
          }
      }

      return false;
  }

  /**
   * @inheritdoc
   * $items=[
   *     ['label' => 'User', 'url' => ['/mimin/user']],
   *     ['label' => 'Role', 'url' => ['/mimin/role']],
   *     ['label' => 'Route', 'url' => ['/mimin/route']],
   * ];
   * $items = Mimin::filterRouteMenu($items);
   * if(count($items)>0){
   *    $menuItems[] = ['label' => 'Administrator', 'items' => $items];
   * }
   */
  public static function filterRouteMenu($routes,$strict=false)
  {
      $allowedRoutes = [];
      $hr = 0;
      foreach ($routes as $route) {
          $value = ArrayHelper::getValue($route, 'url');
          if(is_array($value)){
              $permission = $value[0];
              $allowed = self::filterRoute($permission,$strict);
              if ($allowed) {
                  $allowedRoutes[] = $route;
                  continue;
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

  /**
   * @inheritdoc
   * Mimin::filterTemplateActionColumn(['update','delete'=>'drop','download'],$this->context->route)
   * output {update} {delete} {download}
   * what's about 'delete' and 'drop'?
   * if button name different with route name
   * but for best practice, it should same
   */
  public static function filterTemplateActionColumn($actions,$currentRoute)
  {
      $template = '';
      $pos = (strrpos($currentRoute, '/'));
      $parent = substr($currentRoute, 0, $pos);
      foreach ($actions as $key => $value) {
          if(is_integer($key)){
              $action = $value;
              $permission = $parent . '/' . $action;
          }
          else{
              $action = $key;
              $permission = $parent . '/' . $action;
          }
          $button = "{".$value."} ";
          $allowed = self::filterRoute($permission,true);
          if ($allowed) {
              $template .= $button;
              continue;
          }
          else{
              $allowed = self::filterRoute($parent . '/' . '*',true);
              if ($allowed) {
                  $template .= $button;
              }
          }

      }
      return trim($template);
  }

}
