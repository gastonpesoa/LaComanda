<?php
namespace Clases;
use App\Models\Pedido;
use \DateTime;
use \DateInterval;

class PedidoApi
{
    public function __construct()
    {
    }

    public static function GetPedidoByCodigo($codigo)
    {
        $pedidoORM = new Pedido();
        return $pedidoORM->where('codigo', "=", $codigo)->first();
    }

    public function GetAllPedidos($request, $response, $args)
    {
        $pedidoORM = new Pedido();
        $pedidos = $pedidoORM::orderBy('fecha', 'desc')->get();
        $resp = PedidoApi::ShowPedidosArray($pedidos);
        return $response->withJson($resp, 200);
    }

    public function GetAllCancelPedidos($request, $response, $args)
    {
        $pedidoORM = new Pedido();
        $pedidosOrder = $pedidoORM::orderBy('fecha', 'desc')->where('estado', '=', 'cancelado')->get();
        $resp = PedidoApi::ShowPedidosArray($pedidosOrder);
        return $response->withJson($resp, 200);
    }

    public function GetAllActivePedidos($request, $response, $args)
    {
        $pedidoORM = new Pedido();
        $pedidosOrder = $pedidoORM::orderBy('fecha', 'desc')->where('estado', '!=', 'cancelado')->get();
        $resp = PedidoApi::ShowPedidosArray($pedidosOrder);
        return $response->withJson($resp, 200);
    }

    public static function GetPedidosByMesa($mesa)
    {
        $pedidoORM = new Pedido();
        return $pedidoORM::orderBy('fecha', 'desc')->where([
            ['estado', '!=', 'cancelado'],
            ['estado', '!=', 'finalizado'],
            ['idMesa', '=', $mesa->codigoMesa]
        ])->get();
    }

    public function GetAllPedidosByMesa($request, $response, $args)
    {
        $status = 400;
        $respuesta = array("Estado" => "ERROR", "Mensaje" => "Es requerido id de mesa");
        if(isset($args['codigoMesa']))
        {
            $idMesa = filter_var(trim($args['codigoMesa']), FILTER_SANITIZE_STRING);
            $respuesta = array("Estado" => "ERROR", "Mensaje" => "Id inválido");
            if($idMesa)
            {
                $respuesta = array("Estado" => "ERROR", "Mensaje" => "No existe mesa con id ".$idMesa);
                $mesaExist = MesaApi::GetMesaByCodigo($idMesa);
                if($mesaExist)
                {
                    $pedidosByMesa = PedidoApi::GetPedidosByMesa($mesaExist);
                    $respuesta = PedidoApi::ShowPedidosArray($pedidosByMesa);
                    if((count($respuesta) < 1)){
                        $respuesta = array("Estado" => "OK", "Mensaje" => "Mesa sin pedidos pendientes");
                    }
                    $status = 200;
                }
            }
        }
        return $response->withJson($respuesta, $status);
    }

    public function GetTiempoRestantePedido($request, $response, $args)
    {
        $status = 400;
        $respuesta = array("Estado" => "ERROR", "Mensaje" => "Es requerido id de mesa y código de pedido");
        if(isset($args['codigoMesa']) && isset($args['codigoPedido']))
        {
            $idMesa = filter_var(trim($args['codigoMesa']), FILTER_SANITIZE_STRING);
            $codigo = filter_var(trim($args['codigoPedido']), FILTER_SANITIZE_STRING);
            $respuesta = array("Estado" => "ERROR", "Mensaje" => "Id o código inválido");
            if($idMesa && $codigo)
            {
                $respuesta = array("Estado" => "ERROR", "Mensaje" => "No existe mesa ".$idMesa." o pedido ".$codigo);
                $mesaExist = MesaApi::GetMesaByCodigo($idMesa);
                $pedido = PedidoApi::GetPedidoByCodigo($codigo);
                if($mesaExist && $pedido)
                {
                    $pedidoORM = new Pedido();
                    $pedido = $pedidoORM->where([
                        ['codigo', '=', $codigo],
                        ['idMesa', '=', $idMesa]
                    ])->first();

                    if(is_null($pedido))
                        $respuesta = array("Estado" => "ERROR", "Mensaje" => "No existe pedido ".$codigo." para la mesa ".$idMesa);
                    else
                    {
                        if(strcasecmp($pedido->estado, 'en preparación') == 0){
                            $time = new DateTime('now');
                            $horaEntrega = new DateTime($pedido->horaEntregaEstimada);

                            if($time > $horaEntrega)
                                $respuesta = array("Estado" => "OK", "Mensaje" => "Pedido retrasado");
                            else{
                                $intervalo = $time->diff($horaEntrega);
                                $respuesta = array("Estado" => "OK", "Mensaje" => "Tiempo restante ".$intervalo->format('%H:%I:%S'));
                            }
                        }
                        else{
                            $respuesta = array("Estado" => "ERROR", "Mensaje" => "Pedido en estado ".$pedido->estado);
                        }
                        $status = 200;
                    }
                }
            }
        }
        return $response->withJson($respuesta, $status);
    }

