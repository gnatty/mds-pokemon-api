<?php

namespace MDSPokemonApi\Controller;

use MDSPokemonApi\Utils\ResponseUtils;
use MDSPokemonApi\Utils\GlobalUtils as du;

class PokemonDBController {

  public static function getPokemonList(ResponseUtils $response) {
    // --- SOME URL.
    $dataUrl = array(
      'baseUri'         => 'https://pokemondb.net',
      'pokemonList'     => '/pokedex/all'
    );
    $query = array(
      'getPokemonData'  => '//div[contains(@class, "svtabs-wrapper") and contains(@class, "tabset-basics")]/ul[@class="svtabs-panel-list"]/li'
    );
    // --- On récupère le contenu de la page.
    $content    = @du::getPageContent( $dataUrl['baseUri'] . $dataUrl['pokemonList'] );
    $dom        = new \DOMDocument;
    @$dom->loadHTML($content);

    // --- Récupération du tableau des la liste des pokémons.
    $finder     = new \DomXPath($dom);
    $r          = $finder->query('//table[@id="pokedex"]/tbody/tr/td/a[@class="ent-name"]');
    // --- Tableau des liens des pokémons.
    $pokededUrl = array();

    // --- On parcourt pour récupérer les urls des pokémons.
    foreach ($r as $pokemon) {
      $url = $pokemon->attributes[1]->value;
      $url = str_replace('/pokedex/', '', $url);
      array_push($pokededUrl, $url);
    }

    // --- On supprime les doublons.
    $pokededUrl = array_unique($pokededUrl);
    // --- res.
    return $response->toJsonWithCode(
      $pokededUrl,
      'success'
    );
  }

