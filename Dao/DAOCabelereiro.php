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
        $pst = Conexao::getPreparedStatement("select hj.horario as horario from jornada_diaria as jd
                inner join horario_jornada as hj on hj.id = jd.horario_jornada_id
                where jd.cabelereiro_id = ? and jd.dia_semana = dayofweek(?)
                order by horario
                ;");
        $pst->bindValue(1, $id);
        $pst->bindValue(2, $data);
        $pst->execute();
        $lista = $pst->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($lista);
        return $lista;
    }
}
