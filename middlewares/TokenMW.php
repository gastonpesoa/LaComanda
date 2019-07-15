<?php
namespace Middlewares;
use Clases\Token;

class TokenMW
{
    public function __construct()
    {
    }

    public function VerificarToken($request, $response, $next){

        $status = 401;
        $headers = $request->getHeaders();
        $token = $headers["HTTP_TOKEN"][0];
        try
        {
            Token::VerifyToken($token);
            return $next($request, $response);
        }
        catch(\Exception $ex)
        {
            $respuesta = array("Estado" => "ERROR", "Mensaje" => $ex->getMessage());
        }
        return $response->withJson($respuesta, $status);
    }
}