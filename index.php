<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: *');

use Controller\Authenticate;
use Controller\CabelereiroController;
use Slim\Factory\AppFactory;
use \Controller\ClienteController;
use Controller\HorarioController;
use Dao\DAOHorario;
use Model\Cabelereiro;
use Model\Cliente;
use Model\Horario;

require_once './Controller/ClienteController.php';

require __DIR__ . '/vendor/autoload.php';

$app = AppFactory::create();
$app->addRoutingMiddleware();
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

$app->get('/geraid', function ($request, $response, $args) {
    // $headersJson = json_encode($request->getHeaders());
    //$headers = json_decode($headersJson);
    //$headersJson = json_encode($request->getHeaders());
    //$headers = json_decode($headersJson);
    //$auth = new Authenticate();

    // $validacao = $auth->decodeJWT(jwt: $headers->authorization[0]);
    //$response->getBody()->write(json_encode($validacao));
    //return $response->withHeader('Content-Type', 'application/json');

    $headers = [
        "id" => uniqid(),
        "senha" => md5("senha"),
    ];
    $response->getBody()->write(json_encode($headers));

    return $response->withHeader('Content-Type', 'application/json');
});

$app->get('/', function ($request, $response, $args) {
    // $headersJson = json_encode($request->getHeaders());
    //$headers = json_decode($headersJson);
    //$headersJson = json_encode($request->getHeaders());
    //$headers = json_decode($headersJson);
    //$auth = new Authenticate();

    // $validacao = $auth->decodeJWT(jwt: $headers->authorization[0]);
    //$response->getBody()->write(json_encode($validacao));
    //return $response->withHeader('Content-Type', 'application/json');

    //$headers = [
    //    "id"=> uniqid(),
    //    "senha"=> md5("senha"),
    // ];
    // $response->getBody()->write(json_encode($headers));
    $json = $request->getBody();
    $data = json_decode($json);

    $dao = new DAOHorario();
    $dao->adicionarJornada($data->cabelereiro, $data->dia, $data->horario);
    return $response->withHeader('Content-Type', 'application/json');
});

$app->get('/auth', function ($request, $response, $args) {
    $headersJson = json_encode($request->getHeaders());
    $headers = json_decode($headersJson);

    if ($headers->authorization) {
        $jwt = $headers->authorization[0];
        $validacao = Authenticate::validateJWT(jwt: $jwt);
        if ($validacao instanceof \Firebase\JWT\ExpiredException) {
            $response->getBody()->write(json_encode([
                "msg" => "Token expirado"
            ]));
            return $response->withStatus(401);
        }
        if ($validacao instanceof \Firebase\JWT\SignatureInvalidException) {
            $response->getBody()->write(json_encode([
                "msg" => "Erro de assinatura"
            ]));
            return $response->withStatus(401);
        }
        if ($validacao instanceof Throwable) {
            $response->getBody()->write($validacao->getMessage());
            return $response->withStatus(500);
        }
        if ($validacao == true) {
            return $response->withStatus(200);
        }
    }
    $response->getBody()->write(json_encode([
        "msg" => "Você precisa estar logado"
    ]));
    return $response->withStatus(401);
});


$app->get('/lista-horarios', function ($request, $response, $args) {
    $paramsJson = json_encode($request->getQueryParams());
    $params = json_decode($paramsJson);
    if ($params->id && $params->data) {
        $id = $params->id;
        $data = $params->data;
    } else {
        return $response->withStatus(404);
    }
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
                "token" => json_decode($headers->authorization[0]),
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
            $result = $controller->buscaHorarios(id: $id, data: $data);

            if (count($result) > 0) {
                $response->getBody()->write(json_encode($result));
                return $response->withHeader('Content-Type', 'application/json');
            } else {
                return $response->withStatus(404);
            }
        }
    } else {
        return $response->withStatus(401);
    }
});

//cabelereiro

