<?php
namespace Dao;
use Dao\Conexao;
use Model\Cliente;
use PDO;

class DAOCliente{

    public function cadastro($id, $nome, $email, $senha){
        $pst = Conexao::getPreparedStatement('insert into cliente values(?, ?, ?, ?, ?);');
        $pst -> bindValue(1, $id);
        $pst -> bindValue(2, $nome);
        $pst -> bindValue(3, $email);
        $pst -> bindValue(4, $senha);
        $pst -> bindValue(5, "");
        $resultado = $pst->execute();
        return $resultado;
    }

    public function atualizaFoto($id, $foto){
        $pst = Conexao::getPreparedStatement("
        update cliente set foto = ? where id = ?
         ");
        $pst -> bindValue(1, $foto);
        $pst -> bindValue(2, $id);
        $resultado = $pst->execute();
        return $resultado;
    }

    public function atualizaClienteComSenha($id,  $email, $nome, $senha){
        $pst = Conexao::getPreparedStatement("
        update cliente set nome = ?, email = ?, senha = ? where id = ?
         ");
        $pst -> bindValue(1, $nome);
        $pst -> bindValue(2, $email);
        $pst -> bindValue(3, $senha);
        $pst -> bindValue(4, $id);
        $resultado = $pst->execute();
        return $resultado;
    }

    public function atualizaClienteSemSenha($id, $email, $nome,){
        $pst = Conexao::getPreparedStatement("
        update cliente set nome = ?, email = ?  where id = ?
         ");
        $pst -> bindValue(1, $nome);
        $pst -> bindValue(2, $email);
        $pst -> bindValue(3, $id);
        $resultado = $pst->execute();
        return $resultado;
    }

    public function pegaEmail($id){
        $pst = Conexao::getPreparedStatement('select email from cliente where id = ?;');
        $pst -> bindValue(1, $id);
        $pst->execute();
        $usuario = $pst->fetch(PDO::FETCH_ASSOC);
        return $usuario;
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