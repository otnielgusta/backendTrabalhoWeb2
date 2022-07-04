<?php
namespace Dao;
use Dao\Conexao;
use Model\Cliente;
use PDO;

class DAOCliente{

    public function cadastro($cliente, $senha){
        $pst = Conexao::getPreparedStatement('insert into cliente values(?, ?, ?, ?, ?);');
        $pst -> bindValue(1, $cliente->id);
        $pst -> bindValue(2, $cliente->nome);
        $pst -> bindValue(3, $cliente->email);
        $pst -> bindValue(4, $senha);
        $pst -> bindValue(5, $cliente->foto);
        $resultado = $pst->execute();
        return $resultado;
    }

    public function verificaEmail($email){
        $pst = Conexao::getPreparedStatement('select count(id) as count from cliente where email = ?;');
        $pst -> bindValue(1, $email);
        $pst->execute();
        $usuario = $pst->fetch(PDO::FETCH_ASSOC);
        return $usuario['count'] > 0;
    }
   
    public function login($usuario){
        $pst = Conexao::getPreparedStatement('select id, senha from cliente where email = ?;');
        $pst -> bindValue(1, $usuario);
        $pst->execute();
        $usuario = $pst->fetch(PDO::FETCH_ASSOC);
        return $usuario;
    }
    
    public function getCliente($id) : Cliente{
        $pst = Conexao::getPreparedStatement('select id, nome, email, foto from cliente where id = ?;');
        $pst -> bindValue(1, $id);
        $pst->execute();
        $result = $pst->fetch(PDO::FETCH_ASSOC);

        $cliente = new Cliente();
        $cliente->id = $result['id'];
        $cliente->nome = $result['nome'];
        $cliente->email = $result['email'];
        $cliente->foto = $result['foto'];
        return $cliente;
    }
}

    
    

?>