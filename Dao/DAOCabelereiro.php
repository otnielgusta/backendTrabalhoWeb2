<?php

namespace Dao;

use Dao\Conexao;
use Model\Cabelereiro;
use PDO;

class DAOCabelereiro
{

    public function listaCabelereiros()
    {
        $lista = [];
        $pst = Conexao::getPreparedStatement('select id, nome, email, foto, horario_string, dias_string from cabelereiro;');
        $pst->execute();
        $lista = $pst->fetchAll(PDO::FETCH_ASSOC);
        $cabelereiro = new Cabelereiro();
        $cabelereiros = $cabelereiro->setCabelereiros(cabelereiros: $lista);
        return $cabelereiros;
    }

    public function buscaHorarios($id, $data)
    {
        $lista = [];
        $pst = Conexao::getPreparedStatement("
        select jd.id as id, 'manha' as parte, date_format(hj1.horario, '%H:%i') as horario from jornada_diaria as jd
            inner join horario_jornada as hj1 on hj1.id = jd.horario_jornada_id
            where jd.cabelereiro_id = ? and jd.dia_semana = dayofweek(?) and hour(hj1.horario) < 12
            
            union
            
            select hj.id as id, 'tarde' as parte, date_format(hj.horario, '%H:%i') as horario from jornada_diaria as jd
            inner join horario_jornada as hj on hj.id = jd.horario_jornada_id
            where jd.cabelereiro_id = ? and jd.dia_semana = dayofweek(?) and (hour(hj.horario) > 12 and  hour(hj.horario) < 18)
            
            union
            
            select hj.id as id, 'noite' as parte, date_format(hj.horario, '%H:%i') as horario from jornada_diaria as jd
            inner join horario_jornada as hj on hj.id = jd.horario_jornada_id
            where jd.cabelereiro_id = ? and jd.dia_semana = dayofweek(?) and hour(hj.horario) > 18

            ;");
        $pst->bindValue(1, $id);
        $pst->bindValue(2, $data);
        $pst->bindValue(3, $id);
        $pst->bindValue(4, $data);
        $pst->bindValue(5, $id);
        $pst->bindValue(6, $data);
        $pst->execute();
        $lista = $pst->fetchAll(PDO::FETCH_ASSOC);
        return $lista;
    }
    public function login($usuario){
        $pst = Conexao::getPreparedStatement('select id, senha from cabelereiro where email = ?;');
        $pst -> bindValue(1, $usuario);
        $pst->execute();
        $usuario = $pst->fetch(PDO::FETCH_ASSOC);
        return $usuario;
    }

    public function atualizaFoto($id, $foto){
        $pst = Conexao::getPreparedStatement("
        update cabelereiro set foto = ? where id = ?
         ");
        $pst -> bindValue(1, $foto);
        $pst -> bindValue(2, $id);
        $resultado = $pst->execute();
        return $resultado;
    }

    public function atualizaCabelereiroComSenha($id,  $email, $nome, $senha){
        $pst = Conexao::getPreparedStatement("
        update cabelereiro set  nome = ?, email = ?, senha = ? where id = ?
         ");
        $pst -> bindValue(1, $nome);
        $pst -> bindValue(2, $email);
        $pst -> bindValue(3, $senha);
        $pst -> bindValue(4, $id);
        $resultado = $pst->execute();
        return $resultado;
    }

    public function atualizaCabelereiroSemSenha($id, $email, $nome,){
        $pst = Conexao::getPreparedStatement("
        update cabelereiro set  nome = ?, email = ?  where id = ?
         ");
        $pst -> bindValue(1, $nome);
        $pst -> bindValue(2, $email);
        $pst -> bindValue(3, $id);
        $resultado = $pst->execute();
        return $resultado;
    }

    public function pegaEmail($id){
        $pst = Conexao::getPreparedStatement('select email from cabelereiro where id = ?;');
        $pst -> bindValue(1, $id);
        $pst->execute();
        $usuario = $pst->fetch(PDO::FETCH_ASSOC);
        return $usuario;
    }

    public function verificaEmail($email){
        $pst = Conexao::getPreparedStatement('select count(id) as count from cabelereiro where email = ?;');
        $pst -> bindValue(1, $email);
        $pst->execute();
        $usuario = $pst->fetch(PDO::FETCH_ASSOC);
        return $usuario['count'] > 0;
    }

    public function getCabelereiro($id) : Cabelereiro{
        $pst = Conexao::getPreparedStatement('select id, nome, email, foto, horario_string, dias_string from cabelereiro where id = ?;');
        $pst -> bindValue(1, $id);
        $pst->execute();
        $result = $pst->fetch(PDO::FETCH_ASSOC);

        $cabelereiro = new Cabelereiro();
        $cabelereiro->id = $result['id'];
        $cabelereiro->nome = $result['nome'];
        $cabelereiro->email = $result['email'];
        $cabelereiro->foto = $result['foto'];
        $cabelereiro->horario = $result['horario_string'];
        $cabelereiro->dias = $result['dias_string'];
        return $cabelereiro;
    }
    public function cadastro($id, $nome, $email, $senha, $horario_string, $dias_string){
        
        $pst = Conexao::getPreparedStatement("
        insert into cabelereiro(id, nome, email, senha, horario_string, dias_string)
        values(?, ?, ?, ?, ?, ?);
         
         ");
        $pst -> bindValue(1, $id);
        $pst -> bindValue(2, $nome);
        $pst -> bindValue(3, $email);
        $pst -> bindValue(4, $senha);
        $pst -> bindValue(5, $horario_string);
        $pst -> bindValue(6, $dias_string);
        $resultado = $pst->execute();
        return $resultado;
    }
}
