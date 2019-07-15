<?php
namespace Clases;
use App\Models\Usuario;
use App\Models\Login;
use \DateTime;

class UsuarioApi
{
    protected $logger;

    public function __construct(\Monolog\Logger $logger)
    {
        $this->logger = $logger;
    }

    public static function GetUserByUsername($username)
    {
        $userORM = new Usuario();
        return $userORM->where('username', "=", $username)->first();
    }

    public static function GetUserSector($idUser)
    {
        $userORM = new Usuario();
        $user = $userORM->find($idUser);
        return $user->tipo;
    }

    public static function GetUserOperaciones($idUser)
    {
        $userORM = new Usuario();
        $user = $userORM->find($idUser);
        return $user->cantidad_operaciones;
    }

    public static function GetUserById($id)
    {
        $userORM = new Usuario();
        return $userORM->find($id);
    }

    public function LoginUser($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $status = 400;
        $respuesta = array("Estado" => "ERROR", "Mensaje" => "Son requeridos nombre y clave de usuario");
        if(isset($data['username']) && isset($data['password']))
        {
            $username = filter_var(trim($data['username']), FILTER_SANITIZE_STRING);
            $password = filter_var(trim($data['password']), FILTER_SANITIZE_STRING);

            $respuesta = array("Estado" => "ERROR", "Mensaje" => "Usuario o clave incorrectos");
            if($username && $password)
            {
                $usuario = UsuarioApi::GetUserByUsername($username);
                if($usuario && $usuario->estado != -1)
                {
                    if (password_verify(trim($password), $usuario->clave))
                    {
                        $respuesta = array("Estado" => "ERROR", "Mensaje" => "Usuario suspendido");
                        if($usuario->estado == 1)
                        {
                            $fecha = date('Y-m-d H:i:s');
                            $usuario->fecha_ultimo_login = $fecha;
                            $usuario->save();
                            $token = Token::CreateToken($usuario);
                            $this->logger->addInfo('User login'.$data);
                            $respuesta = array("Estado" => "OK", "Mensaje" => "Bienvenid@ " . $usuario->nombre . "!", "Token" => $token);
                            $status = 200;
                        }
                    }
                }
            }
        }
        return $response->withJson($respuesta, $status);
    }

    public function InsertUser($request, $response, $args)
    {
        $this->logger->addInfo('New user');
        $data = $request->getParsedBody();
        $status = 400;
        $respuesta = array("Estado" => "ERROR", "Mensaje" => "Son requeridos nombre de usuario, nombre, clave y perfil");
        if(isset($data['username']) && isset($data['nombre']) && isset($data['password']) && isset($data['perfil']))
        {
            $nombre = filter_var(trim($data['nombre']), FILTER_SANITIZE_STRING);
            $clave = filter_var(trim($data['password']), FILTER_SANITIZE_STRING);
            $username = filter_var(trim($data['username']), FILTER_SANITIZE_STRING);
            $perfil = filter_var(trim($data['perfil']), FILTER_SANITIZE_STRING);
            $respuesta = array("Estado" => "ERROR", "Mensaje" => "Valores no permitidos");
            if($nombre && $clave && $username && $perfil)
            {
                $respuesta = array("Estado" => "ERROR", "Mensaje" => "El perfil debe ser bartender, cervecero, cocinero, mozo o socio");
                if( strcasecmp($perfil, 'bartender') == 0 ||
                    strcasecmp($perfil, 'cervecero') == 0 ||
                    strcasecmp($perfil, 'cocinero') == 0 ||
                    strcasecmp($perfil, 'mozo') == 0 ||
                    strcasecmp($perfil, 'socio') == 0)
                {
                    $respuesta = array("Estado" => "ERROR", "Mensaje" => "Ya existe usuario registrado con nombre de usuario ".$username);
                    $userExist = UsuarioApi::GetUserByUsername($username);
                    if($userExist == null || $userExist->estado = -1)
                    {
                        $user = new Usuario();
                        $user->fecha_registro = date('Y-m-d H:i:s');
                        $user->estado = 1;
                        $user->username = $username;
                        $user->nombre = $nombre;
                        $user->clave = password_hash($clave, PASSWORD_DEFAULT);
                        $user->tipo = $perfil;
                        $user->save();
                        $respuesta = array("Estado" => "OK", "Mensaje" => "Usuario, ".$username." registrado");
                        $status = 200;
                    }
                }
            }
        }
        return $response->withJson($respuesta, $status);
    }

