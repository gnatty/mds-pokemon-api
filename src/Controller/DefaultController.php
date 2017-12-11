<?php

namespace MDSPokemonApi\Controller;

use MDSPokemonApi\Utils\ResponseUtils;
use MDSPokemonApi\Utils\GlobalUtils as du;
use MDSPokemonApi\Utils\MysqlPdoUtils;

class DefaultController {

  public static $dirMockDB = __DIR__.'/../../pokemon_db';

  public static function ping(ResponseUtils $response) {

    $data = 'pong';

    return $response->toJsonWithCode(
      $data,
      'success'
    );
  }

  public static function test(ResponseUtils $response, MysqlPdoUtils $db) {
    $a = $db->ob();
    $req = $a->prepare('INSERT INTO pokemon_global_checkout(pgc_name) VALUES("ssssss");');
    $req->execute();
    return $response->toJsonWithCode(
      $req->errorInfo(),
      'success'
    );
  }

}