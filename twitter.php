<?php
require "vendor/autoload.php";
require_once "classes/class.twitter.php";

$twitter = new Twitter();
$tag = "tuesday";
$res = $twitter->getByTag($tag);

 echo "<pre>"; print_r($res); echo "</pre>";