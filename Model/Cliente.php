<?php
namespace Model;

class Cliente{
    public $id;
    public $nome;
    public $email;
    public $foto;

    public function toJsonWithId(){
       $obj = [
            "id" => $this->id,
            "nome" => $this->nome,
            "email" => $this->email,
            "foto" => $this->foto
        ];

    return json_encode($obj);

    }
    public function toObjWithoutId(){
        $obj = [
             "nome" => $this->nome,
             "email" => $this->email,
             "foto" => $this->foto
         ];
 
     return $obj;
 
     }
}
?>