<?php

namespace Controller;

use \Controller\Authenticate;
use Dao\DAOCabelereiro;
use Dao\DAOHorario;
use Exception;
use Model\Cliente;

class CabelereiroController
{
    public function login($email, $senha)
    {
        try {

            $dao = new DAOCabelereiro();
            $daoHorario = new DAOHorario();

            $result = $dao->login(usuario: $email);
            if (!$result) {
                return [];
            }

            if (Authenticate::validatePassword(appPassword: $senha, bdPassword: $result['senha'])) {
                $cliente = $dao->getCabelereiro(id: $result['id']);

                $payload = [
                    "id" => $result['id'],
                ];
                $token = Authenticate::genJWT(payload: $payload);
                $response = [
                    "token" => $token,
                    "user" => $cliente->toObjWithoutId(),
                ];
                return $response;
            } else {
                return [];
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function cadastro($nome, $email, $senha, $jornada_semanal, $jornada_diaria, $horario_string, $dias_string)
    {
        try {

            $dao = new DAOCabelereiro();
            $dao_horario = new DAOHorario();
            $id = uniqid();
            $verify_email = $dao->verificaEmail($email);
            if ($verify_email) {
                return [
                    "message" => "Este E-mail ja está sendo utilizado",
                    "status" => "error"
                ];
            }
            $senha_criptografada = md5($senha);
            $result = $dao->cadastro($id, $nome, $email, $senha_criptografada, $horario_string, $dias_string);
            if (!$result) {
                return [
                    "message" => "Ocorreu um erro ao cadastar o usuário",
                    "status" => "error"
                ];
            }

            $js = $dao_horario->criaJornadaSemanal($id, $jornada_semanal);
            if (!$js) {

                return [
                    "message" => "Ocorreu um erro ao cadastar o a jornada semanal",
                    "status" => "error"
                ];
            }

            $jd = $dao_horario->criaJornadaDiaria($id, $jornada_diaria, $jornada_semanal);
            if (!$jd) {
                return [
                    "message" => "Ocorreu um erro ao cadastar o a jornada diária",
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

    public function atualizaCabelereiro($id, $email, $nome, $senha)
    {
        try {

            $dao = new DAOCabelereiro();

            $email_igual =  $dao->pegaEmail($id);
            if ($email_igual["email"] === $email) {
                $result = $dao->login(usuario: $email);
                if (!$result) {
                    return [
                        "message" => "Ocorreu um erro ao atualizar usuario login",
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



            if ($senha == "" || $senha == null) {
                $response = $dao->atualizaCabelereiroSemSenha($id, $email, $nome);
            } else {

                if (!Authenticate::validatePassword(appPassword: $senha, bdPassword: $result['senha'])) {
                    $senha_criptografada = md5($senha);
                } else {

                    $senha_criptografada = $senha;
                }
                $response = $dao->atualizaCabelereiroComSenha($id, $email, $nome, $senha_criptografada);
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

            $dao = new DAOCabelereiro();

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

    public function listarCabelereiros()
    {
        try {

            $dao = new DAOCabelereiro();

            $response = $dao->listaCabelereiros();
            return $response;
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    public function buscaHorarios($id, $data)
    {
        try {

            $dao = new DAOCabelereiro();

            $response = $dao->buscaHorarios($id, $data);
            return $response;
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    public function getCabelereiro($id)
    {
        try {

            $dao = new DAOCabelereiro();

            $response = $dao->getCabelereiro($id);
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
