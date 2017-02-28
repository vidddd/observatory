<?php/* LLamamos a las correspondientes apis y guardamos en bbdd, se ejctura cada minutos  */opcache_reset();session_start();error_reporting(E_ALL ^ E_NOTICE);require_once "vendor/autoload.php";require_once "secret.php";require_once "classes/class.instagram.php";require_once "classes/force-https.php";require_once "classes/class.twitter.php";$loader = new Twig_Loader_Filesystem('templates/');$twig = new Twig_Environment($loader, array());$db = new MysqliDb (HOST,USER,PASSWORD,DATABASE);$twitterclass = new Twitter();$session = false; $results = array();if($_SESSION['access_token']) {    $session = true;  } else {    echo "<h2>No session</h2>"; die;}if ($_GET['id']) {    $id = $_GET['id']; $more = false;    if($_GET['more'] == 1) {        $more = true;$url = $_GET['url']; $lastid = $_GET['lastid'];    } else $more = false;        $searchs = $db->where('id', $id)->get('searchs');    // Si es busqueda por tag    if($searchs[0]['type'] == '1') {        if($searchs[0]['tag']) {             if($more) $results = Instagram::getMoreResults($url); else $results = Instagram::getMediaTag($searchs[0]['tag'],$_SESSION['access_token']);              SaveMedia($id,$results);             if($more) $results = $twitterclass->getByTag($searchs[0]['tag'],$lastid); else $results = $twitterclass->getByTag($searchs[0]['tag']);             SaveMedia($id,$results);        }        if($searchs[0]['ecoembes']){             if($more) $results = Instagram::getMoreResults($url); else $results = Instagram::getMediaTag($searchs[0]['tag-ecoembes'],$_SESSION['access_token']);              SaveMedia($id,$results,"ecoembes");             if($more) $results = $twitterclass->getByTag($searchs[0]['tag-ecoembes'],$lastid); else $results = $twitterclass->getByTag($searchs[0]['tag-ecoembes']);             SaveMedia($id,$results,"ecoembes");        }        if($searchs[0]['playstation']){             if($more) $results = Instagram::getMoreResults($url); else $results = Instagram::getMediaTag($searchs[0]['tag-playstation'],$_SESSION['access_token']);              SaveMedia($id,$results,"playstation");             if($more) $results = $twitterclass->getByTag($searchs[0]['tag-playstation'],$lastid); else $results = $twitterclass->getByTag($searchs[0]['tag-playstation']);             SaveMedia($id,$results,"playstation");        }        if($searchs[0]['lauder']){             if($more) $results = Instagram::getMoreResults($url); else $results = Instagram::getMediaTag($searchs[0]['tag-lauder'],$_SESSION['access_token']);              SaveMedia($id,$results,"lauder");             if($more) $results = $twitterclass->getByTag($searchs[0]['tag-lauder'],$lastid); else $results = $twitterclass->getByTag($searchs[0]['tag-lauder']);             SaveMedia($id,$results,"lauder");        }        if($searchs[0]['mcdonalds']){            if($more) $results = Instagram::getMoreResults($url); else $results = Instagram::getMediaTag($searchs[0]['tag-mcdonalds'],$_SESSION['access_token']);              SaveMedia($id,$results,"mcdonalds");             if($more) $results = $twitterclass->getByTag($searchs[0]['tag-mcdonalds'],$lastid); else $results = $twitterclass->getByTag($searchs[0]['tag-mcdonalds']);             SaveMedia($id,$results,"mcdonalds");        }        if($searchs[0]['new']){             if($more) $results = Instagram::getMoreResults($url); else $results = Instagram::getMediaTag($searchs[0]['tag-new'],$_SESSION['access_token']);              SaveMedia($id,$results,"new");             if($more) $results = $twitterclass->getByTag($searchs[0]['tag-new'],$lastid); else $results = $twitterclass->getByTag($searchs[0]['tag-new']);             SaveMedia($id,$results,"new");        }    // si es busqueda por username    } else if ($searchs[0]['type'] == '2'){         if($searchs[0]['username']) {             if($more) $results = Instagram::getMoreResults($url); else $results = Instagram::getUserMediaRecent($searchs[0]['username'],$_SESSION['access_token']);              SaveMedia($id,$results);             if($more) $results = $twitterclass->getByUsername($searchs[0]['username'],$lastid); else $results = $twitterclass->getByUsername($searchs[0]['username']);             SaveMedia($id,$results);        }        if($searchs[0]['ecoembes']){            if($more) $results = Instagram::getMoreResults($url); else $results = Instagram::getMediaTag($searchs[0]['username-ecoembes'],$_SESSION['access_token']);              SaveMedia($id,$results,"ecoembes");             if($more) $results = $twitterclass->getByUsername($searchs[0]['username-ecoembes'],$lastid); else $results = $twitterclass->getByTag($searchs[0]['username-ecoembes']);             SaveMedia($id,$results,"ecoembes");        }        if($searchs[0]['playstation']){            $results = Instagram::getMediaTag($searchs[0]['username-playstation'],$_SESSION['access_token']);              SaveMedia($id,$results,"playstation");             $results = $twitterclass->getByTag($searchs[0]['username-playstation']);             SaveMedia($id,$results,"playstation");        }        if($searchs[0]['lauder']){            $results = Instagram::getMediaTag($searchs[0]['username-lauder'],$_SESSION['access_token']);              SaveMedia($id,$results,"lauder");             $results = $twitterclass->getByTag($searchs[0]['username-lauder']);             SaveMedia($id,$results,"lauder");        }        if($searchs[0]['mcdonalds']){            $results = Instagram::getMediaTag($searchs[0]['username-mcdonalds'],$_SESSION['access_token']);              SaveMedia($id,$results,"mcdonalds");             $results = $twitterclass->getByTag($searchs[0]['username-mcdonalds']);             SaveMedia($id,$results,"mcdonalds");        }        if($searchs[0]['new']){            $results = Instagram::getMediaTag($searchs[0]['username-new'],$_SESSION['access_token']);              SaveMedia($id,$results,"new");             $results = $twitterclass->getByTag($searchs[0]['username-new']);             SaveMedia($id,$results,"new");        }    } else {        echo "No data"; die;    }    } else {        echo "Necesitamos un parámetro id"; die;} function saveMedia($id, $results, $client = null){    global $db;       //      print_r($results);    foreach($results as $result) {        $data = Array ("searchid" => $id, "client" => $client, "date" => $db->now(),                       "source" => $result['source'],                       "text" => $result['text'],                        "url" => $result['url'],                       "media_url" => $result['media_url'],                        "video_url" => $result['video_url'],                        "id_source" => $result['id_source'],                       "user_name" => $result['user_name'],                        "user_profile_image_url" => $result['user_profile_image_url'],                        "pagination" => $result['pagination']                );            $idr = $db->insert('medias', $data);            if ($idr){          //  echo 'user was created. Id=' . $id;             } else {            echo 'insert failed: ' . $db->getLastError();            }    }   // echo "Saved Data";}