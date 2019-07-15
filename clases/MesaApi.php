<?php
namespace Clases;
use App\Models\Mesa;
use App\Models\Pedido;
use App\Models\Factura;
use App\Models\Encuesta;
use \DateTime;

class MesaApi
{
    public function __construct()
    {
    }

    public static function GetMesaByCodigo($codigo)
    {
        $mesaORM = new Mesa();
        return $mesaORM->where('codigoMesa', "=", $codigo)->first();
    }

    public function GetAllMesas($request, $response, $args)
    {
        $mesaORM = new Mesa();
        $mesas = $mesaORM::orderBy('codigoMesa', 'asc')->get();
        $resp = MesaApi::ShowMesasArray($mesas);
        return $response->withJson($resp, 200);
    }

    public function InsertMesa($request, $response)
    {
        $data = $request->getParsedBody();
        $status = 400;
        $respuesta = array("Estado" => "ERROR", "Mensaje" => "Es requerido un código de mesa");
        if(isset($data['codigo']))
        {
            $codigo = $data['codigo'];
            $respuesta = array("Estado" => "ERROR", "Mensaje" => "Código inválido, debe ser alfanumérico y de 5 caracteres");
            if(ctype_alnum(trim($codigo)) && strlen($codigo) == 5)
            {
                $respuesta = array("Estado" => "ERROR", "Mensaje" => "Ya existe mesa registrada con código ".$codigo);
                $mesaExist = MesaApi::GetMesaByCodigo($codigo);
                if(is_null($mesaExist))
                {
                    $mesa = new Mesa();
                    $mesa->codigoMesa = $codigo;
                    $mesa->estado = 'cerrada';
                    $mesa->save();
                    $respuesta = array("Estado" => "OK", "Mensaje" => "Mesa, ".$codigo." registrada");
                    $status = 200;
                }
            }
        }
        return $response->withJson($respuesta, $status);
    }

    public function UpdateFotoMesa($request, $response)
    {
        $data = $request->getParsedBody();
        $archivos = $request->getUploadedFiles();
        $status = 400;
        $respuesta = array("Estado" => "ERROR", "Mensaje" => "Es requerido código y foto de mesa");
        if(isset($data['codigo']) && isset($archivos['foto']))
        {
            $codigo = $data['codigo'];
            $respuesta = array("Estado" => "ERROR", "Mensaje" => "No existe mesa registrada con código ".$codigo);
            $mesaExist = MesaApi::GetMesaByCodigo($codigo);
            if($mesaExist)
            {
                $titulo = $mesaExist->codigoMesa;
                $destino = "./../imgMesas/";
                $nombreAnterior = $archivos['foto']->getClientFilename();
                $extension = explode(".", $nombreAnterior);
                $extension = array_reverse($extension);
                $finalName = $destino.$titulo.".".$extension[0];
                $archivos['foto']->moveTo($finalName);

                $mesaExist->foto = $finalName;
                $mesaExist->save();

                $respuesta = array("Estado" => "OK", "Mensaje" => "Foto de mesa, ".$mesaExist->codigoMesa." cargada");
                $status = 200;
            }
        }
        return $response->withJson($respuesta, $status);
    }

    public function UpdateEstadoMesa($request, $response)
    {
        $data = $request->getParsedBody();
        $status = 400;
        $validate = false;
        $respuesta = array("Estado" => "ERROR", "Mensaje" => "Es requerido código y estado de mesa");
        if(isset($data['codigo']) && isset($data['estado']))
        {
            $codigo = filter_var(trim($data['codigo']), FILTER_SANITIZE_STRING);
            $estado = filter_var(trim($data['estado']), FILTER_SANITIZE_STRING);
            $respuesta = array("Estado" => "ERROR", "Mensaje" => "Valores no permitidos");
            if($codigo && $estado)
            {
                $respuesta = array("Estado" => "ERROR", "Mensaje" => "No existe mesa registrada con código ".$codigo);
                $mesaExist = MesaApi::GetMesaByCodigo($codigo);
                if($mesaExist)
                {
                    $respuesta = array("Estado" => "ERROR", "Mensaje" => "El estado debe ser 'con cliente esperando pedido', 'con cliente comiendo' o 'con cliente pagando'");
                    if( strcasecmp($estado, 'con cliente esperando pedido') == 0 ||
                        strcasecmp($estado, 'con cliente comiendo') == 0 ||
                        strcasecmp($estado, 'con cliente pagando') == 0 )
                    {
                        switch($mesaExist->estado)
                        {
                            case 'cerrada':
                                $respuesta = array("Estado" => "ERROR", "Mensaje" => "Mesa 'cerrada', solo puede ser modificada a 'con cliente esperando pedido'");
                                if( strcasecmp($estado, 'con cliente esperando pedido') == 0)
                                    $validate = true;
                                break;
                            case 'con cliente esperando pedido':
                                $respuesta = array("Estado" => "ERROR", "Mensaje" => "Mesa 'con cliente esperando pedido', solo puede ser modificada a 'con cliente comiendo'");
                                if( strcasecmp($estado, 'con cliente comiendo') == 0)
                                    $validate = true;
                                break;
                            case 'con cliente comiendo':
                                $respuesta = array("Estado" => "ERROR", "Mensaje" => "Mesa 'con cliente comiendo', solo puede ser modificada a 'con cliente pagando'");
                                if( strcasecmp($estado, 'con cliente pagando') == 0 )
                                    $validate = true;
                                break;
                            case 'con cliente pagando':
                                $respuesta = array("Estado" => "ERROR", "Mensaje" => "Mesa 'con cliente pagando', solo puede ser cerrada por un socio");
                                break;
                        }
                        if($validate)
                        {
                            $mesaExist->estado = $estado;
                            $mesaExist->save();
                            $respuesta = array("Estado" => "OK", "Mensaje" => "Estado de mesa, ".$mesaExist->codigoMesa." modificado");
                            $status = 200;
                        }
                    }
                }
            }
        }
        return $response->withJson($respuesta, $status);
    }