    public function UpdateUser($request, $response, $args)
    {
        $this->logger->addInfo('Update user');
        $data = $request->getParsedBody();
        $status = 400;
        $respuesta = array("Estado" => "ERROR", "Mensaje" => "Es requerido id de usuario");
        if(isset($data['id']))
        {
            $id = $data['id'];
            if(isset($data['nombre']))
                $nombre = filter_var(trim($data['nombre']), FILTER_SANITIZE_STRING);
            if(isset($data['password']))
                $clave = filter_var(trim($data['password']), FILTER_SANITIZE_STRING);
            if(isset($data['username']))
                $username = filter_var(trim($data['username']), FILTER_SANITIZE_STRING);
            if(isset($data['perfil']))
                $perfil = filter_var(trim($data['perfil']), FILTER_SANITIZE_STRING);

            $userOrm = new Usuario();
            $user = $userOrm->find($id);
            $respuesta = array("Estado" => "ERROR", "Mensaje" => "No existe usuario registrado con id de usuario ".$id);
            if($user != null)
            {
                if($username)
                    $user->username = $username;
                if($nombre)
                    $user->nombre = $nombre;
                if($clave)
                    $user->clave = password_hash($clave, PASSWORD_DEFAULT);
                if($perfil)
                {
                    if( strcasecmp($perfil, 'bartender') == 0 ||
                        strcasecmp($perfil, 'cervecero') == 0 ||
                        strcasecmp($perfil, 'cocinero') == 0 ||
                        strcasecmp($perfil, 'mozo') == 0 ||
                        strcasecmp($perfil, 'socio') == 0)
                    {
                        $user->tipo = $perfil;
                    }
                }
                $user->save();
                $respuesta = array("Estado" => "OK", "Mensaje" => "Usuario, ".$username." modificado");
                $status = 200;
            }
        }
        return $response->withJson($respuesta, $status);
    }

    public function DeleteUser($request, $response, $args)
    {
        $status = 400;
        $respuesta = array("Estado" => "ERROR", "Mensaje" => "Es requerido el id del usuario");
        if(isset($args['id']))
        {
            $id = $args['id'];
            $userOrm = new Usuario();
            $user = $userOrm->find($id);
            $respuesta = array("Estado" => "ERROR", "Mensaje" => "No existe usuario con id ".$id);
            if($user)
            {
                $user->estado = -1;
                $user->save();
                $status = 200;
                $respuesta = array("Estado" => "OK", "Mensaje" => "Usuario id: ".$id." eliminado");
            }
        }
        return $response->withJson($respuesta, $status);
    }

    public function SuspenderUser($request, $response, $args)
    {
        $status = 400;
        $respuesta = array("Estado" => "ERROR", "Mensaje" => "Es requerido el id del usuario");
        if(isset($args['id']))
        {
            $id = $args['id'];
            $userOrm = new Usuario();
            $user = $userOrm->find($id);
            $respuesta = array("Estado" => "ERROR", "Mensaje" => "No existe usuario con id ".$id);
            if($user)
            {
                $user->estado = 0;
                $user->save();
                $status = 200;
                $respuesta = array("Estado" => "OK", "Mensaje" => "Usuario id: ".$id." suspendido");
            }
        }
        return $response->withJson($respuesta, $status);
    }

    public function GetAllUsers($request, $response, $args)
    {
        $this->logger->addInfo('User list');
        $userORM = new Usuario();
        $params = $request->getParsedBody();

        if (!isset($params['sort']) && !isset($params['order']))
            $users = UsuarioApi::ShowUsuariosArray($userORM::all());
        else
        {
            if (isset($params['sort']) && isset($params['order']))
            {
                if (strcasecmp($params['sort'],'nombre') == 0)
                {
                    if (strcasecmp($params['order'],'desc') == 0)
                        $users = UsuarioApi::ShowUsuariosArray($userORM::orderBy('nombre', 'desc')->get());
                    else
                        if (strcasecmp($params['order'],'asc') == 0)
                            $users = UsuarioApi::ShowUsuariosArray($userORM::orderBy('nombre', 'asc')->get());
                }
            }
        }
        return $response->withJson($users, 200);
    }