  public static function getPokemonDataByName(ResponseUtils $response) {
    // ---
    if( empty($_GET['pokemon_name']) ) {
      return $response->toJsonWithCode('pokemon not found', 'error', 404);
    }
    // ---
    // --- SOME URL.
    $dataUrl = array(
      'baseUri'         => 'https://pokemondb.net',
      'pokemonList'     => '/pokedex/all'
    );

    $query = array(
      'getPokemonData'  => '//div[contains(@class, "svtabs-wrapper") and contains(@class, "tabset-basics")]/ul[@class="svtabs-panel-list"]/li'
    );

    // --- Pokemon name url.
    $url            = $_GET['pokemon_name'];

    // --- Output data.
    $pokemonsData   = array();
    $evolsData      = array();
    $movesData      = array();

    // ------------------------------------------------------
    // ------------------------------------------------------
    $localURI   = $dataUrl['baseUri'] . '/pokedex/' . $url;
    $content    = @du::getPageContent( $localURI );

    if( empty($content) ) {
      return $response->toJsonWithCode('pokemon not found', 'error', 404);
    }

    $dom        = new \DOMDocument;
    @$dom->loadHTML( $content );
    $finder     = new \DomXPath($dom);

    // ---------------------------------------------------------------------------------------
    // --- POKEMON MOVES DATA
    // ---------------------------------------------------------------------------------------
    $r            = $finder->query('//li[contains(@id, "svtabs_moves")]//table/tbody');
    $moves        = $r[0];
    foreach ($moves->childNodes as $move) {

      $moveData   = array();
      $mv         = $move->childNodes;

      $moveData['lvl']    = $mv[0]->nodeValue;
      $moveData['name']   = $mv[1]->nodeValue;
      $moveData['type']   = strtolower( $mv[2]->nodeValue );
      $moveData['cat']    = strtolower( $mv[3]->childNodes[0]->attributes[2]->nodeValue );
      $moveData['power']  = intval($mv[5]->nodeValue);
      $moveData['acc']    = intval($mv[7]->nodeValue);

      array_push($movesData, $moveData);
    }
    $r            = $finder->query($query['getPokemonData']);


    // ---------------------------------------------------------------------------------------
    // --- EVOLUTION DATA
    // ---------------------------------------------------------------------------------------
    $evol         = $finder->query('//div[@class="infocard-evo-list"]');

    foreach ($evol as $vol) {
      $evol       = array();
      foreach ($vol->childNodes as $typ) {
        if( !empty($typ->tagName) && $typ->tagName == 'span') {
          array_push($evol, $typ);
        } 
      }

      $e = 0;
      while(true) {
        if( empty($evol[$e+2]) ) {
          break;
        }
        $tmp      = array();
        $cur      = $evol[$e];
        $e++;
        $lvl      = $evol[$e];
        $e++;
        $next     = $evol[$e];
        // -- get current state.
        $typ = $cur;
        if( !empty($typ->childNodes[0]->childNodes[0]->attributes[1]->nodeValue)) {
          $img = $typ->childNodes[0]->childNodes[0]->attributes[1]->nodeValue;
          $tmp['cur']['img']    = $img;
          $firstNameSearch      = preg_match("/^(.*)\/(.*).(jpg|png)$/", $img, $res);
          $tmp['cur']['code']   = $res[2];
        }
        $try = preg_match("/([0-9]+)/", $typ->nodeValue, $res);
        $tmp['cur']['id'] = $res[0];
        // -- get next evol.
        $typ = $next;
        if( !empty($typ->childNodes[0]->childNodes[0]->attributes[1]->nodeValue)) {
          $img = $typ->childNodes[0]->childNodes[0]->attributes[1]->nodeValue;
          $tmp['next']['img']   = $img;
          $firstNameSearch      = preg_match("/^(.*)\/(.*).(jpg|png)$/", $img, $res);
          $tmp['next']['code']  = $res[2];
        }
        $try = preg_match("/([0-9]+)/", $typ->nodeValue, $res);
        $tmp['next']['id'] = $res[0];
        // -- get evol lvl.
        $typ = $lvl;
        $try = preg_match("/([0-9]+)/", $typ->nodeValue, $res);
        if( empty($res) ) {
          preg_match("/\((.*)\)$/", $typ->nodeValue, $res);
          $tmp['lvl'] = $res[1];
        } else {
          $tmp['lvl'] = $res[0];
        }

        array_push($evolsData, $tmp);
      }
      // --
    }

    // ---------------------------------------------------------------------------------------
    // --- POKEMONS DATA
    // ---------------------------------------------------------------------------------------

    foreach ($r as $pok) {

      $pokemon    = array();
      $ee         = new \DOMDocument;
      $ee->loadHTML( $dom->saveHTML( $pok ) );
      $xpath      = new \DOMXPath( $ee ); 

      // -- Get pokemon img url.
      $v                = $xpath->query('//img/@src');
      $pokemon['img']   = $v[0]->nodeValue;

      // -- Get pokemon name
      $checkName = preg_match("/^https:\/\/img.pokemondb.net\/artwork\/(.*).jpg$/", $pokemon['img'], $name);
      if( $checkName ) {
        $name = ucwords(str_replace('-', ' ', $name[1]));
        $pokemon['name'] = $name;
      }

      // --- Get pokemon stats
      $v = $xpath->query('//table[@class="vitals-table"]/tbody');
      $pokemon['stats'] = array();

      // ---------------------------------------------------------------------------------------
      // -- POKEDEX DATA.
      // ---------------------------------------------------------------------------------------
      $n    = $v[0]->childNodes;
      // -- n* pokedex.
      $pokemon['stats']['pokedex']                = array();
      $pokemon['stats']['pokedex']['num']         = $n[0]->childNodes[2]->nodeValue;
      $pokemon['stats']['pokedex']['type']        = array();
      foreach ($n[1]->childNodes[2]->childNodes as $type) {
        if( !empty($type->tagName) && $type->tagName == 'a') {
          array_push($pokemon['stats']['pokedex']['type'], strtolower($type->nodeValue) );
        }
      }

      $pokemon['stats']['pokedex']['species']   = utf8_decode($n[2]->childNodes[2]->nodeValue);
      $pokemon['stats']['pokedex']['height']    = utf8_decode($n[3]->childNodes[2]->nodeValue);
      $pokemon['stats']['pokedex']['weight']    = utf8_decode($n[4]->childNodes[2]->nodeValue);

      // -- Abilities
      $pokemon['stats']['pokedex']['abilities'] = array();
      foreach ($n[5]->childNodes[2]->childNodes as $abilities) {
        if( !empty($abilities->tagName) && ($abilities->tagName == 'a' || $abilities->tagName == 'small') ) {
          array_push($pokemon['stats']['pokedex']['abilities'], utf8_decode($abilities->nodeValue));
        }
      }

      // ---------------------------------------------------------------------------------------
      // --- TRAINING DATA.
      // ---------------------------------------------------------------------------------------
      $n    = $v[1]->childNodes;
      $pokemon['stats']['training']             = array();
      array_push( $pokemon['stats']['training'], utf8_decode(trim($n[0]->childNodes[2]->nodeValue)) );
      array_push( $pokemon['stats']['training'], utf8_decode(trim($n[1]->childNodes[2]->nodeValue)) );
      array_push( $pokemon['stats']['training'], utf8_decode(trim($n[2]->childNodes[2]->nodeValue)) );
      array_push( $pokemon['stats']['training'], utf8_decode(trim($n[3]->childNodes[2]->nodeValue)) );
      array_push( $pokemon['stats']['training'], utf8_decode(trim($n[4]->childNodes[2]->nodeValue)) );



      // ---------------------------------------------------------------------------------------
      // --- BREEDING DATA.
      // ---------------------------------------------------------------------------------------
      $n    = $v[2]->childNodes;
      $pokemon['stats']['breeding']                 = array();

      // -- Egg group.
      $pokemon['stats']['breeding']['egg-groups']   = array();
      foreach ($n[0]->childNodes[2]->childNodes as $egggroup) {
        if( !empty($egggroup->tagName) && $egggroup->tagName == 'a') {
          array_push($pokemon['stats']['breeding']['egg-groups'], strtolower($egggroup->nodeValue) );
        }
      }

      // -- Gender.
      foreach ($n[1]->childNodes[2]->childNodes as $gender) {
        if( !empty($gender->tagName) && $gender->tagName == 'span') {
          $checkGender    = strpos($gender->nodeValue, 'female');
          $gender         = floatval($gender->nodeValue);
          // -- get % for female and male.
          if( $checkGender ) {
            $pokemon['stats']['breeding']['gender']['female'] = $gender;
          } else {
            $pokemon['stats']['breeding']['gender']['male'] = $gender;
          }
        }
      }

      // -- Egg cycles
      $pokemon['stats']['breeding']['egg-cycles'] = $n[2]->childNodes[2]->nodeValue;


      // ---------------------------------------------------------------------------------------
      // --- BASE STATS DATA.
      // ---------------------------------------------------------------------------------------
      $n    = $v[3]->childNodes;
      $pokemon['stats']['base-stats'] = array();

      // -- HP
      $bs  = $n[0]->childNodes;
      $pokemon['stats']['base-stats']['hp'] = array();
      $pokemon['stats']['base-stats']['hp']['idk'] = $bs[2]->nodeValue;
      $pokemon['stats']['base-stats']['hp']['min'] = $bs[6]->nodeValue;
      $pokemon['stats']['base-stats']['hp']['max'] = $bs[8]->nodeValue;

      // -- Attack
      $bs  = $n[1]->childNodes;
      $pokemon['stats']['base-stats']['attack'] = array();
      $pokemon['stats']['base-stats']['attack']['idk'] = $bs[2]->nodeValue;
      $pokemon['stats']['base-stats']['attack']['min'] = $bs[6]->nodeValue;
      $pokemon['stats']['base-stats']['attack']['max'] = $bs[8]->nodeValue;

      // -- Defense
      $bs  = $n[2]->childNodes;
      $pokemon['stats']['base-stats']['defense'] = array();
      $pokemon['stats']['base-stats']['defense']['idk'] = $bs[2]->nodeValue;
      $pokemon['stats']['base-stats']['defense']['min'] = $bs[6]->nodeValue;
      $pokemon['stats']['base-stats']['defense']['max'] = $bs[8]->nodeValue;

      // -- Speed Attack
      $bs  = $n[3]->childNodes;
      $pokemon['stats']['base-stats']['sp-atk'] = array();
      $pokemon['stats']['base-stats']['sp-atk']['idk'] = $bs[2]->nodeValue;
      $pokemon['stats']['base-stats']['sp-atk']['min'] = $bs[6]->nodeValue;
      $pokemon['stats']['base-stats']['sp-atk']['max'] = $bs[8]->nodeValue;

      // -- Speed Defense
      $bs  = $n[4]->childNodes;
      $pokemon['stats']['base-stats']['sp-def'] = array();
      $pokemon['stats']['base-stats']['sp-def']['idk'] = $bs[2]->nodeValue;
      $pokemon['stats']['base-stats']['sp-def']['min'] = $bs[6]->nodeValue;
      $pokemon['stats']['base-stats']['sp-def']['max'] = $bs[8]->nodeValue;

      // -- Speed
      $bs  = $n[5]->childNodes;
      $pokemon['stats']['base-stats']['speed'] = array();
      $pokemon['stats']['base-stats']['speed']['idk'] = $bs[2]->nodeValue;
      $pokemon['stats']['base-stats']['speed']['min'] = $bs[6]->nodeValue;
      $pokemon['stats']['base-stats']['speed']['max'] = $bs[8]->nodeValue;

      // ---------------------------------------------------------------------------------------
      // --- Type defenses TYPE DEFENSES DATA.
      // ---------------------------------------------------------------------------------------
      $pokemon['stats']['type-defenses'] = array();
      $v = $xpath->query('//table[@class="type-table"]//tr');


      for($i = 0; $i < $v->length; $i += 2) {

        // -- get name
        $name   = $v[$i]->childNodes;
        $val    = $v[$i+1]->childNodes;

        for($k = 0; $k < $name->length; $k += 2) {
          $typVal       = utf8_decode($val[$k]->nodeValue);
          $typeName     = strtolower($name[$k]->nodeValue);
          $pokemon['stats']['type-defenses'][$typeName] = $typVal;
        }

      }

      array_push($pokemonsData, $pokemon);
    }

    $resAPI = array(
      'evols'           => $evolsData,
      'moves'           => $movesData,
      'pokemons'        => $pokemonsData
    );

    // --- res.
    return $response->toJsonWithCode($resAPI, 'success');
    // ------------------------------------------------------
    // ------------------------------------------------------
  }

}