    public function CloseMesa($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $status = 400;
        $respuesta = array("Estado" => "ERROR", "Mensaje" => "Es requerido código de mesa");
        if(isset($args['codigo']))
        {
            $codigo = filter_var(trim($args['codigo']), FILTER_SANITIZE_STRING);
            $respuesta = array("Estado" => "ERROR", "Mensaje" => "Valores no permitidos");
            if($codigo)
            {
                $respuesta = array("Estado" => "ERROR", "Mensaje" => "No existe mesa registrada con código ".$codigo);
                $mesaExist = MesaApi::GetMesaByCodigo($codigo);
                if($mesaExist)
                {
                    $mesaExist->estado = 'cerrada';
                    $mesaExist->save();
                    $respuesta = array("Estado" => "OK", "Mensaje" => "Mesa, ".$mesaExist->codigoMesa." cerrada");
                    $status = 200;
                }
            }
        }
        return $response->withJson($respuesta, $status);
    }

    public static function ClosePedidosMesa($mesa)
    {
        $pedidos = PedidoApi::GetPedidosByMesa($mesa);
        foreach($pedidos as $pedido)
        {
            $pedido->estado = 'finalizado';
            $pedido->save();
        }
    }

    public function DeleteMesa($request, $response, $args)
    {
        $status = 400;
        $respuesta = array("Estado" => "ERROR", "Mensaje" => "Es requerido el código de mesa");
        if(isset($args['codigo']))
        {
            $codigo = $args['codigo'];
            $mesa = MesaApi::GetMesaByCodigo($codigo);
            $respuesta = array("Estado" => "ERROR", "Mensaje" => "No existe mesa con código ".$codigo);
            if($mesa)
            {
                $mesa->delete();
                $status = 200;
                $respuesta = array("Estado" => "OK", "Mensaje" => "Mesa código: ".$codigo." eliminada");
            }
        }
        return $response->withJson($respuesta, $status);
    }

    public function CobrarMesa($request, $response)
    {
        $data = $request->getParsedBody();
        $status = 400;
        $respuesta = array("Estado" => "ERROR", "Mensaje" => "Es requerido código de mesa");
        if(isset($data['codigo']))
        {
            $codigo = filter_var(trim($data['codigo']), FILTER_SANITIZE_STRING);
            $respuesta = array("Estado" => "ERROR", "Mensaje" => "Código inválido");
            if($codigo)
            {
                $respuesta = array("Estado" => "ERROR", "Mensaje" => "No existe mesa registrada con código ".$codigo);
                $mesaExist = MesaApi::GetMesaByCodigo($codigo);
                if($mesaExist)
                {
                    $pedidos = PedidoApi::GetPedidosByMesa($mesaExist);
                    $importeFinal = 0;
                    foreach($pedidos as $pedido)
                    {
                        if(strcasecmp($pedido->estado, "entregado") == 0)
                        {
                            $importe = PedidoApi::GetPedidoImporte($pedido);
                            $importeFinal += $importe;
                        }
                    }

                    FacturaApi::Facturar($importeFinal, $mesaExist->codigoMesa);
                    MesaApi::ClosePedidosMesa($mesaExist);

                    $respuesta = array("Estado" => "OK", "Mensaje" => "Mesa, ".$mesaExist->codigoMesa." cobrada");
                    $status = 200;
                }
            }
        }
        return $response->withJson($respuesta, $status);
    }

