<?php

namespace MDSPokemonApi\Utils;

use MDSPokemonApi\Utils\MysqlPdoUtils;
use MDSPokemonApi\Utils\GlobalUtils as du;

class PokemonRequestUtils {

  private $_db;

  public function __construct(MysqlPdoUtils $db) {
    $this->_db = $db;
  }

  public function db() {
    return $this->_db;
  }

  public function ob() {
    return $this->db()->ob();
  }
  
  /**
   * @param $pokemonName string - pokemon name
   * @return pokemon.pok_id - pokemon id
   */
  public function getPokemonIdByName($pokemonName) {
    $querySelectPokemonByName = '
      SELECT
        pok_id
      FROM
        pokemon
      WHERE
        pok_name    = :pok_name
      ;
    ';
    $queryInsertPokemonByName = '
      INSERT INTO
        pokemon
        (pok_name)
      VALUES
        (:pok_name)
      ;
    ';
    $params = array(
      'pok_name' => $pokemonName
    );
    $reqSelectPok   = $this->ob()->prepare($querySelectPokemonByName);
    $reqSelectPok->execute($params);
    $result         = $reqSelectPok->fetch();
    if( empty($result) ) {
      $reqInsertPokByName = $this->ob()->prepare($queryInsertPokemonByName);
      $reqInsertPokByName->execute($params);

      if(!$this->db()->isError($reqInsertPokByName)) {
        return du::dbResult('insert', $this->db()->lastInsertId($reqInsertPokByName), 200);
      } else {
        return du::dbResult('insert error', $this->db()->errorInfo($reqInsertPokByName), 404);
      }
    } else {
      return du::dbResult('select', $result['pok_id'], 200);
    }
  }

  /**
   * @param $cur string - pokemon name current state
   * @param $next string - pokemin name next state
   * @param $lvl string - evolution state
   * @return action type success/error
   */
  public function insertEvolution($cur, $next, $lvl) {
    $queryInsert = '
      INSERT INTO
        pokemon_evolution
        (pev_cur_pokemon, pev_next_pokemon, pev_lvl)
      VALUES
        (:pev_cur_pokemon, :pev_next_pokemon, :pev_lvl)
      ;
    ';
    $querySelect = '
      SELECT
        *
      FROM
        pokemon_evolution
      WHERE
        pev_cur_pokemon       = :pev_cur_pokemon
      AND 
        pev_next_pokemon      = :pev_next_pokemon
      ;
    ';
    $queryUpdate = '
      UPDATE
        pokemon_evolution
      SET
        pev_lvl = :pev_lvl
      WHERE
        pev_cur_pokemon       = :pev_cur_pokemon
      AND
        pev_next_pokemon      = :pev_next_pokemon
      ;
    ';

    $params = array(
      'pev_cur_pokemon'       => $cur,
      'pev_next_pokemon'      => $next
    );

    $reqSelectEvol = $this->ob()->prepare($querySelect);
    $reqSelectEvol->execute($params);
    $result        = $reqSelectEvol->fetch();
    if( empty($result) ) {
      $reqInsertEvol = $this->ob()->prepare($queryInsert);
      $params['pev_lvl'] = $lvl;
      $reqInsertEvol->execute($params);
      if(!$this->db()->isError($reqInsertEvol)) {
        return du::dbResult('insert', $this->db()->lastInsertId($reqInsertEvol), 200);
      } else {
        return du::dbResult('insert error', $this->db()->errorInfo($reqInsertEvol), 404);
      }
    } else {
      $reqUpdateEvol = $this->ob()->prepare($queryUpdate);
      $params['pev_lvl'] = $lvl;
      $reqUpdateEvol->execute($params);
      if(!$this->db()->isError($reqUpdateEvol)) {
        return du::dbResult('update', true, 200);
      } else {
        return du::dbResult('update error', $this->db()->errorInfo($reqUpdateEvol), 404);
      }
    }

  }


















}