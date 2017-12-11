<?php

namespace MDSPokemonApi\Controller;

use MDSPokemonApi\Utils\ResponseUtils;
use MDSPokemonApi\Utils\GlobalUtils as du;

class DefaultController {

  public static $dirMockDB = __DIR__.'/../../pokemon_db';

  public static function ping(ResponseUtils $response) {

    $data = 'pong';

    return $response->toJsonWithCode(
      $data,
      'success'
    );
  }

}