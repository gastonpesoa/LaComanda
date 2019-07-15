<?php

use Slim\App;
use Clases\UsuarioApi;
use Clases\PedidoApi;
use Clases\MesaApi;
use Clases\MenuApi;
use Clases\FacturaApi;
use Clases\EncuestaApi;

use Middlewares\PerfilMW;
use Middlewares\PedidoMW;
use Middlewares\EncuestaMW;
use Middlewares\TokenMW;
use Middlewares\RegisterEntryMW;
use Middlewares\OperacionesMW;

return function (App $app) {
    $container = $app->getContainer();

    // view renderer
    $container['renderer'] = function ($c) {
        $settings = $c->get('settings')['renderer'];
        return new \Slim\Views\PhpRenderer($settings['template_path']);
    };

    // monolog
    $container['logger'] = function ($c) {
        $settings = $c->get('settings')['logger'];
        $logger = new \Monolog\Logger($settings['name']);
        $logger->pushProcessor(new \Monolog\Processor\UidProcessor());
        $logger->pushHandler(new \Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
        return $logger;
    };

    // ORM
    $capsule = new \Illuminate\Database\Capsule\Manager;
    $capsule->addConnection($container->get('settings')['db']);
    $capsule->setAsGlobal();
    $capsule->bootEloquent();
    //pass the connection to global container
    $container['db'] = function ($container) use ($capsule){
        return $capsule;
    };

    // Registration Controller
    $container['UsuarioApi'] = function($c) {
        return new UsuarioApi($c->get('logger'));
    };

    $container['PedidoApi'] = function() {
        return new PedidoApi();
    };

    $container['MesaApi'] = function() {
        return new MesaApi();
    };

    $container['MenuApi'] = function() {
        return new MenuApi();
    };

    $container['EncuestaApi'] = function() {
        return new EncuestaApi();
    };

    $container['FacturaApi'] = function() {
        return new FacturaApi();
    };

    // Registration MW
    $container['PerfilMW'] = function() {
        return new PerfilMW();
    };

    $container['PedidoMW'] = function() {
        return new PedidoMW();
    };

    $container['EncuestaMW'] = function() {
        return new EncuestaMW();
    };

    $container['TokenMW'] = function() {
        return new TokenMW();
    };

    $container['RegisterEntryMW'] = function() {
        return new RegisterEntryMW();
    };

    $container['OperacionesMW'] = function() {
        return new OperacionesMW();
    };
};
