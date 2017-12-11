<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ----------------------------------------------------------------
// --------- COMPOSER AUTOLOAD
// ----------------------------------------------------------------
require __DIR__ . '/../vendor/autoload.php';

use MDSPokemonApi\Utils\GlobalUtils as du;
use MDSPokemonApi\Utils\ResponseUtils;
use MDSPokemonApi\Exception\RouteNotFoundException;
use Symfony\Component\Yaml\Yaml;

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
  $response     = new ResponseUtils();
  $exClass      = get_class($e);
  switch ($exClass) {
    case 'MDSPokemonApi\Exception\RouteNotFoundException':
      $v = $response->toJsonWithCode('route not found', 'error', 404);
      break;
    case 'ReflectionException':
      $v = $response->toJsonWithCode('route class not found', 'error', 404);
      break;
    case 'Symfony\Component\Yaml\Exception\ParseException':
      $v = $response->toJsonWithCode('route file not found', 'error', 404);
      break;
    default:
      $v = $response->toJsonWithCode('error : ' . $exClass, 'error', 404);
      break;
  }
  echo $v;
}

