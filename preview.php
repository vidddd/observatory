<?php
//opcache_reset();
session_start();
error_reporting(E_ALL ^ E_NOTICE);
require_once "vendor/autoload.php";
require_once "secret.php";
require_once "classes/class.instagram.php";
require_once "classes/class.twitter.php";
$loader = new Twig_Loader_Filesystem('templates/');
$twig = new Twig_Environment($loader, array());
$db = new MysqliDb (HOST,USER,PASSWORD,DATABASE);
$session = false; $tag = ''; $username = '';

/*
if ($_GET['tag']) {
    $param = "#".$_GET['tag']; $tag = $_GET['tag'];
} else if($_GET['username']){
    $param = "@".$_GET['username']; $username = $_GET['username'];
} else {
    echo "Necesitamos un parámetro tag o username"; die;
    
}*/
if ($_GET['id']) {
    
} else {
    echo "Necesitamos un parámetro id"; die;
    
}
 
$search = $db->where('id', $_GET['id'])->get('searchs');

 $pe = $search[0]['phrase'];

if($_SESSION['access_token']) {
    $session = true;  
} 
echo $twig->render('preview.phtml', array(
                                         'id' => $id,
                                         'pe' => $pe,
                                         'tag' => $tag,'username' => $username,
                                         'session' => $session,
                                         'profile_picture' => $_SESSION['profile_picture'],
                                          ));
 