<?php
namespace Clases;
use App\Models\Menu;
use \DateTime;

class MenuApi
{
    public function __construct()
    {
    }

    public static function GetMenuById($id)
    {
        $menuORM = new Menu();
        return $menuORM->where('id', "=", $id)->first();
    }

    public static function GetMenuByNombre($nombre)
    {
        $menuORM = new Menu();
        return $menuORM->where('nombre', "=", $nombre)->first();
    }

    public function GetAllMenu($request, $response)
    {
        $menuORM = new Menu();
        $resp = $menuORM::orderBy('id', 'asc')->get();
        return $response->withJson($resp, 200);
    }

    public function InsertMenu($request, $response)
    {
        $data = $request->getParsedBody();
        $status = 400;
        $respuesta = array("Estado" => "ERROR", "Mensaje" => "Es requerido un nombre, precio y sector de menú");
        if(isset($data['nombre']) && isset($data['precio']) && isset($data['sector']))
        {
            $nombre = filter_var(trim($data['nombre']), FILTER_SANITIZE_STRING);
            $precio = filter_var(trim($data['precio']), FILTER_VALIDATE_FLOAT);
            $sector = filter_var(trim($data['sector']), FILTER_SANITIZE_STRING);
            $respuesta = array("Estado" => "ERROR", "Mensaje" => "Valores no permitidos");
            if($nombre && $precio && $sector)
            {
                $respuesta = array("Estado" => "ERROR", "Mensaje" => "Ya existe menu registrado con nombre ".$nombre);
                $menuExist = MenuApi::GetMenuByNombre($nombre);
                if(is_null($menuExist))
                {
                    $respuesta = array("Estado" => "ERROR", "Mensaje" => "El sector debe ser bartender, cervecero, cocinero, mozo o socio");
                    if( strcasecmp($sector, 'bartender') == 0 ||
                        strcasecmp($sector, 'cervecero') == 0 ||
                        strcasecmp($sector, 'cocinero') == 0 ||
                        strcasecmp($sector, 'mozo') == 0 ||
                        strcasecmp($sector, 'socio') == 0)
                    {
                        $menu = new Menu();
                        $menu->nombre = $nombre;
                        $menu->precio = $precio;
                        $menu->sector = $sector;
                        $menu->save();
                        $respuesta = array("Estado" => "OK", "Mensaje" => "Menu, ".$nombre." registrado");
                        $status = 200;
                    }
                }
            }
        }
        return $response->withJson($respuesta, $status);
    }

    public function UpdateMenu($request, $response)
    {
        $data = $request->getParsedBody();
        $status = 400;
        $modifica = false;
        $respuesta = array("Estado" => "ERROR", "Mensaje" => "Es requerido id de menú");
        if(isset($data['id']))
        {
            $respuesta = array("Estado" => "ERROR", "Mensaje" => "No existe menú registrado con id ".$data['id']);
            $menuExist = MenuApi::GetMenuById($data['id']);
            if($menuExist)
            {
                if(isset($data['nombre'])){
                    $nombre = filter_var(trim($data['nombre']), FILTER_SANITIZE_STRING);
                    if($nombre){
                        $menuExist->nombre = $nombre;
                        $modifica = true;
                    }
                }
                if(isset($data['precio'])){
                    $precio = filter_var(trim($data['precio']), FILTER_VALIDATE_FLOAT);
                    if($precio){
                        $menuExist->precio = $precio;
                        $modifica = true;
                    }
                }
                if(isset($data['sector'])){
                    $sector = filter_var(trim($data['sector']), FILTER_SANITIZE_STRING);
                    if($sector){
                        if( strcasecmp($sector, 'bartender') == 0 ||
                        strcasecmp($sector, 'cervecero') == 0 ||
                        strcasecmp($sector, 'cocinero') == 0 ||
                        strcasecmp($sector, 'mozo') == 0 ||
                        strcasecmp($sector, 'socio') == 0)
                        {
                            $menuExist->sector = $sector;
                            $modifica = true;
                        }
                    }
                }
                $respuesta = array("Estado" => "ERROR", "Mensaje" => "Valores no permitidos");
                if($modifica){
                    $menuExist->save();
                    $respuesta = array("Estado" => "OK", "Mensaje" => "Menu, ".$menuExist->nombre." modificado");
                    $status = 200;
                }
            }
        }
        return $response->withJson($respuesta, $status);
    }

    public function DeleteMenu($request, $response, $args)
    {
        $status = 400;
        $respuesta = array("Estado" => "ERROR", "Mensaje" => "Es requerido id del menú");
        if(isset($args['id']))
        {
            $id = $args['id'];
            $menu = MenuApi::GetMenuById($id);
            $respuesta = array("Estado" => "ERROR", "Mensaje" => "No existe menú con id ".$id);
            if($menu)
            {
                $menu->delete();
                $status = 200;
                $respuesta = array("Estado" => "OK", "Mensaje" => "Menú id: ".$id." eliminado");
            }
        }
        return $response->withJson($respuesta, $status);
    }
}