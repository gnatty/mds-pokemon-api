<?php

namespace MDSPokemonApi\Utils;

use MDSPokemonApi\Exception\MysqlPdoUtilsException;

class MysqlPdoUtils {

  private $host;
  private $port;
  private $username;
  private $password;
  private $database;
  private $_ob;

  /**
  * @param array $config
  */
  public function __construct (array $config)
  {
    $configKeys   = array('host', 'port', 'username', 'password', 'database');
    $checkKeys    = array_keys($config);
    if( !empty( array_diff($configKeys, $checkKeys) ) ) {
      throw new MysqlPdoUtilsException('[error] wrong config');
    }
    $this->init($config);
  }

  /**
  * @param array $config
  */
  private function init(array $config)
  {
    $this->host      =  $config['host'];
    $this->port      =  $config['port'];
    $this->username  =  $config['username'];
    $this->password  =  $config['password'];
    $this->database  =  $config['database'];
    try {
      $this->_ob =  new \PDO( 
        'mysql:host='. $this->host . ';port=' . $this->port . ';dbname=' . $this->database,
        $this->username, $this->password
      );
      $this->_ob->exec("SET CHARACTER SET utf8");
    } catch(\Exception $e) {
      $this->_ob = null;
      throw new MysqlPdoUtilsException('[error] #' . $e->getCode() . ' - ' . $e->getMessage() );
    }
  }

  /**
  * @return PDO
  */
  public function ob(): \PDO 
  {
    return $this->_ob;
  }

  public function lastInsertId() {
    return $this->_ob->lastInsertId();
  }

  public function isError($req) {
    if( !empty($req->errorInfo()[1]) ) {
      return true;
    }
    return false;
  }

  public function errorInfo($req) {
    return $req->errorInfo();
  }
  
}