    public static function GetPedidoSector($pedido)
    {
        $menu = MenuApi::GetMenuById($pedido->idMenu);
        return $menu->sector;
    }

    public static function GetPedidoImporte($pedido)
    {
        $menu = MenuApi::GetMenuById($pedido->idMenu);
        return $menu->precio;
    }

    public function InsertPedido($request, $response)
    {
        $data = $request->getParsedBody();
        $headers = $request->getHeaders();
        $token = $headers["HTTP_TOKEN"][0];
        $status = 400;
        $respuesta = array("Estado" => "ERROR", "Mensaje" => "Son requeridos nombre de cliente, id de mesa y de menú");
        if(isset($data['nombreCliente']) && isset($data['codigoMesa']) && isset($data['idMenu']))
        {
            $nombreCliente = filter_var(trim($data['nombreCliente']), FILTER_SANITIZE_STRING);
            $codigoMesa = filter_var(trim($data['codigoMesa']), FILTER_SANITIZE_STRING);
            $idMenu = filter_var(trim($data['idMenu']), FILTER_VALIDATE_INT);
            $respuesta = array("Estado" => "ERROR", "Mensaje" => "Valores inválidos");
            if($nombreCliente && $codigoMesa && $idMenu)
            {
                $respuesta = array("Estado" => "ERROR", "Mensaje" => "Código de mesa o de menú incorrecto");
                $mesa = MesaApi::GetMesaByCodigo($codigoMesa);
                $menu = MenuApi::GetMenuById($idMenu);
                if($mesa && $menu)
                {
                    $dataToken = Token::GetData($token);
                    $codigoPedido = substr(str_shuffle(str_repeat("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ", 5)), 0, 5);
                    $fecha = date('Y-m-d');
                    $horaInicial = date('H:i');

                    $pedido = new Pedido();
                    $pedido->codigo = $codigoPedido;
                    $pedido->estado = 'pendiente';
                    $pedido->fecha = $fecha;
                    $pedido->horaInicial = $horaInicial;
                    $pedido->idMesa = $codigoMesa;
                    $pedido->idMenu = $idMenu;
                    $pedido->idMozo = $dataToken->id;
                    $pedido->nombreCliente = $nombreCliente;
                    $pedido->save();
                    $respuesta = array("Estado" => "OK", "Mensaje" => "Pedido, ".$codigoPedido." registrado");
                    $status = 200;
                }
            }
        }
        return $response->withJson($respuesta, $status);
    }

    public function CancelPedido($request, $response, $args)
    {
        $status = 400;
        $respuesta = array("Estado" => "ERROR", "Mensaje" => "Es requerido código de pedido");
        if(isset($args['codigo']))
        {
            $codigo = $args['codigo'];
            $pedido = PedidoApi::GetPedidoByCodigo($codigo);
            $respuesta = array("Estado" => "ERROR", "Mensaje" => "No existe pedido con codigo ".$codigo);
            if($pedido)
            {
                $pedido->estado = 'cancelado';
                $pedido->save();
                $status = 200;
                $respuesta = array("Estado" => "OK", "Mensaje" => "Pedido codigo: ".$codigo." cancelado");
            }
        }
        return $response->withJson($respuesta, $status);
    }

