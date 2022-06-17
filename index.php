<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: *');
header("Access-Control-Allow-Methods: GET, POST, DELETE");

use Slim\Factory\AppFactory;
use \Controller\ClienteController;
use Model\Cliente;

require_once './Controller/ClienteController.php';

require __DIR__ . './vendor/autoload.php';

$app = AppFactory::create();
$app->addRoutingMiddleware();
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

$app->get('/', function ($request, $response, $args) {
    
    $controller = new ClienteController();
    $result = $controller->home();

    $response->getBody()->write($result);
    return $response->withHeader('Content-Type', 'application/json');
});

$app->post('/login-cliente', function ($request, $response, $args) {
    
    $json = $request->getBody();
    
    $controller = new ClienteController();
    $data = json_decode($json);
    $result = $controller->login(email: $data->email, senha: $data->senha);
    if ($result instanceof Throwable) {
        $response->getBody()->write($result->getMessage());
        return $response->withStatus(500);
    }
    if (count($result) > 0) {
        $jsonResponse = json_encode($result);
        $response->getBody()->write($jsonResponse);
        return $response->withHeader('Content-Type', 'application/json');
    }else{
        return $response->withStatus(401);
    }
});

$app->post('/cadastro-cliente', function ($request, $response, $args) {
    
    $json = $request->getBody();
    $data = json_decode($json);
    
    $controller = new ClienteController();
    $cliente = new Cliente();
    $cliente->id = md5(uniqid());
    $cliente->nome = $data->nome;
    $cliente->email = $data->email;
    $cliente->foto = "";

    $result = $controller->cadastro(cliente: $cliente, senha: $data->senha);
    if ($result instanceof Throwable) {
        $erro = [
            "erro" => $result->getMessage()
        ];
        $response->getBody()->write(json_encode($erro));
        return $response->withStatus(500);
    }
    if ($result["message"]) {
        if ($result["message"] == 'Este E-mail ja está sendo utilizado') {
            $jsonResponse = json_encode($result);
            $response->getBody()->write($jsonResponse);
            return $response->withStatus(401);
        }else if($result["message"] == 'ok'){
            return $response->withStatus(201);
        }
        
    }
    return $response->withHeader('Content-Type', 'application/json');

    
});


$app->run();