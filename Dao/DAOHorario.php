<?php

namespace Dao;

use ArrayObject;
use Dao\Conexao;
use Model\Horario;
use PDO;

class DAOHorario
{
    public function marcaHorario(Horario $horario)
    {
        $id = $this->pegarId(horario: $horario);

        if ($id) {
            $pst = Conexao::getPreparedStatement('insert into horario_agendado values(?, ?, ?, ?);');
            $pst->bindValue(1, $horario->id);
            $pst->bindValue(2, $horario->data);
            $pst->bindValue(3, $horario->cliente_id);
            $pst->bindValue(4, $id['id']);
            $pst->execute();
            return true;
        }

        return false;
    }

    public function adicionarJornada($cabelereiro, $dia, $horario)
    {
        $pst = Conexao::getPreparedStatement('insert into jornada_diaria values(?, ?, ?, ?);');
        $pst->bindValue(1, uniqid());
        $pst->bindValue(2, $cabelereiro);
        $pst->bindValue(3, $dia);
        $pst->bindValue(4, $horario);
        $result = $pst->execute();
        return $result;
    }

    public function pegarId(Horario $horario)
    {

        $pst = Conexao::getPreparedStatement('select id from jornada_diaria where cabelereiro_id = ? and horario_jornada_id = ? and dia_semana = dayofweek(?);');
        $pst->bindValue(1, $horario->cabelereiro_id);
        $pst->bindValue(2, $horario->horario_cabelereiro);
        $pst->bindValue(3, $horario->data);
        $pst->execute();
        $id = $pst->fetch(PDO::FETCH_ASSOC);

        return $id;
    }

    public function buscaHorariosCabelereiros($id, $data)
    {

        $pst = Conexao::getPreparedStatement("select ha.id as id_cliente, cl.nome as nome, cl.foto as foto, date_format(hj.horario, '%H:%i') as horario, 'manha' as parte from horario_agendado as ha
        inner join cliente as cl on cl.id = ha.cliente_id
        inner join jornada_diaria as jd on jd.id = ha.horario_cabelereiro
        inner join  horario_jornada as hj on hj.id = jd.horario_jornada_id 
        where ha.data = ? and jd.cabelereiro_id = ? and hour(hj.horario) < 12
        
        union all
        
        select ha.id as id_cliente, cl.nome as nome, cl.foto as foto, date_format(hj.horario, '%H:%i') as horario, 'tarde' as parte from horario_agendado as ha
        inner join cliente as cl on cl.id = ha.cliente_id
        inner join jornada_diaria as jd on jd.id = ha.horario_cabelereiro
        inner join  horario_jornada as hj on hj.id = jd.horario_jornada_id 
        where ha.data = ? and jd.cabelereiro_id = ? and (hour(hj.horario) > 12 and  hour(hj.horario) < 18)
        
        union all
        
        select ha.id as id_cliente, cl.nome as nome, cl.foto as foto, date_format(hj.horario, '%H:%i') as horario, 'noite' as parte from horario_agendado as ha
        inner join cliente as cl on cl.id = ha.cliente_id
        inner join jornada_diaria as jd on jd.id = ha.horario_cabelereiro
        inner join  horario_jornada as hj on hj.id = jd.horario_jornada_id 
        where ha.data = ? and jd.cabelereiro_id = ? and hour(hj.horario) > 18
        ;");

        $pst->bindValue(1, $data);
        $pst->bindValue(2, $id);
        $pst->bindValue(3, $data);
        $pst->bindValue(4, $id);
        $pst->bindValue(5, $data);
        $pst->bindValue(6, $id);
        $pst->execute();
        $result = $pst->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }

    public function listaTodoshorarios()
    {
        $lista = [];
        $pst = Conexao::getPreparedStatement("
        select id, date_format(horario, '%H:%i') as horario, 'manha' as parte from horario_jornada where hour(horario) < 12

        union all

        select id, date_format(horario, '%H:%i') as hora, 'tarde' as parte from horario_jornada where hour(horario) > 12 and hour(horario) < 18

        union all

        select id, date_format(horario, '%H:%i') as horario, 'noite' as parte from horario_jornada where hour(horario) > 18
        ;
        ");
        $pst->execute();
        $lista = $pst->fetchAll(PDO::FETCH_ASSOC);

        return $lista;
    }

    public function criaJornadaSemanal($id,  $jornada_semanal)
    {

        foreach ($jornada_semanal as $key => $value) {
            $pst = Conexao::getPreparedStatement('
                insert into jornada_semanal values(?, ?);
                 
                 ');
            $pst->bindValue(1, $id);
            $pst->bindValue(2, $value);
            $resultado = $pst->execute();

            if (!$resultado) {
                return false;
            }
        }
        return true;
    }

    public function criaJornadaDiaria($id,  $jornada_diaria, $jornada_semanal)
    {
        $i = 0;
        $j = 0;

        $pst = Conexao::getPreparedStatement('
        insert into jornada_diaria values(?, ?, ?, ?);
         ');
        for ($i = 0; $i < count($jornada_semanal); $i++) {
            for ($j = 0; $j < count($jornada_diaria); $j++) {
                $pst->bindValue(1, uniqid());
                $pst->bindValue(2, $id);
                $pst->bindValue(3, $jornada_semanal[$i]);
                $pst->bindValue(4, $jornada_diaria[$j]);
                $resultado = $pst->execute();

                if (!$resultado) {
                    return false;
                }
            }
        }
        return true;
    }
}
