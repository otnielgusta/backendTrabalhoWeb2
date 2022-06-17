<?php
  include './conexao.php';
class TesteConexao{
    public function testeLista(){
      $lista = [];
      $pst = Conexao::getPreparedStatement('select * from cabelereiro;');
      $pst->execute();
      $lista = $pst->fetchAll(PDO::FETCH_ASSOC);
      return $lista;
    }
}

$testeConexao = new TesteConexao();
$teste = $testeConexao->testeLista();

echo json_encode($teste);

?>