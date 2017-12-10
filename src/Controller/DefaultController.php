<?php

namespace MDSPokemonApi\Controller;

use MDSPokemonApi\Utils\ResponseUtils;

class DefaultController {

  public static function ping(ResponseUtils $res) {

    $data = 'pong';

    return $res->toJsonWithCode(
      $data,
      'success'
    );
  }

}