    public static function ShowUsuariosArray($array)
    {
        $result = array();
        if(!is_null($array) && count($array) > 0)
        {
            foreach($array as $user)
            {
                switch ($user->estado)
                {
                    case 1:
                        $estado = "activo";
                        break;
                    case 0:
                        $estado = "suspendido";
                        break;
                    case -1:
                        $estado = "inactivo";
                        break;
                }
                $element = array(
                    "id" => $user->id,
                    "username" => $user->username,
                    "nombre" => $user->nombre,
                    "perfil" => $user->tipo,
                    "fecha_registro" => $user->fecha_registro,
                    "ultimo_login" => $user->fecha_ultimo_login,
                    "cantidad_de_operaciones" => $user->cantidad_operaciones,
                    "estado" => $estado
                );
                array_push($result, $element);
            }
        }
        return $result;
    }

    public function GetEntrysBetweenDates($request, $response, $args)
    {
        $data = $request->getQueryParams();
        $status = 400;
        $respuesta = array("Estado" => "ERROR", "Mensaje" => "Se requiere fecha desde y fecha hasta en formato YYYY-mm-dd HH:ii");
        if(isset($data['desde']) && isset($data['hasta']))
        {
            $checkDesde = UsuarioApi::ValidateDateFormat($data['desde']);
            $checkHasta = UsuarioApi::ValidateDateFormat($data['hasta']);
            $respuesta = array("Estado" => "ERROR", "Mensaje" => "Formato de fecha requerido: YYYY-mm-dd HH:ii");
            if($checkDesde && $checkHasta)
            {
                $fechaDesde = new DateTime($data['desde']);
                $fechaHasta = new DateTime($data['hasta']);
                $logsOrm = new Login();
                $respuesta = $logsOrm->where([
                    ['fecha', '>', $fechaDesde],
                    ['fecha', '<', $fechaHasta]
                    ])->get();
            }
        }
        return $response->withJson($respuesta, $status);
    }

