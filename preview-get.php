<?php/* Coje los datos de bbdd y muesta en pantalla */opcache_reset();session_start();error_reporting(E_ALL);require_once "vendor/autoload.php";require_once "secret.php";require_once "classes/class.instagram.php";require_once "classes/force-https.php";require_once "classes/class.twitter.php";$loader = new Twig_Loader_Filesystem('templates/');$twig = new Twig_Environment($loader, array());$db = new MysqliDb (HOST,USER,PASSWORD,DATABASE);   $twitterclass = new Twitter();$session = false;  $twitter = false; $results = array();if($_SESSION['access_token']) {    $session = true;  } else {    echo "<h2>No session</h2>"; die;}  if ($_GET['id']) {    global $db;    $db->orderBy("date","desc");    $db->where("searchid", $_GET['id']);    $results = $db->withTotalCount()->get('medias', Array (0, 90));    shuffle($results);      $results = array_slice($results,0, 30);        //print_r($results);    echo $twig->render('preview-media.phtml', array(                                         'results' => $results,                                          ));} else {    echo "Necesitamos un parámetro id"; die;}?>