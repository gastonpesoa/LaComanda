<?php

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

return function (App $app)
{
    $container = $app->getContainer();

    $app->post('/login', \UsuarioApi::class . ':LoginUser');

    $app->group('/usuario', function() use ($container){
        $this->post('/new', \UsuarioApi::class . ':InsertUser')->add(\PerfilMW::class . ':ValidarAdmin');
        $this->post('/update', \UsuarioApi::class . ':UpdateUser')->add(\PerfilMW::class . ':ValidarAdmin');
        $this->post('/delete/{id}', \UsuarioApi::class . ':DeleteUser')->add(\PerfilMW::class . ':ValidarAdmin');
        $this->post('/suspender/{id}', \UsuarioApi::class . ':SuspenderUser')->add(\PerfilMW::class . ':ValidarAdmin');
        $this->get('/', \UsuarioApi::class . ':GetAllUsers')->add(\PerfilMW::class . ':ValidarAdmin');
        $this->get('/{id}', \UsuarioApi::class . ':GetById');
    })->add(\TokenMW::class . ':VerificarToken');
};
