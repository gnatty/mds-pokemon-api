<?php

namespace MDSPokemonApi\Utils;

class ResponseUtils {

  public function toJson($data) {
    // ---
    header('Content-Type: application/json');
    return json_encode($data);
  }

  public function toJsonWithCode($data, $state, $code = null) {
    $valStat = array('error', 'success');
    if( !in_array($state, $valStat) ) {
      $state = 'success';
    }
    if(empty($code)) {
      $code = 200;
    }
    $res = array(
      $state => array(
        'code' => $code,
        'data' => $data
      )
    );
    // ---
    header('Content-Type: application/json');
    return json_encode($res);
  }

}