<?php

namespace App\Controllers;

use App\Exceptions\InvalidParamException;
use App\Exceptions\SqlQueryException;

use App\Models\AppModel;
use App\Models\Http;
use Slim\Http\Request;
use Slim\Http\Response;

class AppController {
    private $model;

    public function __construct() {
        $this->model = new AppModel();
    }

    public function search(Request $request, Response $response, array $args) : Response {
        $app = $this->model;
        $params = $request->getParsedBody();



        try {
            if (empty($params)) {
                throw new SqlQueryException('Não foram encontrados currículos com estes parâmetros', Http::NOT_FOUND);
            }

            $curriculums = $app->search($params);

            return Http::getJsonReponseSuccess($response, $curriculums, 'Sucesso', Http::OK);
        } catch (InvalidParamException $error) {
            return Http::getJsonReponseError($response, $error->getMessage(), Http::BAD_REQUEST);

        } catch (SqlQueryException $error) {
            return Http::getJsonReponseError($response, $error->getMessage(), Http::NOT_FOUND);

        } catch (\Exception $error) {
            return Http::getJsonResponseErrorServer($response, $error);
        }
    }
}