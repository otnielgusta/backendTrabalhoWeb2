<?php

namespace Model;

class Horario{
    public $id;
    public $data;
    public $cliente_id;
    public $cabelereiro_id;
    public $horario_cabelereiro;

    public function todosCamposPreenchidos(){
        if (
            $this->id && 
            $this->data && 
            $this->cliente_id && 
            $this->cabelereiro_id && 
            $this->horario_cabelereiro
            ) {
            return true;
        }
        return false;
    }

    public function toJson(){
        $json = [
            "id" =>  $this->id,
            "data" => $this->data,
            "cliente" => $this->cliente_id,
            "cabelereiro" => $this->cabelereiro_id,
            "horario_cabelereiro" =>   $this->horario_cabelereiro
        ];

        return json_encode($json);
    }
}

?>