    public static function ShowMesasArray($array)
    {
        $result = array();
        if(!is_null($array) && count($array) > 0)
        {
            foreach($array as $mesa)
            {
                $element = array(
                    "código" => $mesa->codigoMesa,
                    "estado" => $mesa->estado,
                    "foto" => $mesa->foto
                );
                array_push($result, $element);
            }
        }
        return $result;
    }

    public function GetMesaMasYMenosUsada($request, $response)
    {
        $data = $request->getQueryParams();
        $arrayIdMesas = array();
        $mesasCount = array();
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
                    if(!in_array($pedido->idMesa, $arrayIdMesas)){
                        array_push($arrayIdMesas, $pedido->idMesa);
                    }
                }

                foreach($arrayIdMesas as $id){
                    $count = 0;
                    foreach($listaEnFecha as $pedido){
                        if(strcasecmp($pedido->idMesa, $id) == 0){
                            $count++;
                        }
                    }
                    $result = array('idMesa' => $id, 'cantidad' => $count);
                    array_push($mesasCount, $result);
                }

                for ($i=0; $i < \count($mesasCount); $i++) {
                    if($i == 0){
                        $mayor = $mesasCount[$i]['cantidad'];
                        $menor = $mesasCount[$i]['cantidad'];
                        $idMesaMayor = $mesasCount[$i]['idMesa'];
                        $idMesaMenor = $mesasCount[$i]['idMesa'];
                    }
                    else{
                        if($mesasCount[$i]['cantidad'] > $mayor){
                            $mayor = $mesasCount[$i]['cantidad'];
                            $idMesaMayor = $mesasCount[$i]['idMesa'];
                        }
                        if($mesasCount[$i]['cantidad'] < $menor){
                            $menor = $mesasCount[$i]['cantidad'];
                            $idMesaMenor = $mesasCount[$i]['idMesa'];
                        }
                    }
                }

