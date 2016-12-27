<?php

error_reporting(E_ALL ^ E_NOTICE);
require_once "secret.php";
require_once "vendor/autoload.php";
require_once "classes/class.instagram.php";
require_once "vendor/joshcam/mysqli-database-class/MysqliDb.php";

$db = new MysqliDb (HOST,USER,PASSWORD,DATABASE);
//$dbperros = new MysqliDb (HOST_PERROS,USER_PERROS,PASSWORD_PERROS,DATABASE_PERROS);
$dbperros = new MysqliDb (HOST_PERROS,USER_PERROS,PASSWORD_PERROS,DATABASE_PERROS);
// sacamos el access_token para conectar con instagram
$db->where ("id", 12);
$search = $db->getOne ("searchs");
$access_token = $search['access_token']; $tag = "elpoderdelabrazo";


$results = Instagram::getMediaTag($tag,$access_token);

// 22 de noviembre 2016
$datepost = "1479810951";

// Queremos saber el id del ultimo instagram que hemos guardado
$params = Array(2);
$resl = $dbperros->rawQuery("SELECT id,idIns FROM instagram WHERE type = ? ORDER BY idIns DESC LIMIT 1", $params);
if($resl) $lastid = $resl[0]['idIns'];

// Recorremos los ultimmos post de instagram con el hastag #elpoderdelabrazo hasta llegar hasta el ultimo id insertado
$ultimo = false;
if($results['data']){
    foreach ($results['data'] as $media){
        $id = $media['id'];
        // no hay post nuevos en instagram
        if ($lastid === $id) {
            die;
        }
        // Si solicado posterior mente a la fecha
        if($media['type'] == 'image'){
            // miramos que el id de instagram, que no la hemos guardado antes
            $dbperros->where ("idIns", $id);
            $per = $dbperros->getOne("instagram");
            // si no esta esta foto la guardamos en bbdd
            if(empty($per)){
                $data = Array (
                    'idIns' => $id,
                    'type' => 2,
                    'created_time' => $dbperros->now(),
                    'link' => $media['link'],
                    'image_sta' => $media['images']['low_resolution']['url'],
                    'image_thu' => $media['images']['thumbnail']['url'],
                    'username' => $media['user']['username'],
                    'text' => $media['caption']['text']
                );

                $id = $dbperros->insert ('instagram', $data);
            // si ya esta no hacemos nada
            } else {
                
            }
                
            }
    }
}
