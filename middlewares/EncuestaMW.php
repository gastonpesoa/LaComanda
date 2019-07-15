<?php
namespace Middlewares;
use Clases\MesaApi;

class EncuestaMW
{
    public function __construct()
    {
    }

    public function ValidarEncuesta($request, $response, $next){

        $status = 401;
        $data = $request->getParsedBody();
        try
        {
            $respuesta = array("Estado" => "ERROR", "Mensaje" => "Es requerido, codigo de mesa, puntuación de mesa, de mozo, de cocinero, de restaurante y un comentario");
            if(isset($data['codigoMesa']) && isset($data['puntajeMesa']) && isset($data['puntajeMozo']) && isset($data['puntajeCocinero']) && isset($data['puntajeRestaurante']) && isset($data['comentario']))
            {
                $comentario = filter_var(trim($data['comentario']), FILTER_SANITIZE_STRING);
                $codigoMesa = filter_var(trim($data['codigoMesa']), FILTER_SANITIZE_STRING);
                $puntajeMesa = filter_var(trim($data['puntajeMesa']), FILTER_VALIDATE_INT);
                $puntajeMozo = filter_var(trim($data['puntajeMozo']), FILTER_VALIDATE_INT);
                $puntajeCocinero = filter_var(trim($data['puntajeCocinero']), FILTER_VALIDATE_INT);
                $puntajeRestaurante = filter_var(trim($data['puntajeRestaurante']), FILTER_VALIDATE_INT);
                $respuesta = array("Estado" => "ERROR", "Mensaje" => "Valores inválidos");
                if($comentario && $codigoMesa && $puntajeMesa && $puntajeMozo && $puntajeCocinero && $puntajeRestaurante)
                {
                    $respuesta = array("Estado" => "ERROR", "Mensaje" => "No existe mesa registrada con código ".$codigoMesa);
                    $mesaExist = MesaApi::GetMesaByCodigo($codigoMesa);
                    if($mesaExist)
                    {
                        $respuesta = array("Estado" => "ERROR", "Mensaje" => "La puntuación debe ser entre 1 y 10.");
                        if ($puntajeMesa > 0 && $puntajeMesa < 11 && $puntajeRestaurante > 0 && $puntajeRestaurante < 11 &&
                            $puntajeMozo > 0 && $puntajeMozo < 11 && $puntajeCocinero > 0 && $puntajeCocinero < 11)
                        {
                                return $next($request, $response);
                        }
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
