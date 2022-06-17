<?php
namespace Controller;
use \Controller\Env;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use FFI\Exception;

class Authenticate{

    public static function genJWT($payload){
        $key = Env::SECRET_KEY();
        $time = time();
        $expiration = $time ;
        $payload['exp'] = $expiration;
       
        $jwt = JWT::encode($payload, $key, 'HS256');
    
        return $jwt;
    }

    public static function decodeJWT($jwt){
        $key = Env::SECRET_KEY();
        try {
            return JWT::decode($jwt, new Key($key, 'HS256'));

        } catch (\Firebase\JWT\ExpiredException  $e) {
            http_response_code(401);
            return json_encode([
                "msg" => "Token expirado"
            ]);
        }
    }

    public static function validateJWT($jwt){

        $key = Env::SECRET_KEY();
        try {
            return JWT::decode($jwt, new Key($key, 'HS256'));

        } catch (Exception $e) {
            echo $e; 
        }
    }

    public static function validatePassword($appPassword, $bdPassword){
        if (md5($appPassword) == $bdPassword) {
            return true;
        }
        return false;
    }
}
?>