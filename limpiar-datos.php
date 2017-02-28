<?php
//opcache_reset();
session_start();
error_reporting(E_ALL ^ E_NOTICE);
require_once "vendor/autoload.php";
require_once "secret.php";

$db = new MysqliDb (HOST,USER,PASSWORD,DATABASE);


if($_SESSION['access_token']) {
    $session = true;  
    // limpia despues de oy a las 12
    $hora = date('H')-1;
    $dat = date('Y-m-d '.$hora.':00:00');
    $db->where('date',$dat,"<=");
    if($db->delete('medias')) echo 'successfully deleted';
    
}  else {
    die;
}
