<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ----------------------------------------------------------------
// --------- COMPOSER AUTOLOAD
// ----------------------------------------------------------------
require __DIR__ . '/../vendor/autoload.php';

use MDSPokemonApi\Utils\GlobalUtils as du;
use Symfony\Component\Yaml\Yaml;
use MDSPokemonApi\Exception\RouteNotFoundException;
// ----------------------------------------------------------------
// ----------------------------------------------------------------

try {

  if( empty($_GET['page_name']) ) {
    throw new RouteNotFoundException();
  }

  $route    = Yaml::parseFile(__DIR__ . '/../config/route.yml');
  if( empty($route['route']) ) {
    throw new Symfony\Component\Yaml\Exception\ParseException('');
  }
  $route    = $route['route'];
  $curRoute = $_GET['page_name'];

  if( !in_array($curRoute, array_keys($route)) ) {
    throw new RouteNotFoundException();
  }

  $args     = array();
  $ref      = new \ReflectionMethod($route[$curRoute]); 

  foreach($ref->getParameters() as $key => $param) {
    $paramName              = $param->getName();
    $args[$paramName]       = '';
    if($param->hasType()) {
      // Inst class.
      $callClass            = $param->getClass()->name;
      $args[$paramName]     = new $callClass();
    } elseif($param->isDefaultValueAvailable()) {
        $args[$paramName]   = $param->getDefaultValue();
    }
  }

  $res = call_user_func_array(
    $route[$curRoute],
    $args
  );

  if( !empty($res) ) {
    echo $res;
  }

} catch(Exception $e) {
  $exClass = get_class($e);
  switch ($exClass) {
    case 'MDSPokemonApi\Exception\RouteNotFoundException':
      du::pre('pas de route');
      break;
    case 'ReflectionException':
      du::pre('error route class not found');
      break;
    case 'Symfony\Component\Yaml\Exception\ParseException':
      du::pre('error route file not found');
      break;
    default:
      du::pre('error : ' . $exClass);
      break;
  }
  du::pre($e->getMessage());
}