$app->post('/atualiza-foto-cabelereiro', function ($request, $response, $args) {
    $headersJson = json_encode($request->getHeaders());
    $headers = json_decode($headersJson);

    $json = $request->getBody();
    $data = json_decode($json);

    if ($headers->authorization) {
        $auth = new Authenticate();
        $jwt = $headers->authorization[0];
        $validacao = $auth->decodeJWT(jwt: json_decode($jwt));

        if ($validacao instanceof \Firebase\JWT\ExpiredException) {
            $response->getBody()->write(json_encode([
                "msg" => "Token expirado"
            ]));
            return $response->withStatus(401);
        }
        if ($validacao instanceof \Firebase\JWT\SignatureInvalidException) {
            $response->getBody()->write(json_encode([
                "token" => json_decode($headers->authorization[0]),
                "msg" => "Erro de assinatura"
            ]));
            return $response->withStatus(401);
        }
        if ($validacao instanceof Throwable) {
            $response->getBody()->write($validacao->getMessage());
            return $response->withStatus(500);
        }

        if ($validacao->id) {
            $id = $validacao->id;
            $controller = new CabelereiroController();
            $result = $controller->atualizaFoto(id: $id, foto: $data->foto,);
            if ($result instanceof Throwable) {
                $erro = [
                    "message" => $result->getMessage()
                ];
                $response->getBody()->write(json_encode($erro));
                return $response->withStatus(500);
            }

            if ($result["message"]) {
                if ($result["status"] == "error") {
                    $jsonResponse = json_encode($result);
                    $response->getBody()->write($jsonResponse);
                    return $response->withStatus(500);
                } else if ($result["status"] == "success") {
                    $jsonResponse = json_encode($result);
                    $response->getBody()->write($jsonResponse);
                    return $response->withStatus(202);
                }
            }
            if (!$result) {
                $result['message'] = 'Ocorreu um erro';
                $jsonResponse = json_encode($result);
                $response->getBody()->write($jsonResponse);
                return $response->withStatus(500);
            }
        }
    }

    return $response->withHeader('Content-Type', 'application/json');
});


$app->post('/atualiza-cabelereiro', function ($request, $response, $args) {
    $headersJson = json_encode($request->getHeaders());
    $headers = json_decode($headersJson);

    $json = $request->getBody();
    $data = json_decode($json);

    if ($headers->authorization) {
        $auth = new Authenticate();
        $jwt = $headers->authorization[0];
        $validacao = $auth->decodeJWT(jwt: json_decode($jwt));

        if ($validacao instanceof \Firebase\JWT\ExpiredException) {
            $response->getBody()->write(json_encode([
                "msg" => "Token expirado"
            ]));
            return $response->withStatus(401);
        }
        if ($validacao instanceof \Firebase\JWT\SignatureInvalidException) {
            $response->getBody()->write(json_encode([
                "token" => json_decode($headers->authorization[0]),
                "msg" => "Erro de assinatura"
            ]));
            return $response->withStatus(401);
        }
        if ($validacao instanceof Throwable) {
            $response->getBody()->write($validacao->getMessage());
            return $response->withStatus(500);
        }

        if ($validacao->id) {
            $id = $validacao->id;
            $controller = new CabelereiroController();
            $result = $controller->atualizaCabelereiro($id, $data->email, $data->nome, $data->senha);
            if ($result instanceof Throwable) {
                $erro = [
                    "message" => $result->getMessage()
                ];
                $response->getBody()->write(json_encode($erro));
                return $response->withStatus(500);
            }

            if ($result["message"]) {
                if ($result["status"] == "error") {
                    $jsonResponse = json_encode($result);
                    $response->getBody()->write($jsonResponse);
                    return $response->withStatus(500);
                } else if ($result["status"] == "success") {
                    $jsonResponse = json_encode($result);
                    $response->getBody()->write($jsonResponse);
                    return $response->withStatus(202);
                    return $response->withHeader('Content-Type', 'application/json');
                }
            }
            if (!$result) {
                $result['message'] = 'Ocorreu um erro';
                $jsonResponse = json_encode($result);
                $response->getBody()->write($jsonResponse);
                return $response->withStatus(500);
            }
        }
    }
});

$app->post('/cadastro-cabelereiro', function ($request, $response, $args) {

    $json = $request->getBody();
    $data = json_decode($json);

    $controller = new CabelereiroController();

    $result = $controller->cadastro(
        $data->nome,
        $data->email,
        $data->senha,
        $data->jornada_semanal,
        $data->jornada_diaria,
        $data->horario_texto,
        $data->data_texto
    );
    if ($result instanceof Throwable) {
        $erro = [
            "message" => $result->getMessage()
        ];
        $response->getBody()->write(json_encode($erro));
        return $response->withStatus(500);
    }
    if ($result["message"]) {
        if ($result["status"] == "error") {
            if ($result["message"] == 'Este E-mail ja está sendo utilizado') {
                $jsonResponse = json_encode($result);
                $response->getBody()->write($jsonResponse);
                return $response->withStatus(409);
            } else if (
                $result["message"] == 'Ocorreu um erro ao cadastar o usuário' ||
                $result["message"] == 'Ocorreu um erro ao cadastar o a jornada semanal' ||
                $result["message"] == 'Ocorreu um erro ao cadastar o a jornada diária'
            ) {
                $jsonResponse = json_encode($result);
                $response->getBody()->write($jsonResponse);
                return $response->withStatus(500);
            }
        } else if ($result["status"] == "success") {
            $jsonResponse = json_encode($result);
            $response->getBody()->write($jsonResponse);
            return $response->withStatus(201);
        }
    }
    if (!$result) {
        $result['message'] = 'Ocorreu um erro';
        $jsonResponse = json_encode($result);
        $response->getBody()->write($jsonResponse);
        return $response->withStatus(500);
    }
    return $response->withHeader('Content-Type', 'application/json');
});

