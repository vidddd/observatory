<?php
opcache_reset();
error_reporting(E_ALL ^ E_NOTICE);
ini_set("session.gc_maxlifetime", 24000);
require_once "vendor/autoload.php";
require_once "secret.php";
require_once "classes/class.instagram.php";
require_once "classes/class.twitter.php";

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$config['displayErrorDetails'] = true; $config['addContentLengthHeader'] = false;
$db = new MysqliDb (HOST,USER,PASSWORD,DATABASE);
$instagram = new Instagram();
$twitterclass = new Twitter();
$app = new \Slim\App(["settings" => $config]);
$app->add(new \RKA\SessionMiddleware(['name' => 'SessionObservatory']));
$container = $app->getContainer();
$container['view'] = new \Slim\Views\PhpRenderer("templates/");
$session = new \RKA\Session();
$container['access_token'] = ''; $container['user'] = '';
$container['session'] = $session;

// PAGINA DE INICIO
$app->get('/', function (Request $request, Response $response) {
    $login = false; global $db;
    
    $db->where ("id", "1");
    $ses = $db->getOne("sessions");
    $access_token = $ses['access_token']; 
    $this->session->set('username', $ses['uname']); 
    $this->session->set('profile_picture', $ses['upic']); 
    
    if($access_token){
       $login = true;
       $this->session->set('access_token', $ses['access_token']);  
       if($this->session->__isset('access_token')){
            $login = true;
        }
    }
    return $this->view->render($response, "index.phtml", [ 'urllogin' => URL_LOGIN, 
                                                           'login' => $login, 
                                                           'username' => $this->session->get('username'),
                                                           'profile_picture' => $this->session->get('profile_picture')]);
});
// LOGIN 
$app->get('/instalogin', function (Request $request, Response $response) {
   $url = "https://api.instagram.com/oauth/authorize/?client_id=".CLIENT_ID."&redirect_uri=".REDIRECT_URL."&response_type=code&scope=public_content";
   // envia cabeceras al login de instagram    
   $newres = $response->withStatus(302)->withHeader('Location', $url);
   return $newres;
});
$app->get('/logout', function (Request $request, Response $response) {
    \RKA\Session::destroy();
    $login = false;
    $results = Instagram::logout();
    return $this->view->render($response, "index.phtml", [ 'login' => $login, 'results' => $results ]);
});
// Despues de loguearme en Instagram en devuelve aqui con el codigo  http://your-redirect-uri?code=CODE
$app->get('/dos', function (Request $request, Response $response, $instagram) {
   // comprobamos que no ha sido error http://your-redirect-uri?error=access_denied&error_reason=user_denied&error_description=The+user+denied+your+request

    if($request->getAttribute('error')){
        /*  echo "<pre>";
    print_r($request);
    echo "</pre>"; die;*/
        
       // $error = true; 
      // $response = $this->view->render($response, "index.phtml", ["error" => $request->getAttribute('error_reason'), 'username' => $this->session->username, 'profile_picture' => $this->session->get('profile_picture') ]);
   }

   $code = $request->getParam('code'); 
   //Obtenemos el access token a partir de code proporcionado por instagram depsues del login
   $data = Instagram::getInstagramAccessToken(CLIENT_ID, CLIENT_SECRET, REDIRECT_URL, $code);
   if($data->code == '400'){ // Error 
       $response = $this->view->render($response, "index.phtml", ["error" => true, 'error_type' => $data->error_type, 'error_reason' => $data->error_message,  'username' => '', 'profile_picture' => '' ]);
        $login = false;  $access_token = '';
   } else {
       // guardamos en session el access token y los datos del usuario logueado
       $this->session->set('access_token', $data->access_token); $login = true; $access_token = $this->access_token;
       $user = Instagram::getUserSelf($this->session->get('access_token'));
       $this->session->set('uid', $user['data']['id']);
       $this->session->set('username', $user['data']['username']);
       $this->session->set('profile_picture', $user['data']['profile_picture']);
       $response = $this->view->render($response, "index.phtml",array('access_token' => $access_token, 
                                                                  'login' => $login, 
                                                                  'username' => $this->session->get('username'), 'profile_picture' => $this->session->get('profile_picture')));
   }

   return $response;
});