    public static function ValidateDateFormat($date)
    {
        if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1]) (2[0-3]|[01][0-9]):[0-5][0-9]$/",$date))
            return true;
        else
            return false;
    }

    public function GetOperationsCountBySector($request, $response)
    {
        $cantOpSocio = 0;
        $cantOpMozo = 0;
        $cantOpCocinero = 0;
        $cantOpBartender = 0;
        $cantOpCervecero = 0;

        $data = $request->getQueryParams();
        $status = 400;
        $respuesta = array("Estado" => "ERROR", "Mensaje" => "Se requiere fecha desde y fecha hasta en formato YYYY-mm-dd HH:ii");
        if(isset($data['desde']) && isset($data['hasta']))
        {
            $checkDesde = UsuarioApi::ValidateDateFormat($data['desde']);
            $checkHasta = UsuarioApi::ValidateDateFormat($data['hasta']);
            $respuesta = array("Estado" => "ERROR", "Mensaje" => "Formato de fecha requerido: YYYY-mm-dd HH:ii");
            if($checkDesde && $checkHasta)
            {
                $fechaDesde = new DateTime($data['desde']);
                $fechaHasta = new DateTime($data['hasta']);
                $logsORM = new Login();
                $listaEnFecha = $logsORM->where([
                    ['fecha', '>', $fechaDesde],
                    ['fecha', '<', $fechaHasta]
                    ])->get();

                foreach($listaEnFecha as $logs)
                {
                    // if(stristr($logs->ruta, 'login') === FALSE && stristr($logs->ruta, 'usuario') === FALSE)
                    $userSector = UsuarioApi::GetUserSector($logs->idUser);
                    switch($userSector)
                    {
                        case 'socio':
                            $cantOpSocio++;
                        break;
                        case 'mozo':
                            $cantOpMozo++;
                        break;
                        case 'cocinero':
                            $cantOpCocinero++;
                        break;
                        case 'bartender':
                            $cantOpBartender++;
                            break;
                        case 'cervecero':
                            $cantOpCervecero++;
                        break;
                    }
                }
                $respuesta = array("Estado" => "OK",
                                    "Mensaje" => "Cantidad de operaciones por sector",
                                    "Socio" => $cantOpSocio,
                                    "Mozo" => $cantOpMozo,
                                    "Cocinero" => $cantOpCocinero,
                                    "Bartender" => $cantOpBartender,
                                    "Cervecero" => $cantOpCervecero
                                );
                $status = 200;
            }
        }
        return $response->withJson($respuesta, $status);
    }

    public static function GetFormatArrayUserOperacionesCount($array)
    {
        $return = array();
        foreach($array as $user)
        {
            $element = array($user->nombre => $user->cantidad_operaciones);
            array_push($return,$element);
        }
        return $return;
    }

    public function GetOperationsCountBySectorByEmpleado($request, $response)
    {
        $data = $request->getQueryParams();
        $arrayIdUsers = array();
        $arraySocios = array();
        $arrayMozos = array();
        $arrayCocineros = array();
        $arrayBartenders = array();
        $arrayCerveceros = array();
        $status = 400;
        $respuesta = array("Estado" => "ERROR", "Mensaje" => "Se requiere fecha desde y fecha hasta en formato YYYY-mm-dd HH:ii");
        if(isset($data['desde']) && isset($data['hasta']))
        {
            $checkDesde = UsuarioApi::ValidateDateFormat($data['desde']);
            $checkHasta = UsuarioApi::ValidateDateFormat($data['hasta']);
            $respuesta = array("Estado" => "ERROR", "Mensaje" => "Formato de fecha requerido: YYYY-mm-dd HH:ii");
            if($checkDesde && $checkHasta)
            {
                $fechaDesde = new DateTime($data['desde']);
                $fechaHasta = new DateTime($data['hasta']);
                $logsORM = new Login();
                $listaEnFecha = $logsORM->where([
                    ['fecha', '>', $fechaDesde],
                    ['fecha', '<', $fechaHasta]
                    ])->get();

                foreach($listaEnFecha as $logs){
                    if(!in_array($logs->idUser, $arrayIdUsers))
                        array_push($arrayIdUsers, $logs->idUser);
                }

                foreach($arrayIdUsers as $id){
                    $user = UsuarioApi::GetUserById($id);
                    switch($user->tipo){
                        case 'socio':
                            array_push($arraySocios,$user);
                        break;
                        case 'mozo':
                            array_push($arrayMozos,$user);
                        break;
                        case 'cocinero':
                            array_push($arrayCocineros,$user);
                        break;
                        case 'bartender':
                            array_push($arrayBartenders,$user);
                        break;
                        case 'cervecero':
                            array_push($arrayCerveceros,$user);
                        break;
                    }
                }

                $respuesta = array("Estado" => "OK",
                                    "Mensaje" => "Cantidad de operaciones por sector por empleado",
                                    "Socio" => UsuarioApi::GetFormatArrayUserOperacionesCount($arraySocios),
                                    "Mozo" => UsuarioApi::GetFormatArrayUserOperacionesCount($arrayMozos),
                                    "Cocinero" => UsuarioApi::GetFormatArrayUserOperacionesCount($arrayCocineros),
                                    "Bartender" => UsuarioApi::GetFormatArrayUserOperacionesCount($arrayBartenders),
                                    "Cervecero" => UsuarioApi::GetFormatArrayUserOperacionesCount($arrayCerveceros));
                $status = 200;
            }
        }
        return $response->withJson($respuesta, $status);
    }

    public function GetOperationsCountByEmpleado($request, $response)
    {
        $data = $request->getQueryParams();
        $arrayIdUsers = array();
        $arrayUsers = array();
        $status = 400;
        $respuesta = array("Estado" => "ERROR", "Mensaje" => "Se requiere fecha desde y fecha hasta en formato YYYY-mm-dd HH:ii");
        if(isset($data['desde']) && isset($data['hasta']))
        {
            $checkDesde = UsuarioApi::ValidateDateFormat($data['desde']);
            $checkHasta = UsuarioApi::ValidateDateFormat($data['hasta']);
            $respuesta = array("Estado" => "ERROR", "Mensaje" => "Formato de fecha requerido: YYYY-mm-dd HH:ii");
            if($checkDesde && $checkHasta)
            {
                $fechaDesde = new DateTime($data['desde']);
                $fechaHasta = new DateTime($data['hasta']);
                $logsORM = new Login();
                $listaEnFecha = $logsORM->where([
                    ['fecha', '>', $fechaDesde],
                    ['fecha', '<', $fechaHasta]
                    ])->get();

                foreach($listaEnFecha as $logs){
                    if(!in_array($logs->idUser, $arrayIdUsers)){
                        array_push($arrayIdUsers, $logs->idUser);
                    }
                }

                foreach($arrayIdUsers as $id){
                    $user = UsuarioApi::GetUserById($id);
                    if(strcasecmp($user->tipo, 'admin') !== 0)
                        array_push($arrayUsers, $user);
                }

                $respuesta = array("Estado" => "OK",
                                    "Mensaje" => "Cantidad de operaciones por empleado",
                                    "Usuarios" => UsuarioApi::GetFormatArrayUserOperacionesCount($arrayUsers));
                $status = 200;
            }
        }
        return $response->withJson($respuesta, $status);
    }
}

?>