$app->post('/login-cabelereiro', function ($request, $response, $args) {

    $json = $request->getBody();

    $controller = new CabelereiroController();
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
                "token" => json_decode($headers->authorization[0]),
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
            } else {
                return $response->withStatus(404);
            }
        }
    } else {
        return $response->withStatus(401);
    }
});

$app->get('/lista-horarios-marcados', function ($request, $response, $args) {
    $paramsJson = json_encode($request->getQueryParams());
    $params = json_decode($paramsJson);
    if ($params->data) {
        $data = $params->data;
    } else {
        return $response->withStatus(404);
    }
    $headersJson = json_encode($request->getHeaders());
    $headers = json_decode($headersJson);
    if ($headers->authorization) {
        $auth = new Authenticate();
        $jwt = $headers->authorization[0];
        $validacao = $auth->decodeJWT(jwt: json_decode($jwt));
        if ($validacao instanceof \Firebase\JWT\ExpiredException) {
            $response->getBody()->write(json_encode([
                "msg" => "Token expirado"
            ]));
            return $response->withStatus(401);
        }
        if ($validacao instanceof \Firebase\JWT\SignatureInvalidException) {
            $response->getBody()->write(json_encode([
                "token" => json_decode($headers->authorization[0]),
                "msg" => "Erro de assinatura"
            ]));
            return $response->withStatus(401);
        }
        if ($validacao instanceof Throwable) {
            $response->getBody()->write($validacao->getMessage());
            return $response->withStatus(500);
        }

        if ($validacao->id) {
            $controller = new HorarioController();
            $result = $controller->listaHorariosMarcados(id: $validacao->id, data: $data);

            if (count($result) > 0) {
                $response->getBody()->write(json_encode($result));
                return $response->withHeader('Content-Type', 'application/json');
            } else {
                return $response->withStatus(404);
            }
        }
    } else {
        return $response->withStatus(401);
    }
});

