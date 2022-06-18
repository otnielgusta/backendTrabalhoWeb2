<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: *');

use Controller\Authenticate;
use Controller\CabelereiroController;
use Slim\Factory\AppFactory;
use \Controller\ClienteController;
use Model\Cliente;

require_once './Controller/ClienteController.php';

require __DIR__ . './vendor/autoload.php';

$app = AppFactory::create();
$app->addRoutingMiddleware();
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

$app->get('/', function ($request, $response, $args) {
   // $headersJson = json_encode($request->getHeaders());
    //$headers = json_decode($headersJson);
    $headers = [
        "id"=> uniqid(),
        "senha"=> md5(uniqid()),
    ];
    $response->getBody()->write(json_encode($headers));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->get('/lista-horarios',function ($request, $response, $args) {
    $headersJson = json_encode($request->getHeaders());
    $headers = json_decode($headersJson);
    if ($headers->authorization) {
        $auth = new Authenticate();
        $jwt = $headers->authorization[0];
        $validacao = $auth->decodeJWT(jwt: $jwt);

        if ($validacao instanceof \Firebase\JWT\ExpiredException) {
            $response->getBody()->write(json_encode([
                "msg" => "Token expirado"
            ]));
            return $response->withStatus(401);
        }
        if ($validacao instanceof \Firebase\JWT\SignatureInvalidException) {
            $response->getBody()->write(json_encode([
                "token"=> json_decode($headers->authorization[0]),
                "msg" => "Erro de assinatura"
            ]));
            return $response->withStatus(401);
        }
        if ($validacao instanceof Throwable) {
            $response->getBody()->write($validacao->getMessage());
            return $response->withStatus(500);
        }
        
        if ($validacao == true) {
            $controller = new CabelereiroController();
            $result = $controller->buscaHorarios(id: '1', data:'2022-06-12');
            
            if (count($result) > 0) {
                $response->getBody()->write(json_encode($result));
                return $response->withHeader('Content-Type', 'application/json');
            }else{
                return $response->withStatus(404);
            }
        }
    } else {
        return $response->withStatus(401);
    }
   
});

$app->get('/lista-cabelereiros', function ($request, $response, $args) {
    $headersJson = json_encode($request->getHeaders());
    $headers = json_decode($headersJson);
    if ($headers->authorization) {
        $auth = new Authenticate();
        $validacao = $auth->validateJWT(jwt: json_decode($headers->authorization[0]));
        if ($validacao instanceof \Firebase\JWT\ExpiredException) {
            $response->getBody()->write(json_encode([
                "msg" => "Token expirado"
            ]));
            return $response->withStatus(401);
        }
        if ($validacao instanceof \Firebase\JWT\SignatureInvalidException) {
            $response->getBody()->write(json_encode([
                "token"=> json_decode($headers->authorization[0]),
                "msg" => "Erro de assinatura"
            ]));
            return $response->withStatus(401);
        }
        if ($validacao == true) {
            $controller = new CabelereiroController();
            $result = $controller->listarCabelereiros();
            if ($result instanceof Throwable) {
                $response->getBody()->write($result->getMessage());
                return $response->withStatus(500);
            }
            if (count($result) > 0) {
                $response->getBody()->write(json_encode($result));
                return $response->withHeader('Content-Type', 'application/json');
            }else{
                return $response->withStatus(404);
            }
        }
    } else {
        return $response->withStatus(401);
    }


 
});

//cliente
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
    } else {
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
        } else if ($result["message"] == 'ok') {
            return $response->withStatus(201);
        }
    }
    return $response->withHeader('Content-Type', 'application/json');
});


$app->run();
