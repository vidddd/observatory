<?php
set_time_limit(0);
opcache_reset();
session_start();
error_reporting(E_ALL ^ E_NOTICE);
require_once "vendor/autoload.php";
require_once "secret.php";
require_once "classes/class.instagram.php";
require_once "classes/class.twitter.php";

$loader = new Twig_Loader_Filesystem('templates/');
$twig = new Twig_Environment($loader, array());
$db = new MysqliDb (HOST,USER,PASSWORD,DATABASE);
$session = false; $tag = ''; $username=''; $twitter = false; $results = array();
/*
echo "<pre>";
print_r($_SESSION);
echo "</pre>";
*/

if($_SESSION['access_token']) {
    $session = true;  
} else {
    echo "<h2>No session</h2>"; die;
}
if ($_GET['tag']) {
    $tag = $_GET['tag'];
    $results = Instagram::getMediaTag($tag,$_SESSION['access_token']); 
} else if($_GET['user']){
    $username = $_GET['user'];
    $results = Instagram::getUserMediaRecent($username,$_SESSION['access_token']);
}else if($_GET['url']){
    $url = $_GET['url'];
    $results = Instagram::getMoreResults($url);
} else {

   $url = "";
    
}
ob_end_clean();
header("Connection: close\r\n");
header("Content-Encoding: none\r\n");
ignore_user_abort(true); // optional
ob_start();
echo $twig->render('preview-media.phtml', array(
                                         'results' => $results,
                                         'twitter' => $twitter
                                          ));

$size = ob_get_length();
header("Content-Length: $size");
ob_end_flush();     // Strange behaviour, will not work
flush();            // Unless both are called !
ob_end_clean();

// close current session
if (session_id()) session_write_close(); 

$pid=pcntl_fork();

if ($pid) {
  posix_kill(posix_getppid(),SIGKILL);
} 

//ignore_user_abort(false);
//ob_flush();
//flush();
 //exec('kill -9 ' . getmypid());
/*
    if(connection_aborted()){
        // This happens when connection is closed
         mail('davidalvarezcalvo@gmail.com','Connection aborted','Conectiona aborted');
        exit;
    }
 */