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
            
            select jd.id as id, 'tarde' as parte, date_format(hj2.horario, '%H:%i') as horario from jornada_diaria as jd
            inner join horario_jornada as hj2 on hj2.id = jd.horario_jornada_id
            where jd.cabelereiro_id = ? and jd.dia_semana = dayofweek(?) and (hour(hj2.horario) > 12 and  hour(hj2.horario) < 18)
            
            union
            
            select jd.id as id, 'noite' as parte, date_format(hj2.horario, '%H:%i') as horario from jornada_diaria as jd
            inner join horario_jornada as hj2 on hj2.id = jd.horario_jornada_id
            where jd.cabelereiro_id = ? and jd.dia_semana = dayofweek(?) and hour(hj2.horario) > 18

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
}