// PULSAMOS GET POSTS !!!
$app->post('/busca', function (Request $request, Response $response) {
    $results = array(); global $twitterclass; global $db; global $word;
    $tag = $request->getParam('tag'); $username = $request->getParam('username'); $twitter = $request->getParam('twitter');
    if($this->session->__isset('access_token')){     $login = true; 
        $access_token = $this->session->get('access_token');
    } else {
          return $response->withStatus(302)->withHeader('Location', '/');  
    } 
   
    
    if($tag != ''){ 
       $results = Instagram::getMediaTag($tag,$access_token); 
       if($twitter){$results = array_merge($twitterclass->getByTag($tag),$results);
                     $this->session->set('twitter', $twitter);}         
       $word = $tag; $type = 1;           
       } 
    if($username !='') {
        $results = Instagram::getUserMediaRecent($username,$access_token);
        $word = $username; $type = 2;
    }
    // Guardamos en bbdd la busquda
    $data = Array ("type" => $type,
               "word" => $tag.$username,
               "uid" => $this->session->get('uid'),
               "username" => $this->session->get('username'),
               "date" => $db->now(),
               "access_token" => $access_token
            );
    $db->insert('searchs', $data);
    
    $response = $this->view->render($response, "index.phtml", array('access_token' => $access_token, 
                                                                        'login' => $login, 
                                                                        'results' => $results,
                                                                        'twitter'  => $twitter,
                                                                        'tag' => $tag,'user'=> $username, 'username' => $this->session->get('username'), 'profile_picture' => $this->session->get('profile_picture')));
    return $response;
});
// PULSAMOS MORE RESULTS !!!  - AJAX
$app->post('/morea', function (Request $request, Response $response) {
   global $twitterclass; 
   $url = $request->getParam('url'); 
   $lastid = $request->getParam('lastid'); 
   $tag = $request->getParam('tag'); 
   $results = Instagram::getMoreResults($url);
   if($lastid){
        $results = array_merge($twitterclass->getByTag($tag,$lastid),$results);
       // $results = $twitterclass->getByTag($tag,$lastid);
        $twitter = true;
   }
   return $this->view->render($response, "media.phtml", array('results' => $results, 'twitter' => $twitter));
 
   
   });
$app->get('/screencast', function (Request $request, Response $response) {
    return $this->view->render($response, "screencast.phtml");
});
$app->get('/mihastag/{tag}', function (Request $request, Response $response) {
    $tag = $request->getAttribute('tag'); $results = array();
    
    if($this->session->__isset('access_token')){     $login = true; 
        $access_token = $this->session->get('access_token');
        
    } else {
          return $response->withStatus(302)->withHeader('Location', '/');  
    }
    return $this->view->render($response, "mihastag.phtml", array('results' => $results,
                                                                  'tag' => $tag,
                                                                  'profile_picture' => $this->session->get('profile_picture')));
});
// GET MITAG !!!  - AJAX
$app->get('/getbytag/{tag}', function (Request $request, Response $response) {
   $tag = $request->getAttribute('tag'); global $twitterclass; $twitter = false;
   
    if($this->session->__isset('access_token')){     $login = true; 
        $access_token = $this->session->get('access_token');
    } else {
          return $response->withStatus(302)->withHeader('Location', '/');  
    } 
    $results = Instagram::getMediaTag($tag,$access_token);
    
    if($this->session->get('twitter')){
        $results = array_merge($twitterclass->getByTag($tag),$results);
        $twitter = true;
   }

   return $this->view->render($response, "mihastag-media.phtml", array('results' => $results, 'twitter' => $twitter));
});
// GET MORE !!!  - AJAX
$app->post('/mihastag-more', function (Request $request, Response $response) {
  
  try {
    $url = $request->getParam('url');  global $twitterclass;
    // $lastid = $request->getParam('lastid'); 
    $results = Instagram::getMoreResults($url);
    /* if($this->session->get('twitter')){
         $results = array_merge($twitterclass->getByTag($tag,$lastid),$results);
         $twitter = true;
    }*/

   return $this->view->render($response, "mihastag-media.phtml", array('results' => $results, 'twitter' => $twitter));
   
   
  } catch (Exception $e) {
   // $app->response()->status(400);
   // $app->response()->header('X-Status-Reason', $e->getMessage());
         session_write_close();
      die;
  }
});



$app->get('/miuser/{user}', function (Request $request, Response $response) {
    $user = $request->getAttribute('user'); $results = array();
    
    if($this->session->__isset('access_token')){     $login = true; 
        $access_token = $this->session->get('access_token');
    } else {
          return $response->withStatus(302)->withHeader('Location', '/');  
    } 
    return $this->view->render($response, "mihastag.phtml", array('results' => $results,
                                                                  'user' => $user,
                                                                  'profile_picture' => $this->session->get('profile_picture')));
});
// GET USER !!!  - AJAX
$app->get('/getbyuser/{user}', function (Request $request, Response $response) {
   $user = $request->getAttribute('user');
    if($this->session->__isset('access_token')){     $login = true; 
        $access_token = $this->session->get('access_token');
    } else {
          return $response->withStatus(302)->withHeader('Location', '/');  
    } 
    $results = Instagram::getUserMediaRecent($user,$access_token);
    
   return $this->view->render($response, "mihastag-media.phtml", array('results' => $results));
}); 
 
$app->run();
session_write_close();
