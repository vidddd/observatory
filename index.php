<?php
opcache_reset();
error_reporting(E_ALL ^ E_NOTICE);
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

$container['access_token'] = ''; $container['user'] = '';
//$container['session'] = $session;


// PAGINA DE INICIO
$app->get('/', function (Request $request, Response $response) {
    $login = false; global $db;
    $this->session = new \RKA\Session();
    $db->orderBy("date", "desc");$searchs = $db->get('searchs');
    
    if($this->session->__isset('access_token')){
       $login = true;
       return $this->view->render($response, "index.phtml", [ 'urllogin' => URL_LOGIN, 'error' => false, 'is_error' => false,
                                    
                                                           'searchs' => $searchs, 
                                                           'username' => $this->session->get('username'),
                                                           'profile_picture' => $this->session->get('profile_picture')]);
    }
     else {
         return $this->view->render($response, "login.phtml", [ 'url' => URL ,'error' => true ]);
    }
});




// ACCEDER
$app->post('/', function (Request $request, Response $response) {
    $usuario = $request->getParam('usuario');$pass = $request->getParam('pass');
    global $db;
    $this->session = new \RKA\Session();
    if($usuario == USUARIO && $pass == PASS){
           $db->where ("id", "1");
            $ses = $db->getOne("sessions");
            $this->session->set('access_token',$ses['access_token']); 
            $this->session->set('username', $ses['uname']); 
            $this->session->set('profile_picture', $ses['upic']); 
          return $response->withStatus(302)->withHeader('Location', '/');
    } else {
         return $this->view->render($response, "login.phtml", [ 'error' => true, "url" => URL ]);
    }
});
// borrar
$app->get('/delete', function (Request $request, Response $response) {
     global $db; $id = $request->getParam('id');
    $this->session = new \RKA\Session();
    if($this->session->__isset('access_token')){
        $db->where('id', $id);
        if($db->delete('searchs')) echo 'searchs successfully deleted';
        $db->where('searchid', $id);
        if($db->delete('medias')) echo 'medias successfully deleted';
     
        return $response->withStatus(302)->withHeader('Location', '/');  
    }
     else {
         return $this->view->render($response, "login.phtml", [ 'url' => URL ,'error' => true ]);
    }
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
    // $results = Instagram::logout();
    return $this->view->render($response, "login.phtml", [ 'url' => URL ]);
});
// Despues de loguearme en Instagram en devuelve aqui con el codigo  http://your-redirect-uri?code=CODE
$app->get('/dos', function (Request $request, Response $response, $instagram) {
   // comprobamos que no ha sido error http://your-redirect-uri?error=access_denied&error_reason=user_denied&error_description=The+user+denied+your+request

    if($request->getAttribute('error')){
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
    global $twitterclass; global $db; $this->session = new \RKA\Session();
    $tag = $request->getParam('tag');  $username = $request->getParam('username'); $type = $request->getParam('type');
    $ecoembes = $request->getParam('ecoembes'); $tag_ecoembes = $request->getParam('tag-ecoembes'); $username_ecoembes = $request->getParam('username-ecoembes');$playstation = $request->getParam('playstation'); $tag_playstation = $request->getParam('tag-playstation'); $username_playstation = $request->getParam('username-playstation'); $lauder = $request->getParam('lauder'); $tag_lauder = $request->getParam('tag-lauder'); $username_lauder = $request->getParam('username-lauder'); $mcdonalds = $request->getParam('mcdonalds'); $tag_mcdonalds = $request->getParam('tag-mcdonalds'); $username_mcdonalds = $request->getParam('username-mcdonalds'); $new = $request->getParam('new'); $tag_new = $request->getParam('tag-new'); $username_new = $request->getParam('username-new');
    
    
    if($this->session->__isset('access_token')){     
       $login = true; $phrase = '';
       $access_token = $this->session->get('access_token');
    } else {
       return $response->withStatus(302)->withHeader('Location', URL);  
    }
    if($tag) $phrase .= "#".$tag." ";if($username) $phrase .= "@".$username." " ;if($tag_ecoembes) $phrase .= "#".$tag_ecoembes." ";if($username_ecoembes) $phrase .= "@".$username_ecoembes." "; if($tag_playstation) $phrase .= "#".$tag_playstation." ";if($username_playstation) $phrase .= "@".$username_playstation." ";  if($tag_lauder) $phrase .= "#".$tag_lauder." ";if($username_lauder) $phrase .= "@".$username_lauder." "; if($tag_mcdonalds) $phrase .= "#".$tag_mcdonalds." ";if($username_mcdonalds) $phrase .= "@".$username_mcdonalds." "; if($tag_new) $phrase .= "#".$tag_new." ";if($username_new) $phrase .= "@".$username_new." ";
    // Guardamos en bbdd la busquda
    $data = Array ("date" => $db->now(), "type" => $type, "phrase" => $phrase,        "tag" => $tag, "username" => $username,
               "ecoembes" => $ecoembes, "tag-ecoembes" => $tag_ecoembes, "username-ecoembes" => $username_ecoembes,
               "playstation" => $playstation, "tag-playstation" => $tag_playstation, "username-playstation" => $username_playstation,
               "lauder" => $lauder, "tag-lauder" => $tag_lauder, "username-lauder" => $username_lauder,
               "mcdonalds" => $mcdonalds, "tag-mcdonalds" => $tag_mcdonalds, "username-mcdonalds" => $username_mcdonalds,
               "new" => $new, "tag-new" => $tag_new, "username-new" => $username_new,
            );
    $id = $db->insert('searchs', $data);

    $db->orderBy("date", "desc");
    $searchs = $db->get('searchs');

    return $this->view->render($response, "index.phtml", array(   'searchs' => $searchs,
                                                                        'tag' => $tag,'user'=> $username, 
                                                                        'username' => $this->session->get('username'),
                                                                        'profile_picture' => $this->session->get('profile_picture')));

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