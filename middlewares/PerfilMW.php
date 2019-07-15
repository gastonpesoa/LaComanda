<?php
namespace Middlewares;
use Clases\Token;

class PerfilMW
{
    public function __construct()
    {
    }

    public function ValidarAdmin($request, $response, $next){

        $status = 401;
        $headers = $request->getHeaders();
        $token = $headers["HTTP_TOKEN"][0];
        try
        {
            $data = Token::GetData($token);
            if(strcasecmp($data->tipo, 'admin') == 0)
            {
                return $next($request, $response);
            }
            else{
                $respuesta = array("Estado" => "ERROR", "Mensaje" => "Usted no tiene permisos para realizar esta operación. Comuniquese con su administrador");
            }
        }
        catch(\Exception $ex)
        {
            $respuesta = array("Estado" => "ERROR", "Mensaje" => $ex->getMessage());
        }
        return $response->withJson($respuesta, $status);
    }

    public function ValidarSocio($request, $response, $next){

        $status = 401;
        $headers = $request->getHeaders();
        $token = $headers["HTTP_TOKEN"][0];
        try
        {
            $data = Token::GetData($token);
            if(strcasecmp($data->tipo, 'socio') == 0)
            {
                return $next($request, $response);
            }
            else{
                $respuesta = array("Estado" => "ERROR", "Mensaje" => "Usted no tiene permisos para realizar esta operación");
            }
        }
        catch(\Exception $ex)
        {
            $respuesta = array("Estado" => "ERROR", "Mensaje" => $ex->getMessage());
        }
        return $response->withJson($respuesta, $status);
    }

    public function ValidarMozo($request, $response, $next){

        $status = 401;
        $headers = $request->getHeaders();
        $token = $headers["HTTP_TOKEN"][0];
        try
        {
            $data = Token::GetData($token);
            if(strcasecmp($data->tipo, 'mozo') == 0 || strcasecmp($data->tipo, 'socio') == 0)
            {
                return $next($request, $response);
            }
            else{
                $respuesta = array("Estado" => "ERROR", "Mensaje" => "Usted no tiene permisos para realizar esta operación. Comuniquese con su administrador");
            }
        }
        catch(\Exception $ex)
        {
            $respuesta = array("Estado" => "ERROR", "Mensaje" => $ex->getMessage());
        }
        return $response->withJson($respuesta, $status);
    }
}