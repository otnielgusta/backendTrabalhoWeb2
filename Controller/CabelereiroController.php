<?php

namespace Controller;
use \Controller\Authenticate;
use Dao\DAOCabelereiro;
use Exception;
use Model\Cliente;

class CabelereiroController
{
    public function listarCabelereiros(){
        try {
            
            $dao = new DAOCabelereiro();

            $response = $dao->listaCabelereiros();
            return $response;
            
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    public function buscaHorarios($id, $data){
        try {
            
            $dao = new DAOCabelereiro();

            $response = $dao->buscaHorarios($id, $data);
            return $response;
            
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
}