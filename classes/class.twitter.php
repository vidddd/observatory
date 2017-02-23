<?php
require_once "session.class.php";
use Abraham\TwitterOAuth\TwitterOAuth; //https://twitteroauth.com/
    
class Twitter{
    
    const CONSUMER_KEY = "LfqyJqd37n8QE1XAqbsAJ1lGW";
    const CONSUMER_SECRET = "OZyJsrouXAL6pjcJwLP2aLjYacTgeb933J0lt512T2yqaJcimZ";
    const ACCESS_TOKEN = "795596035775152129-cRuPOLwtT6rcGPLnGZa0Es9u2EIzLnC";
    const ACCESS_TOKEN_SECRET = "aRY7d5O4VoaSY2cR8tRGq54Yop5uoNVkbKkPi6m2qTkbW";
    
    private $connection;
    private $credentials;
    
    public function __construct()
    { 
        $this->connection = new TwitterOAuth(self::CONSUMER_KEY, self::CONSUMER_SECRET, self::ACCESS_TOKEN, self::ACCESS_TOKEN_SECRET);
        $this->credentials = $this->connection->get("account/verify_credentials");
    }
    
    public function getByTag($tag, $lastid = null){
        $results = array(); $i=0;
        
        if(isset($lastid)){
            $statuses = $this->connection->get("search/tweets", ["q" => "#".$tag, "max_id" => $lastid, 'result_type' => 'recent']);
        } else {
            $statuses = $this->connection->get("search/tweets", ["q" => "#".$tag,'result_type' => 'recent']); 
        }
    
        if ($this->connection->getLastHttpCode() == 200) {
            foreach($statuses as $med){
                foreach($med as $media){ 
                    if(is_object($media)){
                        $results[$i]['source'] = 'twitter';
                        $results[$i]['text'] = $media->text;
                        $results[$i]['id_source'] = $media->id;
                        $results[$i]['url'] = 'https://twitter.com/statuses/'.$media->id;
                        if(isset($media->entities->media)){
                            foreach($media->entities->media as $img){
                              
                              $results[$i]['media_url'] = $img->media_url;
                            }
                        }
                    }
                    if(isset($media->user->name)){
                        $results[$i]['user_name']=$media->user->name;
                        $results[$i]['user_profile_image_url']=$media->user->profile_image_url;
                    }
                    $i++;
                }
            }
        } else {
            // Handle error case
        }
   // echo "<pre>"; print_r($results); echo "</pre>"; die;
      return $results;
     }
     
     public function getByUsername($username, $lastid = null){
                 $results = array(); $i=0;
        
        if(isset($lastid)){
            $statuses = $this->connection->get("search/tweets", ["q" => "@".$username, "max_id" => $lastid,'result_type' => 'popular' ]);
                     
        } else {
            $statuses = $this->connection->get("search/tweets", ["q" => "@".$username,'result_type' => 'popular']); 
        }
    
        if ($this->connection->getLastHttpCode() == 200) {
            foreach($statuses as $med){
                foreach($med as $media){ 
                    if(is_object($media)){
                        $results[$i]['source'] = 'twitter';
                        $results[$i]['text'] = $media->text;
                        $results[$i]['id'] = $media->id;
                        $results[$i]['url'] = 'https://twitter.com/statuses/'.$media->id;
                        if(isset($media->entities->media)){
                            foreach($media->entities->media as $img){
                              
                              $results[$i]['media_url'] = $img->media_url;
                            }
                        }
                    }
                    if(isset($media->user->name)){
                        $results[$i]['user_name']=$media->user->name;
                        $results[$i]['user_profile_image_url']=$media->user->profile_image_url;
                    }
                    $i++;
                }
            }
        } else {
            // Handle error case
        }

      return $results;
     }
}