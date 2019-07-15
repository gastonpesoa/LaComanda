<?php
namespace Middlewares;
use App\Models\Usuario;
use Clases\Token;
use Clases\UsuarioApi;

class OperacionesMW
{
    public function __construct()
    {
    }

    public function RegisterOperacion($request, $response, $next){

        $status = 401;
        $headers = $request->getHeaders();
        $data = $request->getParsedBody();
        $path = $request->getUri()->getPath();
        try
        {
            if($path == 'login')
            {
                $username = $data['username'];
                $user = UsuarioApi::GetUserByUsername($username);
            }
            else
            {
                $token = $headers["HTTP_TOKEN"][0];
                $userToken = Token::GetData($token);
                $user = UsuarioApi::GetUserById($userToken->id);
            }
            if(strcasecmp($user->tipo, "admin") !== 0)
            {
                $user->cantidad_operaciones += 1;
                $user->save();
            }
            return $next($request, $response);
        }
        catch(\Exception $ex)
        {
            $respuesta = array("Estado" => "ERROR", "Mensaje" => $ex->getMessage());
        }
        return $response->withJson($respuesta, $status);
    }
}