                $respuesta = array("Estado" => "OK",
                                    "Mesa más usada" => MesaApi::GetMesaByCodigo($idMesaMayor),
                                    "Mesa menos usada" => MesaApi::GetMesaByCodigo($idMesaMenor));
                $status = 200;
            }
        }
        return $response->withJson($respuesta, $status);
    }

    public function GetMesaMasYMenosFacturada($request, $response)
    {
        $data = $request->getQueryParams();
        $arrayIdMesas = array();
        $mesasCount = array();
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
                    if(!in_array($pedido->idMesa, $arrayIdMesas)){
                        $count = 0;
                        $facturaORM = new Factura();
                        $listaFacturasMesa = $facturaORM->where('codigoMesa', '=', $pedido->idMesa)->get();
                        foreach($listaFacturasMesa as $factura){
                            $count += $factura->importe;
                        }
                        $result = array('idMesa' => $pedido->idMesa, 'facturacion' => $count);
                        array_push($mesasCount, $result);
                        array_push($arrayIdMesas, $pedido->idMesa);
                    }
                }

                for ($i=0; $i < \count($mesasCount); $i++) {
                    if($i == 0){
                        $mayor = $mesasCount[$i]['facturacion'];
                        $menor = $mesasCount[$i]['facturacion'];
                        $idMesaMayor = $mesasCount[$i]['idMesa'];
                        $idMesaMenor = $mesasCount[$i]['idMesa'];
                    }
                    else{
                        if($mesasCount[$i]['facturacion'] > $mayor){
                            $mayor = $mesasCount[$i]['facturacion'];
                            $idMesaMayor = $mesasCount[$i]['idMesa'];
                        }
                        if($mesasCount[$i]['facturacion'] < $menor){
                            $menor = $mesasCount[$i]['facturacion'];
                            $idMesaMenor = $mesasCount[$i]['idMesa'];
                        }
                    }
                }

                $respuesta = array("Estado" => "OK",
                                    "Mesa que más facturó" => MesaApi::GetMesaByCodigo($idMesaMayor),
                                    "Mesa que menos facturó" => MesaApi::GetMesaByCodigo($idMesaMenor));
                $status = 200;
            }
        }
        return $response->withJson($respuesta, $status);
    }

    public function GetMesaMayorYMenorImporte($request, $response)
    {
        $data = $request->getQueryParams();
        $arrayIdMesas = array();
        $mesasCount = array();
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
                $facturaORM = new Factura();
                $listaEnFecha = $facturaORM->where([
                    ['fecha', '>', $fechaDesde],
                    ['fecha', '<', $fechaHasta]
                ])->get();

                for ($i=0; $i < \count($listaEnFecha); $i++) {
                    if($i == 0){
                        $mayor = $listaEnFecha[$i]->importe;
                        $menor = $listaEnFecha[$i]->importe;
                        $idMesaMayor = $listaEnFecha[$i]->codigoMesa;
                        $idMesaMenor = $listaEnFecha[$i]->codigoMesa;
                    }
                    else{
                        if($listaEnFecha[$i] > $mayor){
                            $mayor = $listaEnFecha[$i]->importe;
                            $idMesaMayor = $listaEnFecha[$i]->codigoMesa;
                        }
                        if($listaEnFecha[$i] < $menor){
                            $menor = $listaEnFecha[$i]->importe;
                            $idMesaMenor = $listaEnFecha[$i]->codigoMesa;
                        }
                    }
                }

                $respuesta = array("Estado" => "OK",
                                    "Mesa que tuvo la factura con el mayor importe" => MesaApi::GetMesaByCodigo($idMesaMayor),
                                    "Mesa que tuvo la factura con el menor importe" => MesaApi::GetMesaByCodigo($idMesaMenor));
                $status = 200;
            }
        }
        return $response->withJson($respuesta, $status);
    }

    public function GetFacturacionEntreFechas($request, $response)
    {
        $data = $request->getQueryParams();
        $arrayIdMesas = array();
        $mesasCount = array();
        $status = 400;
        $respuesta = array("Estado" => "ERROR", "Mensaje" => "Se requiere fecha desde y fecha hasta en formato YYYY-mm-dd y código de mesa");
        if(isset($data['desde']) && isset($data['hasta']) && isset($data['codigoMesa']))
        {
            $codigo = filter_var(trim($data['codigoMesa']), FILTER_SANITIZE_STRING);
            $checkDesde = PedidoApi::ValidateDateFormat($data['desde']);
            $checkHasta = PedidoApi::ValidateDateFormat($data['hasta']);
            $respuesta = array("Estado" => "ERROR", "Mensaje" => "Formato de fecha requerido: YYYY-mm-dd");
            if($checkDesde && $checkHasta && $codigo)
            {
                $respuesta = array("Estado" => "ERROR", "Mensaje" => "No existe mesa registrada con código ".$codigo);
                $mesaExist = MesaApi::GetMesaByCodigo($codigo);
                if($mesaExist)
                {
                    $fechaDesde = new DateTime($data['desde']);
                    $fechaHasta = new DateTime($data['hasta']);
                    $facturaORM = new Factura();
                    $listaEnFecha = $facturaORM->where([
                        ['fecha', '>', $fechaDesde],
                        ['fecha', '<', $fechaHasta],
                        ['codigoMesa', '=', $codigo],
                    ])->get();

                    $count = 0;
                    foreach($listaEnFecha as $factura){
                        $count+=$factura->importe;
                    }

                    $respuesta = array("Estado" => "OK",
                                        "Mensaje" => "Mesa ".$codigo.", facturó entre las fechas ingresadas $".$count);
                    $status = 200;
                }
            }
        }
        return $response->withJson($respuesta, $status);
    }

    public function GetMejorYPeorPuntuada($request, $response)
    {
        $data = $request->getQueryParams();
        $arrayIdMesas = array();
        $mesasCount = array();
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
                $encuestaORM = new Encuesta();
                $listaEnFecha = $encuestaORM->where([
                    ['fecha', '>', $fechaDesde],
                    ['fecha', '<', $fechaHasta]
                ])->get();

                for ($i=0; $i < \count($listaEnFecha); $i++) {
                    if($i == 0){
                        $mayor = $listaEnFecha[$i]->puntajeMesa;
                        $menor = $listaEnFecha[$i]->puntajeMesa;
                        $idMesaMayor = $listaEnFecha[$i]->codigoMesa;
                        $idMesaMenor = $listaEnFecha[$i]->codigoMesa;
                    }
                    else{
                        if($listaEnFecha[$i] > $mayor){
                            $mayor = $listaEnFecha[$i]->puntajeMesa;
                            $idMesaMayor = $listaEnFecha[$i]->codigoMesa;
                        }
                        if($listaEnFecha[$i] < $menor){
                            $menor = $listaEnFecha[$i]->puntajeMesa;
                            $idMesaMenor = $listaEnFecha[$i]->codigoMesa;
                        }
                    }
                }

                $respuesta = array("Estado" => "OK",
                                    "Mesa que tuvo el mejor puntaje" => MesaApi::GetMesaByCodigo($idMesaMayor),
                                    "Mesa que tuvo el peor puntaje" => MesaApi::GetMesaByCodigo($idMesaMenor));
                $status = 200;
            }
        }
        return $response->withJson($respuesta, $status);
    }
}