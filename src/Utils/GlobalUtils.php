<?php

namespace MDSPokemonApi\Utils;

class GlobalUtils {

  public static function pre($arr) {
    echo '<hr><pre>';
    print_r($arr);
    echo '</pre><hr>';
  }
  public static function jum($val) {
    echo $val.'<br />';
  }
  public static function jume($val) {
    echo '[ERREUR] --- ' . $val . '<br />';
  }

  public static function getPageContent($sUrl) {
    global $sUserAgent;
    $oContext     = stream_context_create(
      array(
        'http' => array(
          'user_agent' => $sUserAgent
        )
      )
    );
    $sContent     = file_get_contents($sUrl, false, $oContext);
    return $sContent;
  }

}