<?php

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

return function (App $app)
{
    $container = $app->getContainer();

    $app->post('/login', \UsuarioApi::class . ':LoginUser')
        ->add(\RegisterEntryMW::class . ':RegisterEntry')
        ->add(\OperacionesMW::class . ':RegisterOperacion');

    $app->get('/pedido/listarPedidosPorMesa/{codigoMesa}', PedidoApi::class . ':GetAllPedidosByMesa');
    $app->get('/pedido/tiempoRestante/{codigoMesa}/{codigoPedido}', PedidoApi::class . ':GetTiempoRestantePedido');

    $app->group('/usuario', function() use ($container){
        $this->post('/new', \UsuarioApi::class . ':InsertUser');
        $this->post('/update', \UsuarioApi::class . ':UpdateUser');
        $this->post('/delete/{id}', \UsuarioApi::class . ':DeleteUser');
        $this->post('/suspender/{id}', \UsuarioApi::class . ':SuspenderUser');
        $this->get('/', \UsuarioApi::class . ':GetAllUsers');
        $this->get('/entradasEntreFechas', \UsuarioApi::class . ':GetEntrysBetweenDates');
        $this->get('/operacionesPorSectorEntreFechas', \UsuarioApi::class . ':GetOperationsCountBySector');
        $this->get('/operacionesPorSectorPorEmpleadoEntreFechas', \UsuarioApi::class . ':GetOperationsCountBySectorByEmpleado');
        $this->get('/operacionesPorEmpleadoEntreFechas', \UsuarioApi::class . ':GetOperationsCountByEmpleado');
    })->add(\PerfilMW::class . ':ValidarAdmin')->add(\TokenMW::class . ':VerificarToken');

    $app->group('/mesa', function() use ($container){
        $this->post('/new', MesaApi::class . ':InsertMesa')->add(\PerfilMW::class . ':ValidarSocio');
        $this->post('/updateFotoMesa', MesaApi::class . ':UpdateFotoMesa')->add(\PerfilMW::class . ':ValidarMozo');
        $this->post('/updateEstadoMesa', MesaApi::class . ':UpdateEstadoMesa')->add(\PerfilMW::class . ':ValidarMozo');
        $this->post('/cobrar', MesaApi::class . ':CobrarMesa')->add(\PerfilMW::class . ':ValidarSocio');
        $this->post('/close/{codigo}', MesaApi::class . ':CloseMesa')->add(\PerfilMW::class . ':ValidarSocio');
        $this->post('/delete/{codigo}', MesaApi::class . ':DeleteMesa')->add(\PerfilMW::class . ':ValidarSocio');
        $this->get('/', MesaApi::class . ':GetAllMesas')->add(\PerfilMW::class . ':ValidarSocio');
    })->add(\TokenMW::class . ':VerificarToken')
        ->add(\RegisterEntryMW::class . ':RegisterEntry')
        ->add(\OperacionesMW::class . ':RegisterOperacion');

    $app->group('/mesa', function() use ($container){
        $this->get('/masYMenosUsadaEntreFechas', MesaApi::class . ':GetMesaMasYMenosUsada');
        $this->get('/masYMenosFacturadaEntreFechas', MesaApi::class . ':GetMesaMasYMenosFacturada');
        $this->get('/mayorYMenorImporteEntreFechas', MesaApi::class . ':GetMesaMayorYMenorImporte');
        $this->get('/facturacionEntreFechas', MesaApi::class . ':GetFacturacionEntreFechas');
        $this->get('/mejorYPeorPuntuada', MesaApi::class . ':GetMejorYPeorPuntuada');
    })->add(\PerfilMW::class . ':ValidarAdmin')->add(\TokenMW::class . ':VerificarToken');

    $app->group('/menu', function() use ($container){
        $this->post('/new', MenuApi::class . ':InsertMenu')->add(\PerfilMW::class . ':ValidarSocio');
        $this->post('/update', MenuApi::class . ':UpdateMenu')->add(\PerfilMW::class . ':ValidarSocio');
        $this->post('/delete/{id}', MenuApi::class . ':DeleteMenu')->add(\PerfilMW::class . ':ValidarSocio');
        $this->get('/', MenuApi::class . ':GetAllMenu');
    })->add(\TokenMW::class . ':VerificarToken')
    ->add(\RegisterEntryMW::class . ':RegisterEntry')
    ->add(\OperacionesMW::class . ':RegisterOperacion');

    $app->group('/pedido', function() use ($container){
        $this->get('/listarTodos', PedidoApi::class . ':GetAllPedidos')
            ->add(\PerfilMW::class . ':ValidarSocio');
        $this->get('/listarCancelados', PedidoApi::class . ':GetAllCancelPedidos')
            ->add(\PerfilMW::class . ':ValidarSocio');
        $this->get('/listarActivos', PedidoApi::class . ':GetAllActivePedidos');
        $this->post('/new', PedidoApi::class . ':InsertPedido')
            ->add(\PerfilMW::class . ':ValidarMozo');
        $this->post('/cancelar/{codigo}', PedidoApi::class . ':CancelPedido')
            ->add(\PerfilMW::class . ':ValidarMozo');
        $this->post('/tomarPedido', PedidoApi::class . ':TomarPedido')
            ->add(\PedidoMW::class . ':ValidarTomarPedido');
        $this->post('/listoParaServir', PedidoApi::class . ':PedidoListoParaServir')
            ->add(\PedidoMW::class . ':ValidarListoParaServir');
        $this->post('/servir', PedidoApi::class . ':ServirPedido')
            ->add(\PedidoMW::class . ':ValidarServir');
    })->add(\TokenMW::class . ':VerificarToken')
    ->add(\RegisterEntryMW::class . ':RegisterEntry')
    ->add(\OperacionesMW::class . ':RegisterOperacion');

    $app->group('/pedido', function() use ($container){
        $this->get('/masYMenosVendidoEntreFechas', PedidoApi::class . ':GetPedidoMasYMenosVendido');
        $this->get('/entregadosFueraDeTiempoEntreFechas', PedidoApi::class . ':GetPedidosNoEntregadosATiempo');
        $this->get('/cancelados', PedidoApi::class . ':GetPedidosCancelados');
    })->add(\PerfilMW::class . ':ValidarAdmin')->add(\TokenMW::class . ':VerificarToken');

    $app->group('/encuesta', function() use ($container){
        $this->post('/realizar', EncuestaApi::class . ':InsertEncuesta')->add(\EncuestaMW::class . ':ValidarEncuesta');
    });

    $app->group('/factura', function() use ($container){
        $this->get('/listarVentasPDF', FacturaApi::class . ':ListarPDF');
        $this->get('/listarVentasExcel', FacturaApi::class . ':ListarExcel');
    })->add(\PerfilMW::class . ':ValidarAdmin')->add(\TokenMW::class . ':VerificarToken');
};
