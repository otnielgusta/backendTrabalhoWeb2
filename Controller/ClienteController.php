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

    public static function cadastro($nome, $email,  $senha){
        try {

            $dao = new DAOCliente();
            $id = uniqid();
            $verify_email = $dao->verificaEmail($email);
            if ($verify_email) {
                return [
                    "message" => "Este E-mail ja está sendo utilizado",
                    "status" => "error"
                ];
            }
            $senha_criptografada = md5($senha);
            $result = $dao->cadastro($id, $nome, $email, $senha_criptografada);
            if (!$result) {
                return [
                    "message" => "Ocorreu um erro ao cadastar o usuário",
                    "status" => "error"
                ];
            }

            return [
                "message" => "ok",
                "status" => "success"
            ];
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function atualizaCliente($id, $email, $nome, $senha)
    {
        try {

            $dao = new DAOCliente();

            $email_igual =  $dao->pegaEmail($id);
            if ($email_igual["email"] === $email) {
                $result = $dao->login(usuario: $email);
                if (!$result) {
                    return [
                        "message" => "Ocorreu um erro ao atualizar usuario",
                        "status" => "error"
                    ];
                }
            } else {

                $verify_email = $dao->verificaEmail($email);
                if ($verify_email) {
                    return [
                        "message" => "Este E-mail ja está sendo utilizado",
                        "status" => "error"
                    ];
                }
            }
<<<<<<< HEAD

            if ($senha == "" || $senha == null) {
                $response = $dao->atualizaClienteSemSenha($id, $email, $nome);
            } else {

                if (!Authenticate::validatePassword(appPassword: $senha, bdPassword: $result['senha'])) {
                    $senha_criptografada = md5($senha);
                } else {

                    $senha_criptografada = $senha;
                }
                $response = $dao->atualizaClienteComSenha($id, $email, $nome, $senha_criptografada);
=======
            $senha_criptografada = md5($senha);
            $wasReg = $dao->cadastro(cliente: $cliente, senha: $senha_criptografada);
            if ($wasReg) {
                $result['message'] = 'ok';
>>>>>>> 31f89bbbf33ad336d47aad16c1ed4e14a768c68d
            }

            if (!$response) {
                return [
                    "message" => "Ocorreu um erro ao atualizar usuario atualização",
                    "status" => "error"
                ];
            }
            return [
                "message" => "ok",
                "status" => "success"
            ];
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function atualizaFoto($id, $foto)
    {
        try {

            $dao = new DAOCliente();

            $response = $dao->atualizaFoto($id, $foto);
            if (!$response) {
                return [
                    "message" => "Ocorreu um erro ao salvar foto",
                    "status" => "error"
                ];
            }
            return [
                "message" => "ok",
                "status" => "success"
            ];
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function getCliente($id)
    {
        try {

            $dao = new DAOCliente();
            $response = $dao->getCliente($id);
            if ($response) {
                return [
                    "status" => "success",
                    "user" => $response->toObjWithoutId()
                ];
            } else {
                return [
                    "status" => "error",
                ];
            }
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
}
