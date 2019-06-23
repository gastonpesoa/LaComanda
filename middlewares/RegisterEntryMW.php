<?php

namespace Middlewares;
use Clases\UsuarioApi;
use Clases\Token;
use App\Models\Login;
use \DateTime;

class RegisterEntryMW
{
    public function __construct()
    {
    }

    public function RegisterEntry($request, $response, $next)
    {
        $path = $request->getUri()->getPath();
        $data = $request->getParsedBody();
        $routeObj = $request->getAttribute('route');

        if(!empty($routeObj))
        {
            $methods = $routeObj->getMethods();
            try
            {
                if($path == 'login')
                {
                    $username = $data['username'];
                    $user = UsuarioApi::GetUserByUsername($username);
                }
                else
                {
                    $headers = $request->getHeaders();
                    $token = $headers["HTTP_TOKEN"][0];
                    $user = Token::GetData($token);
                }
                if(strcasecmp($user->tipo, "admin") !== 0)
                {
                    $log = new Login();
                    $log->idUser = $user->id;
                    $log->fecha = date('Y-m-d H:i:s');
                    $log->metodo = $methods[0];
                    $log->ruta = $path;
                    $log->save();
                }
                return $next($request, $response);
            }
            catch(\Exception $ex)
            {
                $respuesta = array("Estado" => "ERROR", "Mensaje" => "Token invalido.", "Excepcion" => $ex->getMessage());
            }
        }
        else{
            $respuesta = array("Estado" => "ERROR", "Mensaje" => "Ruta invalida.");
        }
        return $response->withJson($respuesta, 401);
    }
}

?>