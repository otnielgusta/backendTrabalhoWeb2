<?php
namespace Model;

use Iterator;

class Cabelereiro{
    public $id;
    public $nome;
    public $email;
    public $foto;
    public $horario;
    public $dias;

    public function toJsonWithId(){
       $obj = [
            "id" => $this->id,
            "nome" => $this->nome,
            "email" => $this->email,
            "foto" => $this->foto,
            "horario" => $this->horario,
            "dias" => $this->dias
        ];

    return json_encode($obj);

    }

    public function setCabelereiro($cabelereiro){
        $this->id = $cabelereiro['id'];
        $this->nome = $cabelereiro['nome'];
        $this->email = $cabelereiro['email'];
        $this->foto = $cabelereiro['foto'];
        $this->horario = $cabelereiro['horario_string'];
        $this->dias = $cabelereiro['dias_string'];

    }
    public function setCabelereiros($cabelereiros){
        $lista = [];
        foreach ($cabelereiros as $key => $value) {
            $cabelereiro = new Cabelereiro();
            $cabelereiro->setCabelereiro(cabelereiro: $value);
            array_push($lista, $cabelereiro);
        }
       return $lista;

    }
    public function toObjWithoutId(){
        $obj = [
             "nome" => $this->nome,
             "email" => $this->email,
             "foto" => $this->foto,
             "horario" => $this->horario,
             "dias" => $this->dias
         ];
 
     return json_encode($obj);
 
     }
}