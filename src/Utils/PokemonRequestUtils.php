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
   * 
   * @param $pokemonName string - pokemon name
   * 
   * @return pokemon.pok_id - pokemon id
   */
  public function getPokemonIdByName($pokemonName) {
    $pokemonName = strtolower($pokemonName);
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
   *
   * @param $pokemonName string - pokemon name
   * 
   * @return 404 if pokemon dosnt exist or 200.
   */
  public function checkIfPokemonExist($pokemonName) {
    $pokemonName = strtolower($pokemonName);
    $querySelectPokemonByName = '
      SELECT
        pok_id
      FROM
        pokemon
      WHERE
        pok_name    = :pok_name
      ;
    ';
    $params = array(
      'pok_name' => $pokemonName
    );
    $reqSelectPok   = $this->ob()->prepare($querySelectPokemonByName);
    $reqSelectPok->execute($params);
    $result         = $reqSelectPok->fetch();
    if( empty($result) ) {
      return du::dbResult('select', true, 404);
    } else {
      return du::dbResult('select', true, 200);
    }
  }

  /**
   * 
   * @param $cur string - pokemon name current state
   * @param $next string - pokemin name next state
   * @param $lvl string - evolution state
   * 
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

  /**
   * 
   * @param array $pok - array of all pokemon data.
   * 
   * @return action type success/error
   */
  public function insertPokemon($pok) {

    $queryUpdate = '
      UPDATE
        pokemon
      SET
        pok_img             = :pok_img,
        pok_num             = :pok_num,
        pok_gender_male     = :pok_gender_male,
        pok_gender_female   = :pok_gender_female,
        pok_height          = :pok_height,
        pok_weight          = :pok_weight,
        pok_ev_yield        = :pok_ev_yield,
        pok_catch_rate      = :pok_catch_rate,
        pok_base_happ       = :pok_base_happ,
        pok_base_exp        = :pok_base_exp,
        pok_growth_rate     = :pok_growth_rate,
        pok_egg_cycles      = :pok_egg_cycles
      WHERE
        pok_name = :pok_name
      ;
    ';

    $queryInsert = '
      INSERT INTO
        pokemon
        (
        pok_name, 
        pok_img,
        pok_num,
        pok_gender_male,
        pok_gender_female,
        pok_height,
        pok_weight,
        pok_ev_yield,
        pok_catch_rate,
        pok_base_happ,
        pok_base_exp,
        pok_growth_rate,
        pok_egg_cycles
        )
      VALUES
        (
        :pok_name, 
        :pok_img,
        :pok_num,
        :pok_gender_male,
        :pok_gender_female,
        :pok_height,
        :pok_weight,
        :pok_ev_yield,
        :pok_catch_rate,
        :pok_base_happ,
        :pok_base_exp,
        :pok_growth_rate,
        :pok_egg_cycles
        )
      ;
    ';

    $params = array(
      'pok_name'                    => du::checkVal(@$pok['name'],                                      'string' ), 
      'pok_img'                     => du::checkVal(@$pok['img'],                                       'string' ),
      'pok_num'                     => du::checkVal(@$pok['stats']['pokedex']['num'],                   'string' ),
      'pok_gender_male'             => du::checkVal(@$pok['stats']['breeding']['gender']['male'],       'float'),
      'pok_gender_female'           => du::checkVal(@$pok['stats']['breeding']['gender']['female'],     'float'),
      'pok_height'                  => du::checkVal(@$pok['stats']['pokedex']['height'],                'string' ),
      'pok_weight'                  => du::checkVal(@$pok['stats']['pokedex']['weight'],                'string' ),
      'pok_ev_yield'                => du::checkVal(@$pok['stats']['training'][0],                      'string' ),
      'pok_catch_rate'              => du::checkVal(@$pok['stats']['training'][1],                      'string' ),
      'pok_base_happ'               => du::checkVal(@$pok['stats']['training'][2],                      'string' ),
      'pok_base_exp'                => du::checkVal(@$pok['stats']['training'][3],                      'string' ),
      'pok_growth_rate'             => du::checkVal(@$pok['stats']['training'][4],                      'string' ),
      'pok_egg_cycles'              => du::checkVal(@$pok['stats']['breeding']['egg-cycles'],           'string' )
    );

    // -- first check if pokemon exist.
    $check = $this->checkIfPokemonExist($pok['name']);
    // -- INSERT.
    if( $check['code'] === 404 ) {
      $reqInsertPok = $this->ob()->prepare($queryInsert);
      $reqInsertPok->execute($params);
      if(!$this->db()->isError($reqInsertPok)) {
        return du::dbResult('insert', $this->db()->lastInsertId($reqInsertPok), 200);
      } else {
        return du::dbResult('insert error', $this->db()->errorInfo($reqInsertPok), 404);
      }
    } else {
    // -- UPDATE.
      $reqUpdatePok = $this->ob()->prepare($queryUpdate);
      $reqUpdatePok->execute($params);
      if(!$this->db()->isError($reqUpdatePok)) {
        return du::dbResult('update', true, 200);
      } else {
        return du::dbResult('update error', $this->db()->errorInfo($reqUpdatePok), 404);
      }
    }
    // ---
  }













}