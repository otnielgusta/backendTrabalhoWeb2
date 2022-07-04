<?php
namespace Controller;
use \Controller\Env;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use FFI\Exception;
use Throwable;

class Authenticate{

    public static function genJWT($payload){
        $key = "otniellindo";
        $time = time();
        $expiration = $time + 60 * 60;
        $payload['exp'] = $expiration;
       
        $jwt = JWT::encode($payload, $key, 'HS256');
    
        return $jwt;
    }

    public function decodeJWT($jwt){
        $key = "otniellindo";
        try {

            $token = JWT::decode($jwt, new Key($key, 'HS256'));
            return $token;

        } catch (Throwable $e) {
            return $e; 
        }
        catch (\Firebase\JWT\ExpiredException $e) {
            return $e;
            
        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            return $e;
        }
    }

    public static function validateJWT($jwt){

        $key = "otniellindo";
        try {
            JWT::decode($jwt, new Key($key, 'HS256'));
            return true;

        } catch (Throwable $e) {
            return $e; 
        }
        catch (\Firebase\JWT\ExpiredException $e) {
            return $e;
            
        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            return $e;
        } catch (\Firebase\JWT\JWT $e) {
            return $e;
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