    public function TomarPedido($request, $response)
    {
        $data = $request->getParsedBody();
        $headers = $request->getHeaders();
        $dataToken = Token::GetData($headers["HTTP_TOKEN"][0]);
        $pedido = PedidoApi::GetPedidoByCodigo($data['codigo']);
        $time = new DateTime('now');
        $time->add(new DateInterval('PT' . $data['tiempo'] . 'M'));
        $horaEntregaEstimada = $time->format('H:i');
        $pedido->estado = 'en preparación';
        $pedido->idUser = $dataToken->id;
        $pedido->horaEntregaEstimada = $horaEntregaEstimada;
        $pedido->save();
        return $response->withJson(array("Estado" => "OK", "Mensaje" => "Pedido codigo: ".$pedido->codigo." en preparación"), 200);
    }

    public function PedidoListoParaServir($request, $response)
    {
        $data = $request->getParsedBody();
        $headers = $request->getHeaders();
        $dataToken = Token::GetData($headers["HTTP_TOKEN"][0]);
        $pedido = PedidoApi::GetPedidoByCodigo($data['codigo']);
        $time = new DateTime('now');
        $horaEntregaReal = $time->format('H:i');
        $pedido->estado = 'listo para servir';
        $pedido->horaEntregaReal = $horaEntregaReal;
        $pedido->save();
        return $response->withJson(array("Estado" => "OK", "Mensaje" => "Pedido codigo: ".$pedido->codigo." listo para servir"), 200);
    }

    public function ServirPedido($request, $response)
    {
        $data = $request->getParsedBody();
        $headers = $request->getHeaders();
        $dataToken = Token::GetData($headers["HTTP_TOKEN"][0]);
        $pedido = PedidoApi::GetPedidoByCodigo($data['codigo']);
        $pedido->estado = 'entregado';
        $pedido->save();
        return $response->withJson(array("Estado" => "OK", "Mensaje" => "Pedido codigo: ".$pedido->codigo." entregado"), 200);
    }

    public static function ShowPedidosArray($array)
    {
        $result = array();
        if(!is_null($array) && count($array) > 0)
        {
            foreach($array as $pedido)
            {
                $mesa = MesaApi::GetMesaByCodigo($pedido->idMesa);
                $menu = MenuApi::GetMenuById($pedido->idMenu);
                $mozo = UsuarioApi::GetUserById($pedido->idMozo);
                $encargado = UsuarioApi::GetUserById($pedido->idUser);

                $element = array(
                    "código" => $pedido->codigo,
                    "estado" => $pedido->estado,
                    "fecha" => $pedido->fecha,
                    "horaInicial" => $pedido->horaInicial,
                    "horaEntregaEstimada" => $pedido->horaEntregaEstimada,
                    "horaEntregaReal" => $pedido->horaEntregaReal,
                    "mesa" => $mesa->codigoMesa,
                    "menu" => $menu->nombre,
                    "mozo" => $mozo->nombre,
                    "encargado" => $encargado->nombre,
                    "nombreCliente" => $pedido->nombreCliente
                );
                array_push($result, $element);
            }
        }
        return $result;
    }

