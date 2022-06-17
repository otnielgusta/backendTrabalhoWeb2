<?php

namespace Controller;
use \Controller\Authenticate;
use Dao\DAOCliente;
use Exception;
use Model\Cliente;

class ClienteController
{

    public function login($email, $senha)
    {
            try{

            $dao = new DAOCliente();

            $result = $dao->login(usuario: $email);
                if (!$result) {
                    return [];
                }

            if (Authenticate::validatePassword(appPassword: $senha, bdPassword: $result['senha'])) {
                $cliente = $dao->getCliente(id: $result['id']);
                $payload = [
                    "id" => $result['id'],
                ];
                $token = Authenticate::genJWT(payload: $payload);
                $response = [
                    "token" => $token,
                    "user" => $cliente->toObjWithoutId()
                ];
                return $response;
            } else {
               return [];
            }
        }
        catch(\Throwable $th){
            return $th;
        }

       
        
    }

    public static function cadastro(Cliente $cliente, string $senha){
        try {
            $dao = new DAOCliente();

            $verifyEmail = $dao->verificaEmail(email: $cliente->email);

            if ($verifyEmail) {
                $result['message'] = 'Este E-mail ja está sendo utilizado';
                return $result; 
            }
            $wasReg = $dao->cadastro(cliente: $cliente, senha: $senha);
            if ($wasReg) {
                $result['message'] = 'ok';
            }
            return $result;
        } catch (\Throwable $th) {
            return $th;
        }
    }
}
