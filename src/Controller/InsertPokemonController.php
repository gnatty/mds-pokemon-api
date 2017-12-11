<?php

namespace MDSPokemonApi\Controller;

use MDSPokemonApi\Utils\ResponseUtils;
use MDSPokemonApi\Utils\GlobalUtils as du;
use MDSPokemonApi\Utils\MysqlPdoUtils;
use MDSPokemonApi\Utils\PokemonRequestUtils;

class InsertPokemonController {

  public static function ping(ResponseUtils $response) {
    $data = 'pong';
    return $response->toJsonWithCode(
      $data,
      'success'
    );
  }

  public static function insertEvol(ResponseUtils $response, MysqlPdoUtils $db) {

    $pokRequest           = new PokemonRequestutils($db);
    $pokemonName          = 'venusaur';
    $uri                  = du::serverUri() . '?page_name=mockDB_GetPokemonDataByName&pokemon_name=';
    $contentPokemon       = json_decode(du::getPageContent($uri . $pokemonName), true);
    if( !empty($contentPokemon['error']) ) {
      return $response->toJsonWithCode('pokemon not found', 'error');
    } 
    $contentPokemon       = $contentPokemon['success']['data'];
    // ---
    $evols = $contentPokemon['evols'];

    $queryInsert = '
      INSERT INTO
        pokemon_evolution
        (pev_cur_pokemon, pev_next_pokemon, pev_lvl)
      VALUES
        (:pev_cur_pokemon, :pev_next_pokemon, :pev_lvl)
      ;
    ';
    foreach ($evols as $evol) {

      $cur        = $pokRequest->getPokemonIdByName($evol['cur']['code']);
      $next       = $pokRequest->getPokemonIdByName($evol['next']['code']); 

      if($cur['code'] === 200 && $next['code'] === 200) {
        $result   = $pokRequest->insertEvolution(
          $cur['data'], 
          $next['data'], 
          $evol['lvl']
        );
        // --- TODO : handle error.
      }

    }
    // resp.
    return $response->toJsonWithCode(
      true,
      'success'
    );
  }

  public static function insertPokemon(ResponseUtils $response, MysqlPdoUtils $db) {

    $pokRequest           = new PokemonRequestutils($db);
    $pokemonName          = 'venusaur';
    $uri                  = du::serverUri() . '?page_name=mockDB_GetPokemonDataByName&pokemon_name=';
    $contentPokemon       = json_decode(du::getPageContent($uri . $pokemonName), true);

    if( !empty($contentPokemon['error']) ) {
      return $response->toJsonWithCode('pokemon not found', 'error');
    } 

    // --- @table(name="pokemon")
    $pokemons = $contentPokemon['success']['data']['pokemons'];
    foreach ($pokemons as $pokemon) {
      // --- @pokemon
      $ret        = $pokRequest->insertPokemon($pokemon);
      $pokemonId  = $ret['data'];
      // --- @pokemon_stats_ability
      $abilities  = $pokemon['stats']['pokedex']['abilities'];
      $ret2       = $pokRequest->insertPokemonAbilities($abilities, $pokemonId);


    }

    exit();
  }

  public static function insertAbilities(ResponseUtils $response, MysqlPdoUtils $db) {

    $pokemonId            = 2;
    $pokRequest           = new PokemonRequestutils($db);
    $pokemonName          = 'venusaur';
    $uri                  = du::serverUri() . '?page_name=mockDB_GetPokemonDataByName&pokemon_name=';
    $contentPokemon       = json_decode(du::getPageContent($uri . $pokemonName), true);

    if( !empty($contentPokemon['error']) ) {
      return $response->toJsonWithCode('pokemon not found', 'error');
    } 

    $abilities = $contentPokemon['success']['data']['moves'];

    foreach ($abilities as $ability) {
      du::pre($ability);
      $abilityId  = $pokRequest->getPokemonGlobalAbilityByName($ability['name']);
      $check      = $pokRequest->checkIfPokemonAbilityExist($pokemonId, $abilityId);
      // -- INSERT
      if( $check['code'] === 404) {
        $r = $pokRequest->insertPokemonAbility($pokemonId, $abilityId);
        du::pre($r);
      }
    }

  }


















}