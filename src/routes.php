<?php

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

return function (App $app) {
    $container = $app->getContainer();

    $app->group('/usuario', function() use ($container) {                                
        $this->get('/welcome', \UsuarioApi::class . ':Welcome');
        $this->get('/', \UsuarioApi::class . ':GetAll');
        $this->get('/{id}', \UsuarioApi::class . ':GetById');
        $this->post('/new', \UsuarioApi::class . ':RegisterUser');
        $this->post('/login', \UsuarioApi::class . ':LoginUser');
    });  
};
