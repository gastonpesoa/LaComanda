<?php
namespace Middlewares;
use App\Models\Usuario;
use Clases\Token;

class OperacionesMW
{
    public function __construct()
    {
    }

    public function RegisterOperacion($request, $response, $next){

        $status = 401;
        $headers = $request->getHeaders();
        $token = $headers["HTTP_TOKEN"][0];
        try
        {
            $userToken = Token::GetData($token);
            $userOrm = new Usuario();
            $user = $userOrm->find($userToken->id);
            $user->cantidad_operaciones += 1;
            $user->save();
            return $next($request, $response);
        }
        catch(\Exception $ex)
        {
            $respuesta = array("Estado" => "ERROR", "Mensaje" => "Bad dB Conection.", "Excepcion" => $ex->getMessage());
        }
        return $response->withJson($respuesta, $status);
    }
}