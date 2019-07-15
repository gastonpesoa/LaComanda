<?php
namespace Middlewares;
use Clases\Token;
use Clases\PedidoApi;
use Clases\UsuarioApi;

class PedidoMW
{
    public static function ValidarTomarPedido($request, $response, $next)
    {
        $parametros = $request->getParsedBody();
        $headers = $request->getHeaders();
        $token = $headers["HTTP_TOKEN"][0];

        try
        {
            $respuesta = array("Estado" => "ERROR", "Mensaje" => "Son requeridos código de pedido y tiempo estimado de preparación");
            if(isset($parametros['codigo']) && isset($parametros['tiempo']))
            {
                $tiempoEstimado = filter_var(trim($parametros['tiempo']), FILTER_VALIDATE_INT);
                $codigo = filter_var(trim($parametros['codigo']), FILTER_SANITIZE_STRING);
                $respuesta = array("Estado" => "ERROR", "Mensaje" => "Tiempo estimado debe ser en minutos");
                if($codigo && $tiempoEstimado)
                {
                    $pedido = PedidoApi::GetPedidoByCodigo($codigo);
                    $data = Token::GetData($token);
                    if (is_null($pedido)) {
                        $respuesta = array("Estado" => "ERROR", "Mensaje" => "No existe pedido con codigo ". $codigo);
                    } else if (strcasecmp($pedido->estado, 'pendiente') != 0) {
                        $respuesta = array("Estado" => "ERROR", "Mensaje" => "Pedido no se encuentra pendiente");
                    } else if (strcasecmp(PedidoApi::GetPedidoSector($pedido), $data->tipo) != 0) {
                        $respuesta = array("Estado" => "ERROR", "Mensaje" => "Pedido codigo, ".$codigo." con sector ".PedidoApi::GetPedidoSector($pedido));
                    } else {
                        return $next($request, $response);
                    }
                }
            }
        }
        catch(\Exception $ex)
        {
            $respuesta = array("Estado" => "ERROR", "Mensaje" => $ex->getMessage());
        }
        return $response->withJson($respuesta, $status);
    }

    public static function ValidarListoParaServir($request, $response, $next)
    {
        $parametros = $request->getParsedBody();
        $headers = $request->getHeaders();
        $token = $headers["HTTP_TOKEN"][0];

        try
        {
            $respuesta = array("Estado" => "ERROR", "Mensaje" => "Es requerido código de pedido");
            if(isset($parametros['codigo']))
            {
                $codigo = filter_var(trim($parametros['codigo']), FILTER_SANITIZE_STRING);
                $respuesta = array("Estado" => "ERROR", "Mensaje" => "Código inválido");
                if($codigo)
                {
                    $pedido = PedidoApi::GetPedidoByCodigo($codigo);
                    $data = Token::GetData($token);
                    if (is_null($pedido)) {
                        $respuesta = array("Estado" => "ERROR", "Mensaje" => "No existe pedido con codigo ". $codigo);
                    } else if (strcasecmp($pedido->estado, 'en preparación') != 0) {
                        $respuesta = array("Estado" => "ERROR", "Mensaje" => "Pedido codigo, ".$codigo." en estado ".$pedido->estado);
                    } else if ($pedido->idUser != $data->id) {
                        $respuesta = array("Estado" => "ERROR", "Mensaje" => "Usuario incorrecto");
                    } else {
                        return $next($request, $response);
                    }
                }
            }
        }
        catch(\Exception $ex)
        {
            $respuesta = array("Estado" => "ERROR", "Mensaje" => $ex->getMessage());
        }
        return $response->withJson($respuesta, $status);
    }

    public static function ValidarServir($request, $response, $next)
    {
        $parametros = $request->getParsedBody();
        $headers = $request->getHeaders();
        $token = $headers["HTTP_TOKEN"][0];

        try
        {
            $respuesta = array("Estado" => "ERROR", "Mensaje" => "Es requerido código de pedido");
            if(isset($parametros['codigo']))
            {
                $codigo = filter_var(trim($parametros['codigo']), FILTER_SANITIZE_STRING);
                $respuesta = array("Estado" => "ERROR", "Mensaje" => "Código inválido");
                if($codigo)
                {
                    $pedido = PedidoApi::GetPedidoByCodigo($codigo);
                    $data = Token::GetData($token);
                    if (is_null($pedido)) {
                        $respuesta = array("Estado" => "ERROR", "Mensaje" => "No existe pedido con codigo ". $codigo);
                    } else if (strcasecmp($pedido->estado, 'listo para servir') != 0) {
                        $respuesta = array("Estado" => "ERROR", "Mensaje" => "Pedido codigo, ".$codigo." en estado ".$pedido->estado);
                    } else if ($pedido->idMozo != $data->id) {
                        $mozo = UsuarioApi::GetUserById($pedido->idMozo);
                        $respuesta = array("Estado" => "ERROR", "Mensaje" => "Pedido codigo, ".$codigo." con mozo ".$mozo->nombre);
                    } else {
                        return $next($request, $response);
                    }
                }
            }
        }
        catch(\Exception $ex)
        {
            $respuesta = array("Estado" => "ERROR", "Mensaje" => $ex->getMessage());
        }
        return $response->withJson($respuesta, $status);
    }
}