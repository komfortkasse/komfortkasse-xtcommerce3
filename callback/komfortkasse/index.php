<?php 
/**
 * Komfortkasse
 * routing
 *
 * @version 1.0.1-xct3
 */

ini_set('default_charset', 'utf-8');

//  error_reporting(E_ALL);
//  ini_set('display_errors', '1');

require_once ('../../includes/application_top_callback.php');
include_once 'Komfortkasse.php';


$action = Komfortkasse_Config::getRequestParameter('action');

$kk = new Komfortkasse();
$kk->$action();
//call_user_method($action, );

?>