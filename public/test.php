<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


class Test {

  /**
   * @type('string')
   * 
   */
  public $name = 'toto';
}



$ra   = new ReflectionAnnotatedClass('Test');
$e    = new Test();

echo '<pre>';
print_r($ra);
echo '</pre>';

