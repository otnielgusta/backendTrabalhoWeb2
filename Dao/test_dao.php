<?php
    $json = json_decode(file_get_contents("php://input"));
    $email = $json->email;
  
    require_once './dao_cliente.php';
    $dao = new DAOCliente();

    $result = $dao->login(usuario: $email);

    echo json_encode($result);


?>