<?php

namespace MDSPokemonApi\Controller;

use MDSPokemonApi\Utils\ResponseUtils;
use MDSPokemonApi\Utils\GlobalUtils as du;

class MockDBController {

  public static $dirMockDB = __DIR__.'/../../pokemon_db';

  public static function loadMockDb(ResponseUtils $response) {

    $allPokemon           = du::serverUri().'?page_name=pokemonDB_GetPokemonList';
    $pokemonByName        = du::serverUri().'?page_name=pokemonDB_GetPokemonDataByName&pokemon_name=';
    $contentAllPokemon    = json_decode(du::getPageContent($allPokemon), true);

    $data = array(
      'created'     => 0,
      'failed'      => 0
    );

    if( !empty($contentAllPokemon['error']) ) {
      return $response->toJsonWithCode('pokemon list all not found', 'error');
    }

    foreach ($contentAllPokemon['success']['data'] as $pokemonName) {
      $contentPokemonByName = json_decode(du::getPageContent($pokemonByName.$pokemonName), true);
      if( !empty($contentPokemonByName['error']) ) {
        $data['failed']++;
        continue;
      }
      $try = du::createPokemonJsonFile($pokemonName.'.json', json_encode($contentPokemonByName['success']['data']));
      if($try) {
        $data['created']++;
        continue;
      }
      $data['failed']++;
    }

    return $response->toJsonWithCode($data, 'success');
  }

  public static function getMockPokemonList(ResponseUtils $response) {
    if( !is_dir(self::$dirMockDB) ) {
      return $response->toJsonWithCode('wrong dir', 'error');
    }
    $r        = scandir(self::$dirMockDB);
    if( empty($r) ) {
      return $response->toJsonWithCode(array(), 'success');
    }
    $rDiff    = array('..', '.');
    $data     = array_diff($r, $rDiff);
    return $response->toJsonWithCode($data, 'success');
  }

  public static function getPokemonDataByName(ResponseUtils $response) {
    if( empty($_GET['pokemon_name']) ) {
      return $response->toJsonWithCode('get parameter [pokemon_name] needs to be set.', 'error', 404);
    }
    $pokemonName    = strtolower($_GET['pokemon_name']);
    $pokemonURI     = self::$dirMockDB.'/'.$pokemonName .'.json';
    if( !file_exists(self::$dirMockDB.'/'.$pokemonName .'.json') ) {
      return $response->toJsonWithCode('pokemon not found', 'error');
    }
    // --- TODO : json_decode on all .json file.
    $pokemonData    = json_decode(file_get_contents($pokemonURI));
    return $response->toJsonWithCode($pokemonData, 'success');
  }
}