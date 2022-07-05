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

            $senha_criptografada = md5($senha);
            $result = $dao->cadastro($id, $nome, $email, $senha_criptografada, $horario_string, $dias_string);
            if (!$result) {
                return false;
            }

            $js = $dao_horario->criaJornadaSemanal($id, $jornada_semanal);
            if (!$js) {
                return false;
            }

            $jd = $dao_horario->criaJornadaDiaria($id, $jornada_diaria, $jornada_semanal);
            if (!$jd) {
                return false;
            }
            return true;
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
}
