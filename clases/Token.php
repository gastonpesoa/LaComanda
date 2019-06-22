<?php
namespace Clases;
use Firebase\JWT\JWT;
use \Exception;

class Token
{
    private static $claveSecreta = 'ClaveSuperSecreta@';
    private static $tipoEncriptacion = ['HS256'];
    private static $aud = null;
    
    public static function CreateToken($datos)
    {
        $ahora = time();
        $payload = array(
        	'iat'=>$ahora,
            'exp' => $ahora + (60*60),
            'aud' => self::Aud(),
            'data' => $datos,
            'app'=> "API REST La Comanda 2019"
        );
     
        return JWT::encode($payload, self::$claveSecreta);
    }
    
    public static function VerifyToken($token)
    {  
        if(empty($token)|| $token=="")
        {
            throw new Exception("El token esta vacio.");
        } 
        // las siguientes lineas lanzan una excepcion, de no ser correcto o de haberse terminado el tiempo       
        try {
            $decodificado = JWT::decode(
                $token,
                self::$claveSecreta,
                self::$tipoEncriptacion
            );
        } catch (ExpiredException $e) {
            //var_dump($e);
           throw new Exception("Clave fuera de tiempo");
        }
        
        // si no da error,  verifico los datos de AUD que uso para saber de que lugar viene  
        if($decodificado->aud !== self::Aud())
        {
            throw new Exception("No es el usuario valido");
        }
    }
       
    public static function GetPayLoad($token)
    {
        return JWT::decode(
            $token,
            self::$claveSecreta,
            self::$tipoEncriptacion
        );
    }

    public static function GetData($token)
    {
        return JWT::decode(
            $token,
            self::$claveSecreta,
            self::$tipoEncriptacion
        )->data;
    }

    private static function Aud()
    {
        $aud = '';
        
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $aud = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $aud = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $aud = $_SERVER['REMOTE_ADDR'];
        }
        
        $aud .= @$_SERVER['HTTP_USER_AGENT'];
        $aud .= gethostname();
        
        return sha1($aud);
    }
}