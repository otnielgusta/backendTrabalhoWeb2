<?php

namespace Controller;

use \Controller\Authenticate;
use Dao\DAOHorario;
use Exception;
use Model\Cliente;
use Model\Horario;

class HorarioController
{
    public function marcarHorario(Horario $horario)
    {

        $dao = new DAOHorario();
        $wasReg = $dao->marcaHorario(horario: $horario);
        if ($wasReg) {

            $result['message'] = 'ok';
        } else {
            $result['message'] = 'erro';
        }
        return $result;
    }

    public function listaHorariosMarcados($id, $data)
    {

        try {
            $daoHorario = new DAOHorario();

            $horarios = $daoHorario->buscaHorariosCabelereiros(id: $id, data: $data);
            $response = [
                "horarios" => $horarios
            ];
            return $response;
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
