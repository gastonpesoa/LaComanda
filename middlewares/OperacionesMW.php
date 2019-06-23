<?php
namespace Middlewares;

class OperacionesMW
{
    public function VerificarLogin($request, $response, $next){

        $status = 401;
        //$params = $request->getParsedBody();
        $headers = $request->getHeaders();
        $token = $headers["HTTP_TOKEN"][0];
        try
        {
            Token::VerifyToken($token);
            return $next($request, $response);
        }
        catch(\Exception $ex)
        {
            $respuesta = array("Estado" => "ERROR", "Mensaje" => "Token invalido.", "Excepcion" => $ex->getMessage());
        }
        return $response->withJson($respuesta, $status);
    }
}