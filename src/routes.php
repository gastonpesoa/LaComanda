<?php

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

return function (App $app)
{
    $container = $app->getContainer();

    $app->post('/login', \UsuarioApi::class . ':LoginUser');

    $app->group('/usuario', function() use ($container){
        $this->post('/new', \UsuarioApi::class . ':InsertUser');
        $this->post('/update', \UsuarioApi::class . ':UpdateUser');
        $this->post('/delete/{id}', \UsuarioApi::class . ':DeleteUser');
        $this->post('/suspender/{id}', \UsuarioApi::class . ':SuspenderUser');
        $this->get('/', \UsuarioApi::class . ':GetAllUsers');
        $this->get('/entradasEntreFechas', \UsuarioApi::class . ':GetEntrysBetweenDates');
        $this->get('/operacionesPorSectorEntreFechas', \UsuarioApi::class . ':GetAllOperationsBySector');
    })->add(\PerfilMW::class . ':ValidarAdmin')->add(\TokenMW::class . ':VerificarToken');
};