    public static function ValidateDateFormat($date)
    {
        if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$date))
            return true;
        else
            return false;
    }

    public function GetPedidoMasYMenosVendido($request, $response)
    {
        $data = $request->getQueryParams();
        $arrayIdMenus = array();
        $menusCount = array();
        $status = 400;
        $respuesta = array("Estado" => "ERROR", "Mensaje" => "Se requiere fecha desde y fecha hasta en formato YYYY-mm-dd");
        if(isset($data['desde']) && isset($data['hasta']))
        {
            $checkDesde = PedidoApi::ValidateDateFormat($data['desde']);
            $checkHasta = PedidoApi::ValidateDateFormat($data['hasta']);
            $respuesta = array("Estado" => "ERROR", "Mensaje" => "Formato de fecha requerido: YYYY-mm-dd");
            if($checkDesde && $checkHasta)
            {
                $fechaDesde = new DateTime($data['desde']);
                $fechaHasta = new DateTime($data['hasta']);
                $pedidoORM = new Pedido();
                $listaEnFecha = $pedidoORM->where([
                    ['fecha', '>', $fechaDesde],
                    ['fecha', '<', $fechaHasta],
                    ['estado', '=', 'finalizado']
                ])->get();

                foreach($listaEnFecha as $pedido){
                    if(!in_array($pedido->idMenu, $arrayIdMenus)){
                        array_push($arrayIdMenus, $pedido->idMenu);
                    }
                }

                foreach($arrayIdMenus as $id){
                    $count = 0;
                    foreach($listaEnFecha as $pedido){
                        if($pedido->idMenu == $id){
                            $count++;
                        }
                    }
                    $result = array('idMenu' => $id, 'cantidad' => $count);
                    array_push($menusCount, $result);
                }

                for ($i=0; $i < \count($menusCount); $i++) { 
                    if($i == 0){
                        $mayor = $menusCount[$i]['cantidad'];
                        $menor = $menusCount[$i]['cantidad'];
                        $idMenuMayor = $menusCount[$i]['idMenu'];
                        $idMenuMenor = $menusCount[$i]['idMenu'];
                    }
                    else{
                        if($menusCount[$i]['cantidad'] > $mayor){
                            $mayor = $menusCount[$i]['cantidad'];
                            $idMenuMayor = $menusCount[$i]['idMenu'];
                        }
                        if($menusCount[$i]['cantidad'] < $menor){
                            $menor = $menusCount[$i]['cantidad'];
                            $idMenuMenor = $menusCount[$i]['idMenu'];
                        }
                    }
                }

                $respuesta = array("Estado" => "OK",
                                    "Menu mas vendido" => MenuApi::GetMenuById($idMenuMayor),
                                    "Menu menos vendido" => MenuApi::GetMenuById($idMenuMenor));
                $status = 200;
            }
        }
        return $response->withJson($respuesta, $status);
    }

    public function GetPedidosNoEntregadosATiempo($request, $response)
    {
        $data = $request->getQueryParams();
        $pedidosFueraDeTiempo = array();
        $status = 400;
        $respuesta = array("Estado" => "ERROR", "Mensaje" => "Se requiere fecha desde y fecha hasta en formato YYYY-mm-dd");
        if(isset($data['desde']) && isset($data['hasta']))
        {
            $checkDesde = PedidoApi::ValidateDateFormat($data['desde']);
            $checkHasta = PedidoApi::ValidateDateFormat($data['hasta']);
            $respuesta = array("Estado" => "ERROR", "Mensaje" => "Formato de fecha requerido: YYYY-mm-dd");
            if($checkDesde && $checkHasta)
            {
                $fechaDesde = new DateTime($data['desde']);
                $fechaHasta = new DateTime($data['hasta']);
                $pedidoORM = new Pedido();
                $listaEnFecha = $pedidoORM->where([
                    ['fecha', '>', $fechaDesde],
                    ['fecha', '<', $fechaHasta],
                    ['estado', '=', 'finalizado']
                ])->get();

                foreach($listaEnFecha as $pedido)
                {
                    if($pedido->horaEntregaReal > $pedido->horaEntregaEstimada)
                        array_push($pedidosFueraDeTiempo, $pedido);
                }

                $respuesta = array("Estado" => "OK",
                                    "Pedidos entregados fuera de tiempo estipulado" => $pedidosFueraDeTiempo);
                $status = 200;
            }
        }
        return $response->withJson($respuesta, $status);
    }

    public function GetPedidosCancelados($request, $response)
    {
        $data = $request->getQueryParams();
        $status = 400;
        $respuesta = array("Estado" => "ERROR", "Mensaje" => "Se requiere fecha desde y fecha hasta en formato YYYY-mm-dd");
        if(isset($data['desde']) && isset($data['hasta']))
        {
            $checkDesde = PedidoApi::ValidateDateFormat($data['desde']);
            $checkHasta = PedidoApi::ValidateDateFormat($data['hasta']);
            $respuesta = array("Estado" => "ERROR", "Mensaje" => "Formato de fecha requerido: YYYY-mm-dd");
            if($checkDesde && $checkHasta)
            {
                $fechaDesde = new DateTime($data['desde']);
                $fechaHasta = new DateTime($data['hasta']);
                $pedidoORM = new Pedido();
                $listaEnFecha = $pedidoORM->where([
                    ['fecha', '>', $fechaDesde],
                    ['fecha', '<', $fechaHasta],
                    ['estado', '=', 'cancelado'],
                ])->get();

                $respuesta = array("Estado" => "OK",
                                    "Pedidos cancelados" => $listaEnFecha);
                $status = 200;
            }
        }
        return $response->withJson($respuesta, $status);
    }
}