<?php
namespace Dispatcher\Swagger;

use Slim\Http\Request;
use Slim\Http\Response;

class SwaggerDispatcher {
    public static function InjectRoutesFromConfig(\Slim\App $app, $config) {
        $paramsValidator = new ParamsValidator();
        $requestValidator = new RequestValidator();
       foreach ($config['paths'] as $route => $path) {
            foreach ($path as $method => $data) {
               $requestParams = new Elements($data['parameters']);
                $app->map(
                    [$method], 
                    $route, 
                    CommandRegisterer::register(
                        $route, 
                        $method, 
                        $data['operationId'],
                        $requestParams, 
                        $paramsValidator, 
                        $requestValidator,
                        $app
                    )
                );
            }
        }
    }
    
}

class Elements implements \Psr\Container\ContainerInterface {
    private $elements;
    
    public function __construct(array $elements) {
        $this->elements = $elements;
    }
    public function get($id) {
        return $this->elements[$id];
    }

    public function has($id): bool {
        return isset($this->elements[$id]);
    }
}

class CommandRegisterer {
    public static function register(
            $route, 
            $method,
            String $operationId,
            Elements $requestParams, 
            ParamsValidator $paramsValidator, 
            RequestValidator $requestValidator
    ) {
        return function (Request $request, Response $response, $params)  use (
            $route, 
            $method, 
            $operationId,
            $requestParams, 
            $paramsValidator, 
            $requestValidator,
            \Psr\Container\ContainerInterface $container
        ) {
            if (!$paramsValidator->isValid($requestParams, $request)) {
                //log error
                //bla
            }
            
            if (!$requestValidator->isValid($route, $method, $requestParams, $request)) {
                //log error
                //bla
            } 

            /* @var CommandHandler $handler */
            $handler = $container->get($operationId);
            $handler->execute($request, $response, $params);
        };
    }
}


class ParamsValidator {
    private function validateTypes(Elements $requestParams, Request $request) {
        return true;
    }
    
    public function isValid(Elements $requestParams, Request $request) {
        //validate types, maybe with filter_var
        if (!$this->validateTypes($requestParams, $request)) {
            return false;
        }
    }
}

class RequestValidator {
    public function isValid($route, $method, Elements $requestParams, request $request) {
        return true;
    }
}