$app->get('/busca-cabelereiro', function ($request, $response, $args) {

    $headersJson = json_encode($request->getHeaders());
    $headers = json_decode($headersJson);
    if ($headers->authorization) {
        $auth = new Authenticate();
        $jwt = $headers->authorization[0];
        $validacao = $auth->decodeJWT(jwt: json_decode($jwt));
        if ($validacao instanceof \Firebase\JWT\ExpiredException) {
            $response->getBody()->write(json_encode([
                "msg" => "Token expirado"
            ]));
            return $response->withStatus(401);
        }
        if ($validacao instanceof \Firebase\JWT\SignatureInvalidException) {
            $response->getBody()->write(json_encode([
                "token" => json_decode($headers->authorization[0]),
                "msg" => "Erro de assinatura"
            ]));
            return $response->withStatus(401);
        }
        if ($validacao instanceof Throwable) {
            $response->getBody()->write($validacao->getMessage());
            return $response->withStatus(500);
        }

        if ($validacao->id) {
            $controller = new CabelereiroController();
            $result = $controller->getCabelereiro(id: $validacao->id);

            if ($result instanceof Throwable) {
                return $response->withStatus(500);
            }

            if ($result["status"]) {
                if ($result["status"] == "error") {
                    return $response->withStatus(500);
                } else if ($result["status"] == "success") {
                    $jsonResponse = json_encode($result);
                    $response->getBody()->write($jsonResponse);
                    return $response->withHeader('Content-Type', 'application/json');
                }
            }
            if (!$result) {
                $result['message'] = 'Ocorreu um erro';
                $jsonResponse = json_encode($result);
                $response->getBody()->write($jsonResponse);
                return $response->withStatus(500);
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
    $cliente->nome = $data->nome;
    $cliente->email = $data->email;
    $cliente->foto = "";

    $result = $controller->cadastro($data->nome,  $data->email, $data->senha);
    if ($result instanceof Throwable) {
        $erro = [
            "message" => $result->getMessage()
        ];
        $response->getBody()->write(json_encode($erro));
        return $response->withStatus(500);
    }
    if ($result["message"]) {
        if ($result["status"] == "error") {
            if ($result["message"] == 'Este E-mail ja está sendo utilizado') {
                $jsonResponse = json_encode($result);
                $response->getBody()->write($jsonResponse);
                return $response->withStatus(409);
            } else if (
                $result["message"] == 'Ocorreu um erro ao cadastar o usuário'

            ) {
                $jsonResponse = json_encode($result);
                $response->getBody()->write($jsonResponse);
                return $response->withStatus(500);
            }
        } else if ($result["status"] == "success") {
            $jsonResponse = json_encode($result);
            $response->getBody()->write($jsonResponse);
            return $response->withStatus(201);
        }
    }
    if (!$result) {
        $result['message'] = 'Ocorreu um erro';
        $jsonResponse = json_encode($result);
        $response->getBody()->write($jsonResponse);
        return $response->withStatus(500);
    }
    return $response->withHeader('Content-Type', 'application/json');
});

$app->post('/atualiza-foto-cliente', function ($request, $response, $args) {
    $headersJson = json_encode($request->getHeaders());
    $headers = json_decode($headersJson);

    $json = $request->getBody();
    $data = json_decode($json);

    if ($headers->authorization) {
        $auth = new Authenticate();
        $jwt = $headers->authorization[0];
        $validacao = $auth->decodeJWT(jwt: json_decode($jwt));

        if ($validacao instanceof \Firebase\JWT\ExpiredException) {
            $response->getBody()->write(json_encode([
                "msg" => "Token expirado"
            ]));
            return $response->withStatus(401);
        }
        if ($validacao instanceof \Firebase\JWT\SignatureInvalidException) {
            $response->getBody()->write(json_encode([
                "token" => json_decode($headers->authorization[0]),
                "msg" => "Erro de assinatura"
            ]));
            return $response->withStatus(401);
        }
        if ($validacao instanceof Throwable) {
            $response->getBody()->write($validacao->getMessage());
            return $response->withStatus(500);
        }

        if ($validacao->id) {
            $id = $validacao->id;
            $controller = new ClienteController();
            $result = $controller->atualizaFoto(id: $id, foto: $data->foto,);
            if ($result instanceof Throwable) {
                $erro = [
                    "message" => $result->getMessage()
                ];
                $response->getBody()->write(json_encode($erro));
                return $response->withStatus(500);
            }

            if ($result["message"]) {
                if ($result["status"] == "error") {
                    $jsonResponse = json_encode($result);
                    $response->getBody()->write($jsonResponse);
                    return $response->withStatus(500);
                } else if ($result["status"] == "success") {
                    $jsonResponse = json_encode($result);
                    $response->getBody()->write($jsonResponse);
                    return $response->withStatus(202);
                }
            }
            if (!$result) {
                $result['message'] = 'Ocorreu um erro';
                $jsonResponse = json_encode($result);
                $response->getBody()->write($jsonResponse);
                return $response->withStatus(500);
            }
        }
    }

    return $response->withHeader('Content-Type', 'application/json');
});

$app->post('/atualiza-cliente', function ($request, $response, $args) {
    $headersJson = json_encode($request->getHeaders());
    $headers = json_decode($headersJson);

    $json = $request->getBody();
    $data = json_decode($json);

    if ($headers->authorization) {
        $auth = new Authenticate();
        $jwt = $headers->authorization[0];
        $validacao = $auth->decodeJWT(jwt: json_decode($jwt));

        if ($validacao instanceof \Firebase\JWT\ExpiredException) {
            $response->getBody()->write(json_encode([
                "msg" => "Token expirado"
            ]));
            return $response->withStatus(401);
        }
        if ($validacao instanceof \Firebase\JWT\SignatureInvalidException) {
            $response->getBody()->write(json_encode([
                "token" => json_decode($headers->authorization[0]),
                "msg" => "Erro de assinatura"
            ]));
            return $response->withStatus(401);
        }
        if ($validacao instanceof Throwable) {
            $response->getBody()->write($validacao->getMessage());
            return $response->withStatus(500);
        }

        if ($validacao->id) {
            $id = $validacao->id;
            $controller = new ClienteController();
            $result = $controller->atualizaCliente($id, $data->email, $data->nome, $data->senha);
            if ($result instanceof Throwable) {
                $erro = [
                    "message" => $result->getMessage()
                ];
                $response->getBody()->write(json_encode($erro));
                return $response->withStatus(500);
            }

            if ($result["message"]) {
                if ($result["status"] == "error") {
                    $jsonResponse = json_encode($result);
                    $response->getBody()->write($jsonResponse);
                    return $response->withStatus(500);
                } else if ($result["status"] == "success") {
                    $jsonResponse = json_encode($result);
                    $response->getBody()->write($jsonResponse);
                    return $response->withStatus(202);
                    return $response->withHeader('Content-Type', 'application/json');
                }
            }
            if (!$result) {
                $result['message'] = 'Ocorreu um erro';
                $jsonResponse = json_encode($result);
                $response->getBody()->write($jsonResponse);
                return $response->withStatus(500);
            }
        }
    }
});

$app->get('/busca-cliente', function ($request, $response, $args) {

    $headersJson = json_encode($request->getHeaders());
    $headers = json_decode($headersJson);
    if ($headers->authorization) {
        $auth = new Authenticate();
        $jwt = $headers->authorization[0];
        $validacao = $auth->decodeJWT(jwt: ($jwt));
        if ($validacao instanceof \Firebase\JWT\ExpiredException) {
            $response->getBody()->write(json_encode([
                "msg" => "Token expirado"
            ]));
            return $response->withStatus(401);
        }
        if ($validacao instanceof \Firebase\JWT\SignatureInvalidException) {
            $response->getBody()->write(json_encode([
                "token" => json_decode($headers->authorization[0]),
                "msg" => "Erro de assinatura"
            ]));
            $response->withStatus(401);
            return $response->withHeader('Content-Type', 'application/json');
        }
        if ($validacao instanceof Throwable) {
            $response->getBody()->write($validacao->getMessage());
            $response->withStatus(500);
            return $response->withHeader('Content-Type', 'application/json');
        }

        if ($validacao->id) {
            $controller = new ClienteController();
            $result = $controller->getCliente(id: $validacao->id);

            if ($result instanceof Throwable) {
                return $response->withStatus(500);
            }

            if ($result["status"]) {
                if ($result["status"] == "error") {
                    return $response->withStatus(500);
                } else if ($result["status"] == "success") {
                    $jsonResponse = json_encode($result);
                    $response->getBody()->write($jsonResponse);
                    return $response->withHeader('Content-Type', 'application/json');
                }
            }
            if (!$result) {
                $result['message'] = 'Ocorreu um erro';
                $jsonResponse = json_encode($result);
                $response->getBody()->write($jsonResponse);
                $response->withStatus(500);
                return $response->withHeader('Content-Type', 'application/json');
            }
        }
    } else {
        return $response->withStatus(401);
    }
});
//horario
$app->post('/agendar-horario', function ($request, $response, $args) {
    $headersJson = json_encode($request->getHeaders());
    $headers = json_decode($headersJson);
    $json = $request->getBody();
    $data = json_decode($json);

    $controller = new HorarioController();
    $horario = new Horario();
    $horario->id = uniqid();
    $horario->data = $data->data;
    $horario->cabelereiro_id = $data->cabelereiro_id;
    $horario->horario_cabelereiro = $data->horario_cabelereiro;

    if ($headers->authorization) {
        $auth = new Authenticate();
        $validacao = $auth->decodeJWT(jwt: ($headers->authorization[0]));

        if ($validacao instanceof \Firebase\JWT\ExpiredException) {
            $response->getBody()->write(json_encode([
                "msg" => "Token expirado"
            ]));
            return $response->withStatus(401);
        }
        if ($validacao instanceof \Firebase\JWT\SignatureInvalidException) {
            $response->getBody()->write(json_encode([
                "token" => json_decode($headers->authorization[0]),
                "msg" => "Erro de assinatura"
            ]));
            return $response->withStatus(401);
        }
        if ($validacao->id) {
            $horario->cliente_id = $validacao->id;
            echo $horario->toJson();

            if (!$horario->todosCamposPreenchidos()) {
                echo "erro dos campos";
                return $response->withStatus(500);
            }

            $result = $controller->marcarHorario(horario: $horario);
            if ($result instanceof Throwable) {
                $erro = [
                    "erro" => $result->getMessage()
                ];
                $response->getBody()->write(json_encode($erro));
                echo "erro de exception";

                return $response->withStatus(500);
            }
            if ($result["message"]) {
                if ($result["message"] == 'ok') {
                    return $response->withStatus(201);
                } else {
                    echo "erro pq deu erro";

                    return $response->withStatus(500);
                }
            }
            return $response->withHeader('Content-Type', 'application/json');
        }
    } else {
        return $response->withStatus(401);
    }
});

$app->get('/lista-todos-horarios', function ($request, $response, $args) {


    $controller = new HorarioController();
    $result = $controller->listaTodosHorarios();

    if (count($result) > 0) {
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    } else {
        return $response->withStatus(404);
    }
});
$app->run();
